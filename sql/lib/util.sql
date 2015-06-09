/**
 * Trim whitespace (including newlines) from input string
 */
create or replace function util.btrim_whitespace(_str text) returns text as $$
    select btrim(_str, E'\n\r\t ');
$$ language sql immutable security definer;

/**
 * Trim whitespace (including newlines) from strings in array
 * 
 * Passing in an empty array will produce a NULL result.
 */
create or replace function util.btrim_whitespace(_str_arr text[]) returns text[] as $$
    select array_agg(x.val) from (select util.btrim_whitespace(unnest(_str_arr)) as val) x;
$$ language sql immutable security definer;

/**
 * Remove nulls and empty strings from an array, trimming whitespace in the
 * process.
 * 
 * Passing in an empty array will produce a NULL result.
 */
create or replace function util.denull_btrim_whitespace(_str_array text[]) returns text[] as $$
    select array_agg(x.val) from (
        select util.btrim_whitespace(unnest(_str_array)) as val
    ) x where x.val is not null and x.val <> '';
$$ language sql immutable security definer;
