/**
 * Get all distinct active-task tags for the given user_id
 */
create or replace function tudu.get_active_task_tags_for_user(_user_id bigint) returns setof record as $$
    select distinct unnest(tags)
    from tudu.task
    where user_id = _user_id and status not in ('deleted', 'finished');
$$ language sql security definer;
