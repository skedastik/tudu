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

create or replace function unit_tests.btrim_whitespace_array() returns test_result as $$
declare
    _message    test_result;
begin
    if util.btrim_whitespace(array[E'\n   foo \t \r', '  bar  ']) <> array['foo', 'bar'] then
        select assert.fail('should trim all trailing and leading whitespace for all entries of input array') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;

create or replace function unit_tests.sort_dedup_denull_btrim_whitespace() returns test_result as $$
declare
    _message    test_result;
begin
    if util.sort_dedup_denull_btrim_whitespace(array[E'3 \n', E'\n 3', '1', '  2  ', null, '', '1', '2', E'\r 2 \t'])
    is distinct from array['1', '2', '3']
    then
        select assert.fail('should remove duplicate elements, sorting them ascending in the process') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;
