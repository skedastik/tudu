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
    _id     bigint;
begin
    _id   := nextval('tudu_user_seq') + 1;
    perform tudu.signup_user('user' || _id || '@foo.xyz', tudu.random_string(), tudu.random_string(), '127.0.0.1');
    return tudu.latest_user();
end;
$$ language plpgsql;
