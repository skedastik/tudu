revoke all on tudu_user from tudu_role_web;
revoke all on tudu_task from tudu_role_web;
revoke all on all functions in schema tudu from tudu_role_web;

drop role tudu_role_web;
create role tudu_role_web;

grant select on table tudu_user to tudu_role_web;
grant select on table tudu_task to tudu_role_web;
grant execute on all functions in schema tudu to tudu_role_web;

/* create role tudu_user_web with login encrypted password 'changeme' in role tudu_role_web; */
