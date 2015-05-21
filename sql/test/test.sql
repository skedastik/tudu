begin;
\ir plpgunit/1.install-unit-test.sql
\ir helper/helper.sql
\ir util.sql
\ir user.sql
\ir token.sql
\ir task.sql
/**
 * plpgunit has been amended to automatically roll back individual functions in
 * the unit_tests schema. This is to prevent unit tests from potentially
 * affecting eachother.
 */
select * from unit_tests.begin();
rollback;
