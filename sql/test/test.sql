begin;
\ir plpgunit/1.install-unit-test.sql
\ir util.sql
\ir user.sql
select * from unit_tests.begin();
rollback;
