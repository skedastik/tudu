/* test */

/**
 * Return the latest user.
 */
create or replace function latest_user() returns tudu_user as $$
    select * from tudu_user order by user_id desc limit 1;
$$ language sql;

/**
 * Return the latest user log.
 */
create or replace function latest_user_log() returns tudu_user_log as $$
    select * from tudu_user_log order by log_id desc limit 1;
$$ language sql;

/*create table tudu_user (
    user_id                 bigint primary key default nextval('tudu_user_seq'),
    email                   varchar(128) not null unique,
    password_salt           varchar(64) not null,
    password_hash           varchar(256) not null,
    --
    kvs                     hstore default null,
    status                  varchar(32) not null default 'init',
    edate                   timestamptz not null default current_timestamp,
    cdate                   timestamptz not null default current_timestamp
);
create table tudu_user_log (
    log_id                  bigint primary key default nextval('tudu_user_log_seq'),
    user_id                 bigint references tudu_user,
    operation               varchar(128) not null,
    ip                      inet default null,
    info                    text default null,
    --
    kvs                     hstore default null,
    cdate                   timestamptz not null default current_timestamp
);*/

create or replace function unit_tests.signup_user() returns test_result as $$
declare
    _message        test_result;
    _user_john      tudu_user%ROWTYPE;
    _user_john_log  tudu_user_log%ROWTYPE;
begin
    perform tudu.signup_user('foo@bar.baz', 'djiosd', 'ahdinsdjnsdkfjkul', '127.0.0.1');
    select * into _user_john from latest_user();
    select * into _user_john_log from latest_user_log();
    
    if _user_john.email <> 'foo@bar.baz' then
        select assert.fail('should create a user with email "foo@bar.baz"') into _message;
        return _message;
    end if;
    
    if _user_john.password_salt <> 'djiosd' then
        select assert.fail('should create a user with password_salt "djiosd"') into _message;
        return _message;
    end if;
    
    if _user_john.password_hash <> 'ahdinsdjnsdkfjkul' then
        select assert.fail('should create a user with password_hash "ahdinsdjnsdkfjkul"') into _message;
        return _message;
    end if;
    
    if _user_john.status <> 'init' then
        select assert.fail('should create a user with status "init"') into _message;
        return _message;
    end if;
    
    if _user_john_log.user_id <> _user_john.user_id then
        select assert.fail('should create a user log entry with user_id equal to that of newly signed up user') into _message;
        return _message;
    end if;
    
    if _user_john_log.ip <> '127.0.0.1' then
        select assert.fail('should create a user log entry with ip "127.0.0.1"') into _message;
        return _message;
    end if;
    
    if _user_john_log.operation <> 'signup' then
        select assert.fail('should create a user log entry with operation "signup"') into _message;
        return _message;
    end if;

    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

/* TODO: Test duplicate email */
/* TODO: Test confirm user */
