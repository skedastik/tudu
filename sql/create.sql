create table tudu_user (
    user_id serial primary key,
    email varchar(128) not null,
    password_hash varchar(256) not null,
    date_created timestamp not null default current_timestamp
);

create table tudu_task (
    task_id serial primary key,
    task_description text not null,
    /* index this. tasks will be displayed in reverse-chronological order */
    date_created timestamp not null default current_timestamp,
    /* null date indicates task has not been completed */
    /* index this. partition on this also? there will not be much interaction
     * with completed tasks. in fact, all interaction with completed tasks will
     * take place in the history viewer. partitioning could make sense. */
    date_completed timestamp,
    user_id integer references tudu_user
);

create table tudu_tag (
    tag_id serial primary key,
    tag_text varchar(32) unique
);

create table tudu_task_tag (
    -- if a task is deleted, delete corresponding task/tag link
    task_id integer references tudu_task on delete cascade,
    -- forbid deletion of tags that are linked to tasks
    tag_id integer references tudu_tag on delete restrict,
    unique (task_id, tag_id)
);

create table tudu_access_token (
    token_id serial primary key,
    token_string char(256) not null unique,
    date_created timestamp not null default current_timestamp,
    revoked boolean not null default false,
    user_id integer references tudu_user
);

-- Triggers --------------------------------------------------------------------

/**
 * Whenever a tag is removed from a task, delete the tag if no other tasks
 * reference it.
 */
create or replace function attempt_tag_deletion() returns trigger as $$
begin
    delete from tudu_tag where tag_id = OLD.tag_id;
    return null;
exception
    when foreign_key_violation
    then return null;
end;
$$ language plpgsql security definer;

create trigger tudu_task_tag_deletion
after delete on tudu_task_tag
for each row
execute procedure attempt_tag_deletion();
