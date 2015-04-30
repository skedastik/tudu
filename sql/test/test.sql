\ir plpgunit/1.install-unit-test.sql
\ir util.sql
\ir user.sql

begin;
select * from unit_tests.begin();
rollback;
