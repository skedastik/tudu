/* test */

create or replace function unit_tests.signup_user() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _user_log   tudu_user_log%ROWTYPE;
begin
    perform tudu.signup_user('Foo@BaR.baz', 'djiosd', 'ahdinsdjnsdkfjkul', '127.0.0.1');
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
    
    /* simultaneously test for case insensitivity */
    _result := tudu.signup_user('BaZ@qUx.gAr', 'ndfaksh', 'iuiowernsdlfio', '127.0.0.1');
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
    perform tudu.signup_user('super@user.win', 'biawebd', 'asduytcwfebnail', '127.0.0.1');
    
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
    perform tudu.signup_user('iheart@turtles.gmn', 'fsdmnf', 'opoytouyi');
    
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

create or replace function unit_tests.set_user_password_hash() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _user_id    bigint;
    _user_log   tudu_user_log%ROWTYPE;
begin
    _user     := tudu.create_random_user();
    _user_id  := tudu.set_user_password_hash(_user.user_id, _user.password_hash, 'new-unlikely-password-hash', 'new-unlikely-salt');
    _user     := tudu.latest_user();
    _user_log := tudu.latest_user_log();
    
    if _user_id <> _user.user_id then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;
    
    if _user.password_hash <> 'new-unlikely-password-hash' then
        select assert.fail('should set password_hash to "new-unlikely-password-hash"') into _message;
        return _message;
    end if;
    
    if _user.password_salt <> 'new-unlikely-salt' then
        select assert.fail('should set password_salt to "new-unlikely-salt"') into _message;
        return _message;
    end if;
    
    if _user_log.user_id <> _user_id then
        select assert.fail('should create a user log entry with matching user_id') into _message;
        return _message;
    end if;
    
    if _user_log.operation <> 'set_password_hash' then
        select assert.fail('should create a user log entry with operation "set_password_hash"') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.set_user_password_hash_using_invalid_user_id() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _user_id    bigint;
    _user_log   tudu_user_log%ROWTYPE;
begin
    _user     := tudu.create_random_user();
    _user_id  := tudu.set_user_password_hash(-1, _user.password_hash, 'new-unlikely-password-hash', 'new-unlikely-salt');
    _user     := tudu.latest_user();
    _user_log := tudu.latest_user_log();
    
    if _user_id <> -1 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    if _user_log.operation = 'set_password_hash' then
        select assert.fail('should NOT create a user log entry') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.set_user_password_hash_using_mismatched_old_password_hash() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _user_id    bigint;
    _user_log   tudu_user_log%ROWTYPE;
begin
    _user     := tudu.create_random_user();
    _user_id  := tudu.set_user_password_hash(_user.user_id, 'mismatched-password-hash', 'new-unlikely-password-hash', 'new-salt');
    _user     := tudu.latest_user();
    _user_log := tudu.latest_user_log();
    
    if _user_id <> -2 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    if _user_log.operation = 'set_password_hash' then
        select assert.fail('should NOT create a user log entry') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.set_user_password_hash_using_identical_new_password_hash() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _user_id    bigint;
    _user_log   tudu_user_log%ROWTYPE;
begin
    _user     := tudu.create_random_user();
    _user_id  := tudu.set_user_password_hash(_user.user_id, _user.password_hash, _user.password_hash, 'new-salt');
    _user     := tudu.latest_user();
    _user_log := tudu.latest_user_log();
    
    if _user_id <> -3 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    if _user_log.operation = 'set_password_hash' then
        select assert.fail('should NOT create a user log entry') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.set_user_password_hash_using_identical_password_salt() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _user_id    bigint;
    _user_log   tudu_user_log%ROWTYPE;
begin
    _user     := tudu.create_random_user();
    _user_id  := tudu.set_user_password_hash(_user.user_id, _user.password_hash, 'new-unlikely-password-hash', _user.password_salt);
    _user     := tudu.latest_user();
    _user_log := tudu.latest_user_log();
    
    if _user_id <> -4 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    if _user_log.operation = 'set_password_hash' then
        select assert.fail('should NOT create a user log entry') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.set_user_email() returns test_result as $$
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
    _user_id   := tudu.set_user_email(_user.user_id, '   New@Email.Set   ');
    _user      := tudu.latest_user();
    _user_log  := tudu.latest_user_log();
    
    if _user_id <> _user.user_id then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;
    
    if _user.email <> 'New@Email.Set' then
        select assert.fail('should set email to "New@Email.Set"') into _message;
        return _message;
    end if;
    
    if _user_log.user_id <> _user_id then
        select assert.fail('should create a user log entry with matching user_id') into _message;
        return _message;
    end if;
    
    if _user_log.operation <> 'set_email' then
        select assert.fail('should create a user log entry with operation "set_email"') into _message;
        return _message;
    end if;
    
    if _user_log.kvs->'old_email' <> _old_email or _user_log.kvs->'new_email' <> 'New@Email.Set' then
        select assert.fail('should create a user log entry with appropriate "old_email" and "new_email" key/value pairs') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.set_user_email_using_invalid_user_id() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _user_id    bigint;
    _user_log   tudu_user_log%ROWTYPE;
begin
    _user     := tudu.create_random_user();
    _user_id  := tudu.set_user_email(-1, 'new@email.set');
    _user     := tudu.latest_user();
    _user_log := tudu.latest_user_log();
    
    if _user_id <> -1 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    if _user_log.operation = 'set_email' then
        select assert.fail('should NOT create a user log entry') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.set_user_email_to_identical_existing_email() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _user_id    bigint;
    _user_log   tudu_user_log%ROWTYPE;
begin
    _user     := tudu.create_random_user();
    _user_id  := tudu.set_user_email(_user.user_id, _user.email);
    _user     := tudu.latest_user();
    _user_log := tudu.latest_user_log();
    
    if _user_id <> -2 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    if _user_log.operation = 'set_email' then
        select assert.fail('should NOT create a user log entry') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.get_user_by_id() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _result     tudu_user%ROWTYPE;
begin
    _user   := tudu.create_random_user();
    _result := tudu.get_user_by_id(_user.user_id);
    
    if _result is null then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;
    
    if _result.email <> _user.email then
        select assert.fail('should select a user with matching email') into _message;
        return _message;
    end if;
    
    if _result.password_salt <> _user.password_salt then
        select assert.fail('should select a user with matching password salt') into _message;
        return _message;
    end if;
    
    if _result.password_hash <> _user.password_hash then
        select assert.fail('should select a user with matching password hash') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.set_user_email_to_already_taken_email() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _user_id    bigint;
    _user_log   tudu_user_log%ROWTYPE;
begin
    perform tudu.signup_user('this.email.is.taken@ohnoes.woe', 'dsfkbn', 'mnsafdubahksdf');
    _user     := tudu.create_random_user();
    _user_id  := tudu.set_user_email(_user.user_id, 'this.email.is.taken@ohnoes.woe');
    _user     := tudu.latest_user();
    _user_log := tudu.latest_user_log();
    
    if _user_id <> -3 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    if _user_log.operation = 'set_email' then
        select assert.fail('should NOT create a user log entry') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;
