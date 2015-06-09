/**
 * Create a new task for an existing user.
 * 
 * Arguments
 *   _user_id       ID of existing user
 *   _description   Task description
 *   _tags          Array of tag strings
 *   _ip            Optional IP address
 *   _kvs           Optional KVS data
 * 
 * Returns
 *   Task ID on success
 *   -1 if user ID is not valid
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
    
    if not exists (select 1 from tudu_user where user_id = _user_id) then
        return -1;
    end if;
    
    insert into tudu_task (task_id, user_id, description, tags, kvs)
    values (_task_id, _user_id, _description, _tags, _kvs);
    
    perform tudu.task_log_add(_task_id, 'create', _ip);
    
    return _task_id;
end;
$$ language plpgsql security definer;

/**
 * Update an existing task.
 * 
 * Arguments
 *   _task_id       ID of existing task
 *   _description   (optional) Task description
 *   _tags          (optional) Array of tags or empty array to remove tags
 *   _ip            Optional IP address
 * 
 * Returns
 *   ID of task on success
 *   -1 if task ID is invalid
 *   -2 if task has been deleted
 *   -3 if task is not in an alterable state
 */
create or replace function tudu.update_task(
    _task_id            bigint,
    _new_description    varchar     default null,
    _new_tags           varchar[]   default null,
    _ip                 inet        default null
) returns bigint as $$
declare
    _description    varchar;
    _tags           varchar[];
    _status         varchar;
begin
    select task_id, tags, description, status
    into _task_id, _tags, _description, _status
    from tudu_task where task_id = _task_id;
    
    if _task_id is null then
        return -1;
    end if;
    
    if _status = 'deleted' then
        return -2;
    end if;
    
    if _status <> 'active' then
        return -3;
    end if;
    
    update tudu_task
    set description = coalesce(_new_description, description),
        tags        = case when _new_tags is null then tags
                           when array_dims(_new_tags) is null then null
                           else _new_tags
                      end,
        edate       = now()
    where task_id = _task_id;
    
    if _new_tags is not null then
        perform tudu.task_log_add(
            _task_id,
            'update_tags',
            _ip,
            hstore(array[
                ['old_tags', _tags::text],
                ['new_tags', _new_tags::text]
            ])
        );
    end if;
    
    if _new_description is not null and _new_description <> _description then
        perform tudu.task_log_add(
            _task_id,
            'update_description',
            _ip,
            hstore(array[
                ['old_description', _description],
                ['new_description', _new_description]
            ])
        );
    end if;
    
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
    
    if _status <> 'active' then
        return -3;
    end if;
    
    update tudu_task
    set status        = 'finished',
        finished_date = now(),
        edate         = now()
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
 *   _kvs           Optional HSTORE
 *   _info          Optional info string
 */
create or replace function tudu.task_log_add(
    _task_id        bigint,
    _operation      varchar,
    _ip             inet        default null,
    _kvs            hstore      default '',
    _info           text        default null
) returns void as $$
begin
    insert into tudu_task_log (task_id, operation, ip, info, kvs)
    values (_task_id, _operation, _ip, _info, _kvs);
end;
$$ language plpgsql security definer;
