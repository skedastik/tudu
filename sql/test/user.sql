/* test */

create or replace function unit_tests.signup_user() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _user_log   tudu_user_log%ROWTYPE;
begin
    perform tudu.signup_user('Foo@BaR.baz', 'ahdinsdjnsdkfjkul', '127.0.0.1');
    _user       := tudu.latest_user();
    _user_log   := tudu.latest_user_log();
    
    if _user.user_id is null then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;
    
    if _user.email <> 'Foo@BaR.baz' then
        select assert.fail('should create a user with email "Foo@BaR.baz" (note that case must be preserved)') into _message;
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
    perform tudu.signup_user('baz@qux.gar', 'nasndjasnkjdASD', '127.0.0.1');
    
    /* simultaneously test for case insensitivity */
    _result := tudu.signup_user('BaZ@qUx.gAr', 'iuiowernsdlfio', '127.0.0.1');
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
    perform tudu.signup_user('fee@ble.yer', 'nmsabnytrbewkasd', '127.0.0.1');
    
    _user   := tudu.latest_user();
    _result := tudu.confirm_user(_user.user_id, null, 'bad_signup_token', '127.0.0.1');
    
    if _result <> -2 then
        select assert.fail('should fail.') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.confirm_user_using_id() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _user_log   tudu_user_log%ROWTYPE;
    _result     bigint;
begin
    perform tudu.signup_user('super@user.win', 'asduytcwfebnail', '127.0.0.1');
    
    _user     := tudu.latest_user();
    _result   := tudu.confirm_user(_user.user_id, null, _user.kvs->'signup_token', '127.0.0.1');
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

create or replace function unit_tests.confirm_user_using_email() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _result     bigint;
begin
    perform tudu.signup_user('iheart@turtles.gmn', 'opoytouyi');
    
    _user     := tudu.latest_user();
    /* simultaneously test for case insensitivity and normalization */
    _result   := tudu.confirm_user(null, '   iHeArT@tUrTlEs.gMn   ', _user.kvs->'signup_token');
    _user     := tudu.latest_user();
    
    if _result <> _user.user_id then
        select assert.fail('should succeed given a valid email and signup token.') into _message;
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
    _result := tudu.confirm_user(-1, null, 'kajdsfkjlsdfl', '127.0.0.1');
    
    if _result <> -1 then
        select assert.fail('should fail.') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.confirm_active_user() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _result     bigint;
begin
    _user   := tudu.create_random_user();
    perform tudu.confirm_user(null, _user.email, _user.kvs->'signup_token');
    _result := tudu.confirm_user(null, _user.email, _user.kvs->'signup_token');
    
    if _result <> -3 then
        select assert.fail('should fail.') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.update_user() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _old_email  varchar;
    _user_id    bigint;
    _user_log   tudu_user_log%ROWTYPE;
begin
    _user      := tudu.create_random_user();
    _old_email := _user.email;
    /* simultaneously test email normalization */
    _user_id   := tudu.update_user(_user.user_id, '   New@Email.Set   ', 'pw_hash');
    _user      := tudu.latest_user();
    
    if _user_id <> _user.user_id then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;
    
    if _user.email <> 'New@Email.Set' then
        select assert.fail('should change email to "New@Email.Set"') into _message;
        return _message;
    end if;
    
    if _user.password_hash <> 'pw_hash' then
        select assert.fail('should change password hash to "pw_hash"') into _message;
        return _message;
    end if;
    
    if not exists (
        select 1 from tudu_user_log 
        where user_id = _user_id
          and operation = 'change_email'
          and kvs->'old_email' = _old_email
          and kvs->'new_email' = 'New@Email.Set'
    ) then
        select assert.fail('should create a user log entry with matching user_id, operation "change_email", and KVS with correct "old_email" and "new_email" keys') into _message;
        return _message;
    end if;
    
    if not exists (select 1 from tudu_user_log where user_id = _user_id and operation = 'change_password_hash') then
        select assert.fail('should create a user log entry with matching user_id and operation "change_password_hash"') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.update_user_with_null_email() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _old_email  varchar;
    _user_id    bigint;
    _user_log   tudu_user_log%ROWTYPE;
begin
    _user      := tudu.create_random_user();
    _old_email := _user.email;
    _user_id   := tudu.update_user(_user.user_id, null, 'pw_hash');
    _user      := tudu.latest_user();
    
    if _user_id <> _user.user_id then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;
    
    if _user.email <> _old_email then
        select assert.fail('should not change email') into _message;
        return _message;
    end if;
    
    if _user.password_hash <> 'pw_hash' then
        select assert.fail('should change password hash to "pw_hash"') into _message;
        return _message;
    end if;
    
    if exists (select 1 from tudu_user_log where user_id = _user_id and operation = 'change_email') then
        select assert.fail('should not create a user log entry with operation "change_email"') into _message;
        return _message;
    end if;
    
    if not exists (select 1 from tudu_user_log where user_id = _user_id and operation = 'change_password_hash') then
        select assert.fail('should create a user log entry with matching user_id and operation "change_password_hash"') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.update_user_with_null_password_hash() returns test_result as $$
