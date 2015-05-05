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

create or replace function unit_tests.denull_btrim_whitespace() returns test_result as $$
declare
    _message    test_result;
begin
    if util.denull_btrim_whitespace(array[E'Three \n', 'one', '  2  ', null, '', 'One', '2', E'\r 2 \t'])
    is distinct from array['Three', 'one', '2', 'One', '2', '2']
    then
        select assert.fail('should remove NULLs and empty strings, removing whitespace in the process') into _message;
        return _message;
    end if;
    
    select assert.ok('End of test.') into _message;
    return _message;
end;
$$ language plpgsql;
