/* test */

create or replace function unit_tests.create_task() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
begin
    _user := tudu.create_random_user();
    perform tudu.create_task(
        _user.user_id,
        'Learn to play Smoke on the Water',
        /* simultaneously test tag normalization */
        array['guitar', null, '   gUiTar   ', 'MuSic', '']
    );
    _task := tudu.latest_task();
    
    if _task is null then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;
    
    if _task.user_id <> _user.user_id then
        select assert.fail('should create a task with appropriate user_id') into _message;
        return _message;
    end if;
    
    if _task.description <> 'Learn to play Smoke on the Water' then
        select assert.fail('should create a task with description "Learn to play Smoke on the Water"') into _message;
        return _message;
    end if;
    
    if _task.tags is distinct from array['guitar', 'gUiTar', 'MuSic']::varchar[] then
        select assert.fail('should normalize tags (remove nulls and empty strings, trim whitespace)') into _message;
        return _message;
    end if;
    
    /* TODO: test logging */
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;
