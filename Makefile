DB_NAME = tudu

all: sql

test: sql_test
	
sql: sql_init sql_test

sql_init:
	psql -d $(DB_NAME) -f sql/init.sql

sql_test:
	psql -d $(DB_NAME) -f sql/test/test.sql -v ON_ERROR_STOP=1

.PHONY: test sql sql_init sql_test
