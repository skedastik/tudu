DB_NAME = tudu_dev

all: sql server

test: sql_test server_test

server: server_test
	
sql: sql_init sql_test

sql_init:
	psql -d $(DB_NAME) -f sql/init.sql

sql_test:
	psql -d $(DB_NAME) -f sql/test/test.sql -v ON_ERROR_STOP=1

server_test:
	phpunit

.PHONY: all