declare
    _message        test_result;
    _user           tudu_user%ROWTYPE;
    _old_email      varchar;
    _old_pw_hash    varchar;
    _user_id        bigint;
begin
    _user        := tudu.create_random_user();
    _old_email   := _user.email;
    _old_pw_hash := _user.password_hash;
    _user_id     := tudu.update_user(_user.user_id, 'New@Email.Set', null);
    _user        := tudu.latest_user();
    
    if _user_id <> _user.user_id then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;
    
    if _user.email <> 'New@Email.Set' then
        select assert.fail('should change email to "New@Email.Set"') into _message;
        return _message;
    end if;
    
    if _user.password_hash <> _old_pw_hash then
        select assert.fail('should not change password hash') into _message;
        return _message;
    end if;
    
    if not exists (
        select 1 from tudu_user_log 
        where user_id = _user_id
          and operation = 'change_email'
          and kvs->'old_email' = _old_email
          and kvs->'new_email' = 'New@Email.Set'
    ) then
        select assert.fail('should create a user log entry with matching user_id, operation "change_email", and KVS with correct "old_email" and "new_email" keys') into _message;
        return _message;
    end if;
    
    if exists (select 1 from tudu_user_log where user_id = _user_id and operation = 'change_password_hash') then
        select assert.fail('should NOT create a user log entry with operation "change_password_hash"') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.update_user_with_null_email_and_password_hash() returns test_result as $$
declare
    _message        test_result;
    _user           tudu_user%ROWTYPE;
    _old_email      varchar;
    _old_pw_hash    varchar;
    _user_id        bigint;
begin
    _user        := tudu.create_random_user();
    _old_email   := _user.email;
    _old_pw_hash := _user.password_hash;
    _user_id     := tudu.update_user(_user.user_id, null, null);
    _user        := tudu.latest_user();
    
    if _user_id <> _user.user_id then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;
    
    if _user.email <> _old_email then
        select assert.fail('should not change email') into _message;
        return _message;
    end if;
    
    if _user.password_hash <> _old_pw_hash then
        select assert.fail('should not change password hash') into _message;
        return _message;
    end if;
    
    if exists (select 1 from tudu_user_log where user_id = _user_id and operation = 'change_email') then
        select assert.fail('should not create a user log entry with operation "change_email"') into _message;
        return _message;
    end if;
    
    if exists (select 1 from tudu_user_log where user_id = _user_id and operation = 'change_password_hash') then
        select assert.fail('should not create a user log entry with operation "change_password_hash"') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.update_user_with_identical_email_and_password_hash() returns test_result as $$
declare
    _message        test_result;
    _user           tudu_user%ROWTYPE;
    _old_email      varchar;
    _old_pw_hash    varchar;
    _user_id        bigint;
begin
    _user        := tudu.create_random_user();
    _old_email   := _user.email;
    _old_pw_hash := _user.password_hash;
    _user_id     := tudu.update_user(_user.user_id, _user.email, _user.password_hash);
    _user        := tudu.latest_user();
    
    if _user_id <> _user.user_id then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;
    
    if _user.email <> _old_email then
        select assert.fail('should not change email') into _message;
        return _message;
    end if;
    
    if _user.password_hash <> _old_pw_hash then
        select assert.fail('should not change password hash') into _message;
        return _message;
    end if;
    
    if exists (select 1 from tudu_user_log where user_id = _user_id and operation = 'change_email') then
        select assert.fail('should not create a user log entry with operation "change_email"') into _message;
        return _message;
    end if;
    
    if exists (select 1 from tudu_user_log where user_id = _user_id and operation = 'change_password_hash') then
        select assert.fail('should NOT create a user log entry with operation "change_password_hash"') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.update_user_using_invalid_user_id() returns test_result as $$
declare
    _message    test_result;
    _user_id    bigint;
begin
    _user_id := tudu.update_user(-1, 'new@email.set', 'pw_hash');
    
    if _user_id <> -1 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    if exists (
        select 1 from tudu_user_log
        where user_id = _user_id
    ) then
        select assert.fail('should NOT create any user log entries.') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.update_user_using_already_taken_email() returns test_result as $$
declare
    _message        test_result;
    _user1          tudu_user%ROWTYPE;
    _user2          tudu_user%ROWTYPE;
    _user_id        bigint;
begin
    _user1   := tudu.create_random_user();
    _user2   := tudu.create_random_user();
    _user_id := tudu.update_user(_user2.user_id, _user1.email, null);
    
    if _user_id <> -2 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;
