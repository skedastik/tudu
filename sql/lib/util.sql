/**
 * Trim whitespace (including newlines) from input string
 */
create or replace function util.btrim_whitespace(varchar) returns varchar as $$
    select btrim($1, E'\n\r\t ');
$$ language sql immutable security definer;
