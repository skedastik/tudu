/* test */

create or replace function unit_tests.create_task() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
    _task_id    bigint;
    _task_log   tudu_task_log%ROWTYPE;
begin
    _user     := tudu.create_random_user();
    _task_id  := tudu.create_task(
        _user.user_id,
        'Learn to play Smoke on the Water',
        /* simultaneously test tag normalization */
        array['guitar', null, '   gUiTar   ', 'MuSic', ''],
        '127.0.0.1'
    );
    _task     := tudu.latest_task();
    _task_log := tudu.latest_task_log();
    
    if _task_id < 0 then
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
    
    if _task.finished_date is not null then
        select assert.fail('should create a task with a NULL finished_date') into _message;
        return _message;
    end if;
    
    if _task.status <> 'init' then
        select assert.fail('should create a task with status "init"') into _message;
        return _message;
    end if;
    
    if _task_log.task_id <> _task.task_id then
        select assert.fail('should create a task log entry with matching task_id') into _message;
        return _message;
    end if;
    
    if _task_log.operation <> 'create' then
        select assert.fail('should create a task log entry with operation "create"') into _message;
        return _message;
    end if;
    
    if _task_log.ip <> '127.0.0.1' then
        select assert.fail('should create a task log entry with ip "127.0.0.1"') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.set_task_tags() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
    _task_id    bigint;
    _task_log   tudu_task_log%ROWTYPE;
    _old_tags   varchar[];
begin
    _user     := tudu.create_random_user();
    _task     := tudu.create_random_task(_user.user_id);
    _old_tags := _task.tags;
    _task_id  := tudu.set_task_tags(_task.task_id, array['   foo   ', null, E'\n  bar  \r', '']);
    _task     := tudu.latest_task();
    _task_log := tudu.latest_task_log();
    
    if _task_id <> _task.task_id then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;
    
    if _task.tags is distinct from array['foo', 'bar']::varchar[] then
        select assert.fail(E'should set tags to array[\'foo\', \'bar\']') into _message;
        return _message;
    end if;
    
    if _task_log.task_id <> _task_id then
        select assert.fail('should create a task log entry with matching task_id') into _message;
        return _message;
    end if;
    
    if _task_log.operation <> 'set_tags' then
        select assert.fail('should create a task log entry with operation "set_tags"') into _message;
        return _message;
    end if;
    
    if _task_log.kvs->'old_tags' <> _old_tags::text or _task_log.kvs->'new_tags' <> _task.tags::text then
        select assert.fail('should create a task log entry with appropriate "old_tags" and "new_tags" key/value pairs') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.set_task_tags_using_invalid_task_id() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
    _task_id    bigint;
    _task_log   tudu_task_log%ROWTYPE;
begin
    _user     := tudu.create_random_user();
    _task     := tudu.create_random_task(_user.user_id);
    _task_id  := tudu.set_task_tags(-1, array['foo', 'bar']);
    _task_log := tudu.latest_task_log();
    
    if _task_id <> -1 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    if _task_log.operation = 'set_tags' then
        select assert.fail('should NOT create a task log entry') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.set_task_tags_using_identical_tags() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task_id    bigint;
    _task_log   tudu_task_log%ROWTYPE;
begin
    _user     := tudu.create_random_user();
    _task_id  := tudu.create_task(
        _user.user_id,
        'Learn to play Smoke on the Water',
        array['guitar', null, '   Guitar   ', E'\tMusicTime\t', '']
    );
    _task_id  := tudu.set_task_tags(_task_id, array[null, E'\rguitar\n', '   Guitar   ', '', '    MusicTime   ']);
    _task_log := tudu.latest_task_log();

    if _task_id <> -2 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;

    if _task_log.operation = 'set_tags' then
        select assert.fail('should NOT create a task log entry') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.delete_task() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
    _task_id    bigint;
    _task_log   tudu_task_log%ROWTYPE;
begin
    _user     := tudu.create_random_user();
    _task     := tudu.create_random_task(_user.user_id);
    _task_id  := tudu.delete_task(_task.task_id);
    _task     := tudu.latest_task();
    _task_log := tudu.latest_task_log();
    
    if _task_id <> _task.task_id then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;
    
    if _task.status <> 'deleted' then
        select assert.fail('should set status to "deleted"') into _message;
        return _message;
    end if;
    
    if _task_log.task_id <> _task_id then
        select assert.fail('should create a task log entry with matching task_id') into _message;
        return _message;
    end if;
    
    if _task_log.operation <> 'delete' then
        select assert.fail('should create a task log entry with operation "delete"') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.delete_task_using_invalid_task_id() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
    _task_id    bigint;
    _task_log   tudu_task_log%ROWTYPE;
begin
    _user     := tudu.create_random_user();
    _task     := tudu.create_random_task(_user.user_id);
    _task_id  := tudu.delete_task(-1);
    _task     := tudu.latest_task();
    _task_log := tudu.latest_task_log();
    
    if _task_id <> -1 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    if _task_log.operation = 'delete' then
        select assert.fail('should NOT create a task log entry') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.delete_task_that_has_already_been_deleted() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
    _task_id    bigint;
begin
    _user     := tudu.create_random_user();
    _task     := tudu.create_random_task(_user.user_id);
    perform tudu.delete_task(_task.task_id);
    _task_id  := tudu.delete_task(_task.task_id);
    _task     := tudu.latest_task();
    
    if _task_id <> -2 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.finish_task() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
    _task_id    bigint;
    _task_log   tudu_task_log%ROWTYPE;
begin
    _user     := tudu.create_random_user();
    _task     := tudu.create_random_task(_user.user_id);
    _task_id  := tudu.finish_task(_task.task_id);
    _task     := tudu.latest_task();
    _task_log := tudu.latest_task_log();
    
    if _task_id <> _task.task_id then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;
    
    if _task.status <> 'finished' then
        select assert.fail('should set status to "finished"') into _message;
        return _message;
    end if;
    
    if _task_log.task_id <> _task_id then
        select assert.fail('should create a task log entry with matching task_id') into _message;
        return _message;
    end if;
    
    if _task_log.operation <> 'finish' then
        select assert.fail('should create a task log entry with operation "finish"') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.finish_task_using_invalid_task_id() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
    _task_id    bigint;
    _task_log   tudu_task_log%ROWTYPE;
begin
    _user     := tudu.create_random_user();
    _task     := tudu.create_random_task(_user.user_id);
    _task_id  := tudu.finish_task(-1);
    _task     := tudu.latest_task();
    _task_log := tudu.latest_task_log();
    
    if _task_id <> -1 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    if _task_log.operation = 'finish' then
        select assert.fail('should NOT create a task log entry') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.finish_task_that_has_already_been_finished() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
    _task_id    bigint;
begin
    _user     := tudu.create_random_user();
    _task     := tudu.create_random_task(_user.user_id);
    perform tudu.finish_task(_task.task_id);
    _task_id  := tudu.finish_task(_task.task_id);
    _task     := tudu.latest_task();
    
    if _task_id <> -2 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.finish_task_in_incompatible_state() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
    _task_id    bigint;
begin
    _user     := tudu.create_random_user();
    _task     := tudu.create_random_task(_user.user_id);
    perform tudu.delete_task(_task.task_id);
    _task_id  := tudu.finish_task(_task.task_id);
    _task     := tudu.latest_task();
    
    if _task_id <> -3 then
        select assert.fail('should fail for a task that has been deleted') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;
