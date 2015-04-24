create table tudu_user (
    user_id serial primary key,
    email varchar(128) not null,
    pw_hash varchar(256) not null,
    date_created timestamp not null default current_timestamp
);

create table tudu_note (
    note_id serial primary key,
    note_text text not null,
    -- ordinal double precision not null unique,
    user_id integer references tudu_user
);

create table tudu_tag (
    tag_id serial primary key,
    tag_text varchar(32) unique
);

create table tudu_note_tag (
    -- if a note is deleted, delete corresponding note/tag link
    note_id integer references tudu_note on delete cascade,
    -- forbid deletion of tags that are linked to notes
    tag_id integer references tudu_tag on delete restrict,
    unique (note_id, tag_id)
);

-- Triggers --------------------------------------------------------------------

/**
 * Whenever a tag is removed from a note, delete the tag if no other notes
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

create trigger tudu_note_tag_deletion
after delete on tudu_note_tag
for each row
execute procedure attempt_tag_deletion();

/*create table tudu_access_token (
    token_id serial primary key,
    token_string char(256) not null unique,
    date_created timestamp not null default current_timestamp,
    revoked boolean not null default false,
    user_id integer references tudu_user
);*/