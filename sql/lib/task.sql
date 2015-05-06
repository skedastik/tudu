/**
 * Create a new task for an existing user.
 * 
 * Arguments
 *   _user_id       ID of existing user
 *   _description   Task description
 *   _tags          Array of tag strings. This array will be normalized: NULLs
 *                  and empty strings are removed, while whitespace is trimmed.
 *   _ip            Optional IP address
 *   _kvs           Optional KVS data
 * 
 * Returns
 *   ID of new task on success
 */
create or replace function tudu.create_task(
    _user_id        bigint,
    _description    varchar,
    _tags           varchar[],
    _ip             inet        default null,
    _kvs            hstore      default ''
) returns bigint as $$
declare
    _task_id        bigint;
begin
    _task_id := nextval('tudu_task_seq');
    _tags    := util.denull_btrim_whitespace(_tags);
    
    insert into tudu_task (task_id, user_id, description, tags, kvs)
    values (_task_id, _user_id, _description, _tags, _kvs);
    
    perform tudu.task_log_add(_task_id, 'create', _ip);
    
    return _task_id;
end;
$$ language plpgsql security definer;

/**
 * Update an existing task's tags.
 * 
 * Arguments
 *   _task_id       ID of existing task
 *   _tags          Array of tag strings or NULL to remove all tags. This array
 *                  will be normalized: NULLs and empty strings are removed,
 *                  whitespace is trimmed.
 *   _ip            Optional IP address
 *                  
 * Returns
 *   ID of task on success
 *   -1 if task ID is invalid
 *   -2 if tags are identical (different order is NOT considered identical)
 */
create or replace function tudu.set_task_tags(
    _task_id    bigint,
    _new_tags   varchar[],
    _ip         inet        default null
) returns bigint as $$
declare
    _tags       varchar[];
begin
    _new_tags := util.denull_btrim_whitespace(_new_tags);
    select task_id, tags into _task_id, _tags from tudu_task where task_id = _task_id;
    
    if _task_id is null then
        return -1;
    end if;
    
    if _tags is not distinct from _new_tags then
        return -2;
    end if;
    
    update tudu_task
    set tags  = _new_tags,
        edate = now()
    where task_id = _task_id;
    
    perform tudu.task_log_add(_task_id, 'set_tags', _ip);
    
    return _task_id;
end;
$$ language plpgsql security definer;

/**
 * Mark an existing task as deleted.
 * 
 * Arguments
 *   _task_id   ID of existing task
 *   _ip        Optional IP address
 * 
 * Returns
 *   ID of task on success
 *   -1 if task ID is invalid
 *   -2 if task is already deleted
 */
create or replace function tudu.delete_task(
    _task_id    bigint,
    _ip         inet        default null
) returns bigint as $$
declare
    _status     varchar;
begin
    select task_id, status into _task_id, _status from tudu_task where task_id = _task_id;
    
    if _task_id is null then
        return -1;
    end if;
    
    if _status = 'deleted' then
        return -2;
    end if;
    
    update tudu_task
    set status = 'deleted',
        edate  = now()
    where task_id = _task_id;
    
    perform tudu.task_log_add(_task_id, 'delete', _ip);
    
    return _task_id;
end;
$$ language plpgsql security definer;

/**
 * Mark an existing task as finished.
 * 
 * Arguments
 *   _task_id   ID of existing task
 *   _ip        Optional IP address
 * 
 * Returns
 *   ID of task on success
 *   -1 if task ID is invalid
 *   -2 if task is already finished
 *   -3 if task is in an incompatible state (e.g. deleted)
 */
create or replace function tudu.finish_task(
    _task_id    bigint,
    _ip         inet        default null
) returns bigint as $$
declare
    _status     varchar;
begin
    select task_id, status into _task_id, _status from tudu_task where task_id = _task_id;
    
    if _task_id is null then
        return -1;
    end if;
    
    if _status = 'finished' then
        return -2;
    end if;
    
    if _status <> 'init' then
        return -3;
    end if;
    
    update tudu_task
    set status = 'finished',
        edate  = now()
    where task_id = _task_id;
    
    perform tudu.task_log_add(_task_id, 'finish', _ip);
    
    return _task_id;
end;
$$ language plpgsql security definer;

/**
 * Log a task operation.
 * 
 * Arguments
 *   _task_id       Task ID
 *   _operation     An operation string
 *   _ip            Optional IP address
 *   _info          Optional info string
 *   _kvs           Optional HSTORE
 */
create or replace function tudu.task_log_add(
    _task_id        bigint,
    _operation      varchar,
    _ip             inet        default null,
    _info           text        default null,
    _kvs            hstore      default ''
) returns void as $$
begin
    insert into tudu_task_log (task_id, operation, ip, info, kvs)
    values (_task_id, _operation, _ip, _info, _kvs);
end;
$$ language plpgsql security definer;
