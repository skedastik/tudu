/**
 * Sign up a new user.
 * 
 * Arguments
 *   _email         A valid email address
 *   _pw_hash       Encrypted password
 *   _ip            Optional IP address
 *   _kvs           Optional KVS data
 *   _autoconfirm   Pass true to automatically confirm user
 * 
 * Returns
 *   User ID on success.
 *   -1 if email is already in use
 */
create or replace function tudu.signup_user(
    _email          varchar,
    _pw_hash        varchar,
    _ip             inet default null,
    _kvs            hstore default '',
    _autoconfirm    boolean default false
) returns bigint as $$
declare
    _user_id        bigint;
    _signup_token   varchar;
    _constraint     text;
begin
    _email        := util.btrim_whitespace(_email);
    _user_id      := nextval('tudu_user_seq');
    _signup_token := md5(random()::text || 'tudumajik' || _user_id);
    _kvs          := _kvs || hstore('signup_token', _signup_token);
    
    if exists (select 1 from tudu_user where lower(email) = lower(_email)) then
        return -1;
    end if;
    
    insert into tudu_user (user_id, email, password_hash, kvs)
    values (_user_id, _email, _pw_hash, _kvs);
    
    perform tudu.user_log_add(_user_id, 'signup', _ip);
        
    if _autoconfirm then
        perform tudu.confirm_user(_user_id, null, _signup_token, _ip);
    end if;
    
    return _user_id;
end;
$$ language plpgsql security definer;

/**
 * Verify a user's account.
 * 
 * Arguments
 *   _user_id           ID of existing user (optional w/ below)
 *   _email             Email address of existing user (optional w/ above)
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
    _email              varchar,
    _signup_token       varchar,
    _ip                 inet default null
) returns bigint as $$
declare
    _kvs                hstore;
    _status             varchar;
begin
    _email := util.btrim_whitespace(_email);
    
    select user_id, status, kvs into _user_id, _status, _kvs from tudu_user
    where case when _user_id is null then lower(email) = lower(_email)
               else user_id = _user_id end;
    
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
        edate  = now()
    where user_id = _user_id;
    
    perform tudu.user_log_add(_user_id, 'confirm', _ip);
    
    return _user_id;
end;
$$ language plpgsql security definer;

/**
 * Change an existing user's password.
 * 
 * Arguments
 *   _user_id       ID of existing user
 *   _old_pw_hash   Old password hash
 *   _new_pw_hash   New password hash
 *   _ip            Optional IP address
 * 
 * Returns
 *   ID of user on success
 *   -1 if user ID is invalid
 *   -2 if old password hash does not match
 *   -3 if new password hash is identical to the old one
 */
create or replace function tudu.set_user_password_hash(
    _user_id        bigint,
    _old_pw_hash    varchar,
    _new_pw_hash    varchar,
    _ip             inet        default null
) returns bigint as $$
declare
    _pw_hash        varchar;
begin
    select user_id, password_hash
    into _user_id, _pw_hash
    from tudu_user where user_id = _user_id;
    
    if _user_id is null then
        return -1;
    end if;
    
    if _pw_hash <> _old_pw_hash then
        return -2;
    end if;
    
    if _pw_hash = _new_pw_hash then
        return -3;
    end if;
    
    update tudu_user
    set password_hash = _new_pw_hash,
        edate         = now()
    where user_id = _user_id;
    
    perform tudu.user_log_add(_user_id, 'set_password_hash', _ip);
    
    return _user_id;
end;
$$ language plpgsql security definer;

/**
 * Change an existing user's email address.
 * 
 * Arguments
 *   _user_id       ID of existing user
 *   _new_email     Email address
 *   _ip            Optional IP address
 * 
 * Returns
 *   ID of user on success
 *   -1 if user ID is invalid
 *   -2 if email address is identical to existing one
 *   -3 if email address is already linked to another user
 */
create or replace function tudu.set_user_email(
    _user_id    bigint,
    _new_email  varchar,
    _ip         inet        default null
) returns bigint as $$
declare
    _email      varchar;
begin
    _new_email := util.btrim_whitespace(_new_email);
    select user_id, email into _user_id, _email from tudu_user where user_id = _user_id;
    
    if _user_id is null then
        return -1;
    end if;
    
    if _email = _new_email then
        return -2;
    end if;
    
    if exists (select 1 from tudu_user where email = _new_email) then
        return -3;
    end if;
    
    update tudu_user
    set email = _new_email,
        edate = now()
    where user_id = _user_id;
    
    perform tudu.user_log_add(
        _user_id,
        'set_email',
        _ip,
        hstore(array[
            ['old_email', _email],
            ['new_email', _new_email]
        ])
    );
    
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
 *   _kvs           Optional HSTORE
 *   _info          Optional info string
 */
create or replace function tudu.user_log_add(
    _user_id        bigint,
    _operation      varchar,
    _ip             inet default null,
    _kvs            hstore default '',
    _info           text default null
) returns void as $$
begin
    insert into tudu_user_log (user_id, operation, ip, info, kvs)
    values (_user_id, _operation, _ip, _info, _kvs);
end;
$$ language plpgsql security definer;
