/* test */

create or replace function unit_tests.signup_user() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _user_log   tudu_user_log%ROWTYPE;
begin
    perform tudu.signup_user('foo@bar.baz', 'djiosd', 'ahdinsdjnsdkfjkul', '127.0.0.1');
    _user     := tudu.latest_user();
    _user_log := tudu.latest_user_log();
    
    if _user.email <> 'foo@bar.baz' then
        select assert.fail('should create a user with email "foo@bar.baz"') into _message;
        return _message;
    end if;
    
    if _user.password_salt <> 'djiosd' then
        select assert.fail('should create a user with password_salt "djiosd"') into _message;
        return _message;
    end if;
    
    if _user.password_hash <> 'ahdinsdjnsdkfjkul' then
        select assert.fail('should create a user with password_hash "ahdinsdjnsdkfjkul"') into _message;
        return _message;
    end if;
    
    if _user.status <> 'init' then
        select assert.fail('should create a user with status "init"') into _message;
        return _message;
    end if;
    
    if not _user.kvs ? 'signup_token' then
        select assert.fail('should create a user with "kvs" HSTORE containing a "signup_token" key') into _message;
        return _message;
    end if;
    
    if _user_log.user_id <> _user.user_id then
        select assert.fail('should create a user log entry with user_id equal to that of newly signed up user') into _message;
        return _message;
    end if;
    
    if _user_log.ip <> '127.0.0.1' then
        select assert.fail('should create a user log entry with ip "127.0.0.1"') into _message;
        return _message;
    end if;
    
    if _user_log.operation <> 'signup' then
        select assert.fail('should create a user log entry with operation "signup"') into _message;
        return _message;
    end if;

    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.signup_user_with_duplicate_email() returns test_result as $$
declare
    _message    test_result;
    _result     bigint;
begin
    perform tudu.signup_user('baz@qux.gar', 'opiopuw', 'nasndjasnkjdASD', '127.0.0.1');
    
    _result := tudu.signup_user('baz@qux.gar', 'ndfaksh', 'iuiowernsdlfio', '127.0.0.1');
    if _result <> -1 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.confirm_user_using_mismatched_signup_token() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _result     bigint;
begin
    perform tudu.signup_user('fee@ble.yer', 'mspbneub', 'nmsabnytrbewkasd', '127.0.0.1');
    
    _user   := tudu.latest_user();
    _result := tudu.confirm_user(_user.user_id, 'bad_signup_token', '127.0.0.1');
    
    if _result <> -2 then
        select assert.fail('should fail.') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.confirm_user() returns test_result as $$
declare
    _message        test_result;
    _user           tudu_user%ROWTYPE;
    _user_log       tudu_user_log%ROWTYPE;
    _result         bigint;
begin
    perform tudu.signup_user('super@user.win', 'biawebd', 'asduytcwfebnail', '127.0.0.1');
    
    _user     := tudu.latest_user();
    _result   := tudu.confirm_user(_user.user_id, _user.kvs->'signup_token', '127.0.0.1');
    _user     := tudu.latest_user();
    _user_log := tudu.latest_user_log();
    
    if _result <> _user.user_id then
        select assert.fail('should succeed given a valid user ID and signup token.') into _message;
        return _message;
    end if;
    
    if _user.status <> 'active' then
        select assert.fail(E'should set confirmed user\'s status to "active".') into _message;
        return _message;
    end if;
    
    if _user_log.operation <> 'confirm' then
        select assert.fail('should create a user log entry with operation "confirm"') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.confirm_nonexistent_user() returns test_result as $$
declare
    _message    test_result;
    _result     bigint;
begin
    _result := tudu.confirm_user(-1, 'kajdsfkjlsdfl', '127.0.0.1');
    
    if _result <> -1 then
        select assert.fail('should fail.') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;
