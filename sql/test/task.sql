/* test */

create or replace function unit_tests.create_task() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task_id    bigint;
begin
    _user    := tudu.create_random_user();
    _task_id := tudu.create_task(
        _user.user_id,
        'Learn to play Smoke on the Water',
        /* simultaneously test tag normalization */
        array['guitar', '   guitar   ', 'music', '', null]
    );
    
    /* TODO: assertions */
    /* TODO: test logging */
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;
