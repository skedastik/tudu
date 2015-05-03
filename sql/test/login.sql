/* test */

create or replace function unit_tests.create_access_token() returns test_result as $$
declare
    _message            test_result;
    _user               tudu_user%ROWTYPE;
    _access_token_id    bigint;
    _access_token       tudu_access_token%ROWTYPE;
    _access_token_log   tudu_access_token_log%ROWTYPE;
begin
    _user               := tudu.create_random_user();
    _access_token_id    := tudu.create_access_token(_user.user_id, _user.password_hash, 'silly-token-string', '127.0.0.1');
    _access_token       := tudu.latest_access_token();
    _access_token_log   := tudu.latest_access_token_log();
    
    if _access_token_id < 0 then
        select assert.fail('should succeed.') into _message;
        return _message;
    end if;
    
    if _access_token.user_id is distinct from _user.user_id then
        select assert.fail('should create an access token with appropriate user_id') into _message;
        return _message;
    end if;
    
    if _access_token.token_string <> 'silly-token-string' then
        select assert.fail('should create an access token with token_string "silly-token_string-string"') into _message;
        return _message;
    end if;
    
    if _access_token.status <> 'active' then
        select assert.fail('should create an access token with status "active"') into _message;
        return _message;
    end if;
    
    if _access_token_log.token_id is distinct from _access_token.token_id then
        select assert.fail('should create an access token log entry with matching token_id') into _message;
        return _message;
    end if;
    
    if _access_token_log.operation <> 'create' then
        select assert.fail('should create an access token log entry with operation "create"') into _message;
        return _message;
    end if;
    
    if _access_token_log.ip <> '127.0.0.1' then
        select assert.fail('should create an access token log entry with ip "127.0.0.1"') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.revoke_active_access_token() returns test_result as $$
declare
    _message            test_result;
    _user               tudu_user%ROWTYPE;
begin
    _user := tudu.create_random_user();
    
    perform tudu.create_access_token(
        _user.user_id,
        _user.password_hash,
        'token-string',
        '127.0.0.1'
    );
    
    perform tudu.revoke_active_access_token(_user.user_id, '127.0.0.1');
    
    if exists (select 1 from tudu_access_token where user_id = _user.user_id and status = 'active') then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.create_another_valid_access_token() returns test_result as $$
declare
    _message            test_result;
    _user               tudu_user%ROWTYPE;
    _access_token_id    bigint;
begin
    _user := tudu.create_random_user();
    
    perform tudu.create_access_token(
        _user.user_id,
        _user.password_hash,
        'token-string',
        '127.0.0.1'
    );
    
    _access_token_id := tudu.create_access_token(
        _user.user_id,
        _user.password_hash,
        'different-token-string',
        '127.0.0.1'
    );
    
    if _access_token_id < 0 then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.create_access_token_with_nonexistent_user() returns test_result as $$
declare
    _message            test_result;
    _access_token_id    bigint;
begin
    _access_token_id := tudu.create_access_token(-1, 'jhdsafkj', 'silly-token-string', '127.0.0.1');
    
    if _access_token_id <> -1 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.create_access_token_with_mismatched_password() returns test_result as $$
declare
    _message            test_result;
    _access_token_id    bigint;
    _user               tudu_user%ROWTYPE;
begin
    _user := tudu.create_random_user();
    _access_token_id := tudu.create_access_token(
        _user.user_id,
        _user.password_hash || 'mismatched',
        'silly-token-string',
        '127.0.0.1'
    );
    
    if _access_token_id <> -2 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.create_access_token_with_duplicate_token_string() returns test_result as $$
declare
    _message            test_result;
    _access_token_id    bigint;
    _user               tudu_user%ROWTYPE;
begin
    _user := tudu.create_random_user();

    perform tudu.create_access_token(
        _user.user_id,
        _user.password_hash,
        'silly-token-string',
        '127.0.0.1'
    );

    _access_token_id := tudu.create_access_token(
        _user.user_id,
        _user.password_hash,
        'silly-token-string',
        '127.0.0.1'
    );

    if _access_token_id <> -3 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;

    if not exists (select 1 from tudu_access_token where user_id = _user.user_id and status = 'active') then
        select assert.fail('should NOT revoke the currently active access token') into _message;
        return _message;
    end if;

    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;
