/* test */

create or replace function unit_tests.create_task() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
    _task_id    bigint;
    _task_log   tudu_task_log%ROWTYPE;
    _tags       varchar[];
begin
    _user     := tudu.create_random_user();
    _tags     := array['guitar', 'guitar', 'music'];
    _task_id  := tudu.create_task(
        _user.user_id,
        'Learn to play Smoke on the Water',
        /* simultaneously test tag normalization */
        _tags,
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
    
    if _task.tags is distinct from _tags then
        select assert.fail('should create task with tags {"guitar", "guitar", "music"}') into _message;
        return _message;
    end if;
    
    if _task.finished_date is not null then
        select assert.fail('should create a task with a NULL finished_date') into _message;
        return _message;
    end if;
    
    if _task.status <> 'active' then
        select assert.fail('should create a task with status "active"') into _message;
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

create or replace function unit_tests.create_task_using_nonexistent_user_id() returns test_result as $$
declare
    _message    test_result;
    _result     bigint;
    _task_log   tudu_task_log%ROWTYPE;
begin
    _result := tudu.create_task(
        -1,
        'Learn to play Smoke on the Water',
        array['guitar'],
        '127.0.0.1'
    );
    _task_log  := tudu.latest_task_log();
    
    if _result <> -1 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    if _task_log.task_id is not null then
        select assert.fail('should NOT create a task log entry') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.update_task() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
    _task_id    bigint;
    _old_desc   varchar;
    _old_tags   varchar[];
    _new_tags   varchar[];
    _new_desc   varchar;
begin
    _user     := tudu.create_random_user();
    _task     := tudu.create_random_task(_user.user_id);
    _old_desc := _task.description;
    _old_tags := _task.tags;
    _new_desc := 'Test #foo #bar';
    _new_tags := array['foo', 'bar'];
    _task_id := tudu.update_task(
        _task.task_id,
        _new_desc,
        _new_tags
    );
    _task := tudu.latest_task();

    if _task_id <> _task.task_id then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;

    if _task.description <> _new_desc then
        select assert.fail('should set description to "' || _new_desc || '"') into _message;
        return _message;
    end if;

    if _task.tags is distinct from array['foo', 'bar']::varchar[] then
        select assert.fail('should set tags to {"foo", "bar"}') into _message;
        return _message;
    end if;

    if not exists (
        select 1 from tudu_task_log
        where task_id = _task_id
          and operation = 'update_description'
          and kvs->'old_description' = _old_desc
          and kvs->'new_description' = _new_desc
    ) then
        select assert.fail('should create a task log entry with matching task_id, operation "update_description", and KVS with correct "old_description" and "new_description" keys') into _message;
        return _message;
    end if;

    if not exists (
        select 1 from tudu_task_log
        where task_id = _task_id
          and operation = 'update_tags'
          and kvs->'old_tags' = _old_tags::text
          and kvs->'new_tags' = _new_tags::text
    ) then
        select assert.fail('should create a task log entry with matching task_id, operation "update_tags", and KVS with correct "old_tags" and "new_tags" keys') into _message;
        return _message;
    end if;

    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.update_task_using_invalid_task_id() returns test_result as $$
declare
    _message    test_result;
    _result     bigint;
begin
    _result := tudu.update_task(-1, 'Test #foo #bar', array['foo', 'bar']);

    if _result <> -1 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;

    if exists (
        select 1 from tudu_task_log
        where operation = 'update_tags' or operation = 'update_description'
    ) then
        select assert.fail('should not create any task log entries') into _message;
        return _message;
    end if;

    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.update_task_using_null_values() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
    _task_id    bigint;
    _old_desc   varchar;
    _old_tags   varchar[];
begin
    _user     := tudu.create_random_user();
    _task     := tudu.create_random_task(_user.user_id);
    _old_desc := _task.description;
    _old_tags := _task.tags;
    _task_id  := tudu.update_task(_task.task_id, null, null);
    _task     := tudu.latest_task();

    if _task_id <> _task.task_id then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;

    if _task.description <> _old_desc then
        select assert.fail('should not change description') into _message;
        return _message;
    end if;

    if _task.tags is distinct from _old_tags then
        select assert.fail('should not change tags') into _message;
        return _message;
    end if;

    if exists (
        select 1 from tudu_task_log
        where operation = 'update_tags' or operation = 'update_description'
    ) then
        select assert.fail('should not create any task log entries') into _message;
        return _message;
    end if;

    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.update_task_with_deleted_status() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
    _result     bigint;
begin
    _user     := tudu.create_random_user();
    _task     := tudu.create_random_task(_user.user_id);
    update tudu_task set status = 'deleted' where task_id = _task.task_id;
    _result   := tudu.update_task(_task.task_id, null, array[]::varchar[]);

    if _result <> -2 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;

    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.update_task_with_non_active_status() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
    _result     bigint;
begin
    _user     := tudu.create_random_user();
    _task     := tudu.create_random_task(_user.user_id);
    update tudu_task set status = 'finished' where task_id = _task.task_id;
    _result   := tudu.update_task(_task.task_id, null, array[]::varchar[]);

    if _result <> -3 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;

    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.update_task_with_empty_tags_array() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
    _task_id    bigint;
begin
    _user     := tudu.create_random_user();
    _task     := tudu.create_random_task(_user.user_id);
    _task_id  := tudu.update_task(_task.task_id, null, array[]::varchar[]);
    _task     := tudu.latest_task();

    if _task_id <> _task.task_id then
        select assert.fail('should succeed') into _message;
        return _message;
    end if;

    if _task.tags is not null then
        select assert.fail('should set tags to NULL') into _message;
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
    
    if _task.finished_date <> now() then
        select assert.fail('should set finished_date to now()') into _message;
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
    
    if _task_id <> -3 then
        select assert.fail('should fail') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.finish_task_with_deleted_status() returns test_result as $$
declare
    _message    test_result;
    _user       tudu_user%ROWTYPE;
    _task       tudu_task%ROWTYPE;
    _task_id    bigint;
begin
    _user     := tudu.create_random_user();
    _task     := tudu.create_random_task(_user.user_id);
    update tudu_task set status = 'deleted' where task_id = _task.task_id;
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
