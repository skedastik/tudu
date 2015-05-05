/**
 * Create an access token for a given user. If a valid access token already
 * exists, it is revoked before creating a new one. You should generate a new
 * access token whenever the user logs in.
 * 
 * Arguments
 *   _user_id       ID of existing user
 *   _pw_hash       User's password hash
 *   _token_string  A non-NULL, access token string. Token strings can never be
 *                  reused per individual user.
 *   _ttl           Access token time to live. A token is considered expired if
 *                  (now() - cdate >= ttl).
 *   _ip            Optional IP address
 *   _kvs           Optional KVS data
 * 
 * Returns
 *   Token ID on success.
 *   -1 if user does not exist
 *   -2 if password hash does not match
 *   -3 if access token is not unique for the given user
 */
create or replace function tudu.create_access_token(
    _user_id            bigint,
    _pw_hash            varchar,
    _token_string       text,
    _ttl                interval,
    _ip                 inet default null,
    _kvs                hstore default ''
) returns bigint as $$
declare
    _token_id           bigint;
    _pw_hash_current    varchar;
    _constraint         text;
begin
    select user_id, password_hash into _user_id, _pw_hash_current from tudu_user where user_id = _user_id;
    
    if _user_id is null then
        return -1;
    end if;
    
    if _pw_hash is distinct from _pw_hash_current then
        return -2;
    end if;
    
    begin
        perform tudu.revoke_active_access_token(_user_id, _ip);
    
        _token_id := nextval('tudu_access_token_seq');
        _kvs      := _kvs || hstore('ttl', _ttl::text);
        
        insert into tudu_access_token (token_id, user_id, token_string, kvs)
        values (_token_id, _user_id, _token_string, _kvs);
    exception when unique_violation then
        get stacked diagnostics _constraint = constraint_name;
        if _constraint = 'tudu_access_token_uniq_idx' then
            return -3;
        end if;
        raise;
    end;
    
    perform tudu.access_token_log_add(_token_id, 'create', _ip);
    
    return _token_id;
end;
$$ language plpgsql security definer;

/**
 * Revoke given user's currently active access token.
 * 
 * Arguments
 *   _user_id       ID of existing user
 *   _ip            Optional IP address
 * 
 * Returns
 *   ID of access token on success
 *   -1 if no active access token exists
 */
create or replace function tudu.revoke_active_access_token(
    _user_id        bigint,
    _ip             inet default null
) returns bigint as $$
declare
    _token_id       bigint;
begin
    with updated_row as (
        update tudu_access_token
        set status = 'revoked',
            edate  = now()
        where user_id = _user_id and status = 'active'
        returning token_id
    )
    select token_id into _token_id from updated_row;
    
    if _token_id is null then
        return -1;
    end if;
    
    perform tudu.access_token_log_add(_token_id, 'revoke', _ip);
    
    return _token_id;
end;
$$ language plpgsql security definer;

/**
 * Validate an access token for a given user.
 * 
 * Arguments
 *   _user_id       ID of existing user
 *   _token_string  An access token string
 *  
 * Returns
 *    0 if token is valid
 *   -1 if token/user pair does not exist
 *   -2 if token was revoked
 *   -3 if token is expired
 */
create or replace function tudu.validate_access_token(
    _user_id        bigint,
    _token_string   text
) returns integer as $$
declare
    _status         varchar;
    _ttl            interval;
    _cdate          timestamptz;
begin
    select status, kvs->'ttl', cdate into _status, _ttl, _cdate
    from tudu_access_token
    where user_id = _user_id and token_string = _token_string;
    
    if _status is null then
        return -1;
    end if;
    
    if _status <> 'active' then
        return -2;
    end if;
    
    if now() - _cdate >= _ttl then
        return -3;
    end if;
    
    return 0;
end;
$$ language plpgsql security definer;

/**
 * Log a user access token operation.
 * 
 * Arguments
 *   _token_id      Access token ID
 *   _operation     An operation string
 *   _ip            Optional IP address
 *   _info          Optional info string
 *   _kvs           Optional HSTORE
 */
create or replace function tudu.access_token_log_add(
    _token_id       bigint,
    _operation      varchar,
    _ip             inet default null,
    _info           text default null,
    _kvs            hstore default ''
) returns void as $$
begin
    insert into tudu_access_token_log (token_id, operation, ip, info, kvs)
    values (_token_id, _operation, _ip, _info, _kvs);
end;
$$ language plpgsql security definer;
