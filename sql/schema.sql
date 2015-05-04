create schema tudu;
create schema util;
create extension hstore;

create sequence tudu_user_seq              increment 1 start 1000 minvalue 1000;
create sequence tudu_task_seq              increment 1 start 1000 minvalue 1000;
create sequence tudu_access_token_seq      increment 1 start 1000 minvalue 1000;
create sequence tudu_user_log_seq          increment 1 start 1 minvalue 1;
create sequence tudu_task_log_seq          increment 1 start 1 minvalue 1;
create sequence tudu_access_token_log_seq  increment 1 start 1 minvalue 1;

/* User */

create table tudu_user (
    user_id                 bigint primary key default nextval('tudu_user_seq'),
    email                   varchar(128) not null unique,
    password_salt           varchar(64) not null,
    password_hash           varchar(256) not null,
    --                       
    kvs                     hstore not null default '',
    status                  varchar(32) not null default 'init',
    edate                   timestamptz not null default current_timestamp,
    cdate                   timestamptz not null default current_timestamp,
    --
    check (status in ('deleted', 'init', 'active', 'suspended'))
);
create index tudu_user_cdate_idx on tudu_user using btree (cdate);
create index tudu_user_kvs_idx on tudu_user using gin (kvs);

create table tudu_user_log (
    log_id                  bigint primary key default nextval('tudu_user_log_seq'),
    user_id                 bigint references tudu_user,
    operation               varchar(128) not null,
    ip                      inet default null,
    info                    text default null,
    --
    kvs                     hstore not null default '',
    cdate                   timestamptz not null default current_timestamp
);
create index tudu_user_log_cdate_idx on tudu_user_log using btree (cdate);
create index tudu_user_log_kvs_idx on tudu_user_log using gin (kvs);

/* Task */

create table tudu_task (
    task_id                 bigint primary key default nextval('tudu_task_seq'),
    user_id                 bigint references tudu_user,
    description             text,
    tags                    varchar[] default null,
    finished_date           timestamptz,
    --
    kvs                     hstore not null default '',
    status                  varchar(32) not null default 'init',
    edate                   timestamptz not null default current_timestamp,
    cdate                   timestamptz not null default current_timestamp,
    --
    check (status in ('deleted', 'init', 'finished'))
);
create index tudu_task_finished_date_idx on tudu_task using btree (finished_date);
create index tudu_task_cdate_idx on tudu_task using btree (cdate);
create index tudu_task_kvs_idx on tudu_task using gin (kvs);

create table tudu_task_log (
    log_id                  bigint primary key default nextval('tudu_task_log_seq'),
    task_id                 bigint references tudu_task,
    operation               varchar(128) not null,
    info                    text default null,
    ip                      inet default null,
    --
    kvs                     hstore not null default '',
    cdate                   timestamptz not null default current_timestamp
);
create index tudu_task_log_cdate_idx on tudu_task_log using btree (cdate);
create index tudu_task_log_kvs_idx on tudu_task_log using gin (kvs);

/* User access token */

create table tudu_access_token (
    token_id                bigint primary key default nextval('tudu_access_token_seq'),
    user_id                 bigint references tudu_user,
    token_string            text not null,
    --
    kvs                     hstore not null default '',
    status                  varchar(32) not null default 'active',
    edate                   timestamptz not null default current_timestamp,
    cdate                   timestamptz not null default current_timestamp,
    --
    check (status in ('deleted', 'active', 'revoked'))
);
create unique index tudu_access_token_uniq_idx on tudu_access_token (user_id, token_string);
create unique index tudu_access_token_status_idx on tudu_access_token (user_id, status) where status = 'active';
create index tudu_access_token_cdate_idx on tudu_access_token using btree (cdate);
create index tudu_access_token_kvs_idx on tudu_access_token using gin (kvs);

create table tudu_access_token_log (
    log_id                  bigint primary key default nextval('tudu_access_token_log_seq'),
    token_id                bigint references tudu_access_token,
    operation               varchar(128) not null,
    ip                      inet default null,
    info                    text default null,
    --
    kvs                     hstore not null default '',
    cdate                   timestamptz not null default current_timestamp
);
create index tudu_access_token_log_cdate_idx on tudu_access_token_log using btree (cdate);
create index tudu_access_token_log_kvs_idx on tudu_access_token_log using gin (kvs);
