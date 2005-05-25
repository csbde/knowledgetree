CREATE FUNCTION concat (text, text)
RETURNS text
AS '
    select $1 || $2'
LANGUAGE sql;

CREATE FUNCTION left (text, integer)
RETURNS text
AS '
    select substr($1, 1, $2)'
LANGUAGE sql;
