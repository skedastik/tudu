/**
 * Create an access token for a given active user.
 * 
 * Arguments
 *   _user_id       ID of existing user
 *   _token_string  A non-NULL, access token string. Token strings can never be
 *                  reused per individual user.
 *   _token_type    Type of access token.
 *   _ttl           Access token time to live. A token is considered expired if
 *                  (now() - cdate >= ttl).
 *   _auto_revoke   Optional. Automatically revoke existing active tokens of
 *                  same type. Defaults to FALSE.
 *   _ip            Optional IP address
 *   _kvs           Optional KVS data
 * 
 * Returns
 *   Token ID on success.
 *   -1 if user is not valid
 *   -2 if access token is not unique for the given user
 */
create or replace function tudu.create_access_token(
    _user_id            bigint,
    _token_string       text,
    _token_type         varchar,
    _ttl                interval,
    _auto_revoke        boolean     default false,
    _ip                 inet        default null,
    _kvs                hstore      default ''
) returns bigint as $$
declare
    _token_id           bigint;
    _constraint         text;
begin
    select user_id into _user_id from tudu_user where user_id = _user_id and status = 'active';
    
    if _user_id is null then
        return -1;
    end if;
    
    if exists (select 1 from tudu_access_token where user_id = _user_id and token_string = _token_string) then
        return -2;
    end if;
    
    if _auto_revoke then
        perform tudu.revoke_active_access_tokens(_user_id, _token_type, _ip);
    end if;
    
    _token_id := nextval('tudu_access_token_seq');
    _kvs      := _kvs || hstore('ttl', _ttl::text);
    
    insert into tudu_access_token (token_id, user_id, token_string, token_type, kvs)
    values (_token_id, _user_id, _token_string, _token_type, _kvs);
    
    perform tudu.access_token_log_add(_token_id, 'create', _ip);
    
    return _token_id;
end;
$$ language plpgsql security definer;

/**
 * Revoke user's active access tokens of given type.
 * 
 * Arguments
 *   _user_id       ID of existing user
 *   _token_type    Type of access token.
 *   _ip            Optional IP address
 * 
 * Returns
 *   The number of tokens revoked on success
 *   -1 if no active access tokens of same type exist
 */
create or replace function tudu.revoke_active_access_tokens(
    _user_id        bigint,
    _token_type     varchar,
    _ip             inet        default null
) returns int as $$
declare
    _token_ids      bigint[];
    _token_id       bigint;
    _count          int         default 0;
begin
    with updated_rows as (
        update tudu_access_token
        set status = 'revoked',
            edate  = now()
        where user_id = _user_id and status = 'active' and token_type = _token_type
        returning token_id
    )
    select array_agg(token_id) into _token_ids from updated_rows;
    
    if _token_ids is null then
        return -1;
    end if;
    
    foreach _token_id in array _token_ids loop
        perform tudu.access_token_log_add(_token_id, 'revoke', _ip);
        _count := _count + 1;
    end loop;
    
    return _count;
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
 *   _kvs           Optional HSTORE
 *   _info          Optional info string
 */
create or replace function tudu.access_token_log_add(
    _token_id       bigint,
    _operation      varchar,
    _ip             inet default null,
    _kvs            hstore default '',
    _info           text default null
) returns void as $$
begin
    insert into tudu_access_token_log (token_id, operation, ip, info, kvs)
    values (_token_id, _operation, _ip, _info, _kvs);
end;
$$ language plpgsql security definer;
