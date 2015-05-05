/**
 * Create a new task for an existing user.
 * 
 * Arguments
 *   _user_id       ID of existing user
 *   _description   Task description
 *   _tags          Array of tag strings. This array will be normalized: 
 *                  duplicates, NULLs, and empty strings are removed,
 *   _kvs           Optional KVS data
 *   _ip            Optional IP address
 * 
 * Returns
 *   ID of new task on success
 */
create or replace function tudu.create_task(
    _user_id        bigint,
    _description    text,
    _tags           varchar[],
    _ip             inet default null,
    _kvs            hstore default ''
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
    _ip             inet default null,
    _info           text default null,
    _kvs            hstore default ''
) returns void as $$
begin
    insert into tudu_task_log (task_id, operation, ip, info, kvs)
    values (_task_id, _operation, _ip, _info, _kvs);
end;
$$ language plpgsql security definer;
