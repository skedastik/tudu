<?php
namespace Tudu\Core\Data;

/**
 * A minimal database abstraction layer.
 */
abstract class DbConnection {
    
    protected $options;
    protected $connection;
    
    /**
     * Constructor.
     * 
     * @param string[] $options Must have the following keys: host, database, 
     * username, password. The following keys are optional: port (defaults to
     * 5432).
     */
    public function __construct($options) {
        $this->connection = null;
        $this->options = [
            'host'     => $options['host'],
            'database' => $options['database'],
            'username' => $options['username'],
            'password' => $options['password'],
            'port'     => isset($options['port']) ? $options['port'] : 5432
        ];
    }
    
    /**
     * Establish a connection the database.
     * 
     * This need not be called explicitly. A call to query() automatically calls
     * connect().
     */
    abstract public function connect();
    
    /**
     * Query the database using a parameterized query string.
     * 
     * @param string $queryString A parameterized query string. Parameters are
     * delimited with $1, $2,... corresponding to elements of $params.
     * @param array $params Array of query parameters. Number of elements in
     * array must match number of parameters in $queryString.
     * @param string $queryName An optional name for the query. If a name is
     * provided, the query is executed as a prepared statement, otherwise the
     * the database is queried directly.
     * 
     * @return array|FALSE If query succeeds, returns an array of rows where
     * each row is a key/value array. Otherwise, returns FALSE.
     */
    abstract function query($queryString, array $params = [], $queryName = '');
    
    /**
     * Return the last database error.
     */
    abstract function getLastError();
}
?>
