\i drop_all.sql;
\i create.sql;

/* TODO: Explore options for automated testing */

create or replace function play() returns void as $$
declare
    _uid integer;
begin
    insert into tudu_user (email, pw_hash) values('foo@bar.com', 'xxx');
    
    insert into tudu_note (note_text, user_id) values('Red Wine Gastrique', 1);
    insert into tudu_note (note_text, user_id) values('Bordeaux', 1);
    
    insert into tudu_tag (tag_text) values('food');
    insert into tudu_tag (tag_text) values('wine');
    
    insert into tudu_note_tag (note_id, tag_id) values (1, 1);
    insert into tudu_note_tag (note_id, tag_id) values (1, 2);
    insert into tudu_note_tag (note_id, tag_id) values (2, 2);
    
    /* fails, as it should */
    /* delete from tudu_tag where tag_id = 1; */
    
    /* should succeed and cascade delete to tudu_note_tag */
    delete from tudu_note where note_id = 1;
    
    /* 'food' tag should have been deleted automatically */
end;
$$ language plpgsql security definer;

select play();

select * from tudu_tag;
select * from tudu_note_tag;