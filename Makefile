DB_NAME = tudu

all: sql_init test

sql_init:
	psql -d $(DB_NAME) -f sql/init.sql

test:
	psql -d $(DB_NAME) -f sql/test/test.sql -v ON_ERROR_STOP=1

.PHONY: sql_init test
