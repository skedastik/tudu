/**
 * Sign up a new user.
 * 
 * Arguments
 *   _email         A valid email address
 *   _pw_salt       User's password salt
 *   _pw_hash       Encrypted password
 *   _kvs           Optional KVS data
 *   _ip            Optional IP address
 *   _autoconfirm   Pass true to automatically confirm user
 * 
 * Returns
 *   User ID on success.
 *   -1 if email is already in use
 */
create or replace function tudu.signup_user(
    _email          varchar,
    _pw_salt        varchar,
    _pw_hash        varchar,
    _ip             inet,
    _autoconfirm    boolean default false
) returns bigint as $$
declare
    _user_id bigint;
    _signup_token varchar;
    _kvs hstore;
begin
    if exists (select 1 from tudu_user where email = _email) then
        return -1;
    end if;
    
    _user_id      := nextval('tudu_user_seq');
    _email        := lower(util.btrim_whitespace(_email));
    _signup_token := md5(random()::text || 'tudumajik' || _user_id);
    _kvs          := hstore('signup_token', _signup_token);
    
    insert into tudu_user (user_id, email, password_salt, password_hash, kvs)
    values (_user_id, _email, _pw_salt, _pw_hash, _kvs);
    
    perform tudu.user_log_add(_user_id, 'signup', _ip);
        
    if _autoconfirm then
        perform tudu.confirm_user(_user_id, _signup_token, _ip);
    end if;
    
    return _user_id;
end;
$$ language plpgsql security definer;

/**
 * Confirm an existing user.
 * 
 * Arguments
 *   _user_id           ID of existing user
 *   _signup_token      A signup token
 *   _ip                Optional IP address
 * 
 * Returns
 *   User ID on success.
 *   -1 if user does not exist
 *   -2 if signup token does not match
 */
create or replace function tudu.confirm_user(
    _user_id            bigint,
    _signup_token       varchar,
    _ip                 inet
) returns bigint as $$
declare
    _kvs hstore;
    _status varchar;
begin
    select user_id, status, kvs into _user_id, _status, _kvs from tudu_user where user_id = _user_id;
    
    if _user_id is null then
        return -1;
    end if;
    
    if _status = 'active' then
        return _user_id;
    end if;
    
    if _signup_token is distinct from (_kvs->'signup_token') then
        return -2;
    end if;
    
    update tudu_user
    set status = 'active',
        edate = now()
    where user_id = _user_id;
    
    perform tudu.user_log_add(_user_id, 'confirm', _ip);
    
    return _user_id;
end;
$$ language plpgsql security definer;

/**
 * Log a user operation. Automatically called by user functions.
 * 
 * Arguments
 *   _user_id       User ID
 *   _operation     An operation string
 *   _ip            Optional IP address
 *   _info          Optional info string
 *   _kvs           Optional HSTORE
 */
create or replace function tudu.user_log_add(
    _user_id        bigint,
    _operation      varchar,
    _ip             inet default null,
    _info           text default null,
    _kvs            hstore default null
) returns void as $$
begin
    insert into tudu_user_log (user_id, operation, ip, info, kvs)
    values (_user_id, _operation, _ip, _info, _kvs);
end;
$$ language plpgsql security definer;
