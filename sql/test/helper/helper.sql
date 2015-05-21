/**
 * Return the latest user.
 */
drop function if exists tudu.latest_user();
create function tudu.latest_user() returns tudu_user as $$
    select * from tudu_user order by user_id desc limit 1;
$$ language sql;

/**
 * Return the latest user log.
 */
drop function if exists tudu.latest_user_log();
create function tudu.latest_user_log() returns tudu_user_log as $$
    select * from tudu_user_log order by log_id desc limit 1;
$$ language sql;

/**
 * Return the latest access token.
 */
drop function if exists tudu.latest_access_token();
create function tudu.latest_access_token() returns tudu_access_token as $$
    select * from tudu_access_token order by token_id desc limit 1;
$$ language sql;

/**
 * Return the latest access token log.
 */
drop function if exists tudu.latest_access_token_log();
create function tudu.latest_access_token_log() returns tudu_access_token_log as $$
    select * from tudu_access_token_log order by log_id desc limit 1;
$$ language sql;

/**
 * Return the latest task.
 */
drop function if exists tudu.latest_task();
create function tudu.latest_task() returns tudu_task as $$
    select * from tudu_task order by task_id desc limit 1;
$$ language sql;

/**
 * Return the latest task log.
 */
drop function if exists tudu.latest_task_log();
create function tudu.latest_task_log() returns tudu_task_log as $$
    select * from tudu_task_log order by log_id desc limit 1;
$$ language sql;

/**
 * Generate a random string.
 */
drop function if exists tudu.random_string();
create function tudu.random_string() returns text as $$
begin
    return md5(random()::text);
end;
$$ language plpgsql;

/**
 * Sign up a random user.
 */
drop function if exists tudu.create_random_user();
create function tudu.create_random_user() returns tudu_user as $$
declare
    _id bigint;
begin
    _id := currval('tudu_user_seq');
    perform tudu.signup_user('user' || _id || '@foo.xyz', tudu.random_string(), '127.0.0.1');
    return tudu.latest_user();
end;
$$ language plpgsql;

/**
 * Create a random access token for the given user.
 * 
 * Arguments
 *   _user_id       User ID
 *   _token_type    Type of access token.
 *   _ttl           Optional TTL value (default is 1 week)
 *   _auto_revoke   Optional. Automatically revoke existing active tokens of
 *                  same type. Defaults to FALSE.
 */
drop function if exists tudu.create_random_access_token(bigint, varchar, interval);
create function tudu.create_random_access_token(
    _user_id        bigint,
    _token_type     varchar,
    _ttl            interval    default '1 week',
    _auto_revoke    boolean     default false
) returns tudu_access_token as $$
begin
    perform tudu.create_access_token(_user_id, tudu.random_string(), _token_type, _ttl, _auto_revoke, '127.0.0.1');
    return tudu.latest_access_token();
end;
$$ language plpgsql;

/**
 * Create a random task for the given user.
 * 
 * Arguments
 *   _user_id       User ID
 *   _description   Optional description
 *   _tags          Optional array of tag strings
 */
drop function if exists tudu.create_random_task(bigint, varchar, interval);
create function tudu.create_random_task(
    _user_id        bigint,
    _description    varchar     default tudu.random_string(),
    _tags           varchar[]   default array[tudu.random_string(), tudu.random_string()]
) returns tudu_task as $$
begin
    perform tudu.create_task(_user_id, _description, _tags, '127.0.0.1');
    return tudu.latest_task();
end;
$$ language plpgsql;
