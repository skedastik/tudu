/* test */

create or replace function unit_tests.btrim_whitespace() returns test_result as $$
declare
    _message    test_result;
begin
    if util.btrim_whitespace(E'\n   foo \t \r') <> 'foo' then
        select assert.fail('should trim all trailing and leading whitespace.') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;
