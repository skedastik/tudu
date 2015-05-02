/* test */

create or replace function unit_tests.create_access_token() returns test_result as $$
declare
    _message        test_result;
    _user           tudu_user%ROWTYPE;
    _access_token   tudu_access_token%ROWTYPE;
begin
    _user := tudu.create_random_user();
    perform tudu.create_access_token(_user.user_id, _user.password_hash, 'silly-token-string', '127.0.0.1');
    _access_token := tudu.latest_access_token();
    
    /* TODO */
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;
