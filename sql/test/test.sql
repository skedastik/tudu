begin;
\ir plpgunit/1.install-unit-test.sql
\ir helper/helper.sql
\ir util.sql
\ir user.sql
\ir login.sql
select * from unit_tests.begin();
rollback;
