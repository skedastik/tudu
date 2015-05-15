DB_NAME = tudu_dev

all: sql server

test: sql_test server_test

coverage: server_coverage

server: server_test

# SQL --------------------------------------------------------------------------
	
sql: sql_init sql_test

sql_init:
	psql -d $(DB_NAME) -f sql/init.sql

sql_test:
	psql -d $(DB_NAME) -f sql/test/test.sql -v ON_ERROR_STOP=1

# Server -----------------------------------------------------------------------

server_test:
	phpunit

server_coverage:
	phpunit --coverage-html coverage/phpunit/

# Misc -------------------------------------------------------------------------

clean:
	rm -rf coverage/phpunit/*

.PHONY: all clean
