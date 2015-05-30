<?php
namespace Tudu\Core\Database;

use \Tudu\Core\Logger;
use \Tudu\Core\TuduException;

final class PgSQLConnection extends DbConnection {
    
    public function connect() {
        if ($this->connection === null) {
            $this->connection = pg_connect("host={$this->options['host']} port={$this->options['port']} dbname={$this->options['database']} user={$this->options['username']} password={$this->options['password']}");
        }
    }
    
    public function close() {
        if ($this->connection !== null) {
            pg_close($this->connection);
            $this->connection = null;
        }
    }
    
    public function query($queryString, array $params = [], $queryName = '') {
        $logger = Logger::getInstance();
        $this->connect();
        
        if (empty($queryName)) {
            $result = pg_query_params($this->connection, $queryString, $params);
        } else {
            pg_prepare($this->connection, $queryName, $queryString);
            $result = pg_execute($this->connection, $queryName, $params);
        }
        
        if ($result) {
            if (empty($params)) {
                $logger->info('Database query ['.$queryString.'] succeeded');
            } else {
                $logger->info('Database query ['.$queryString.'] succeeded with params', $params);
            }
        } else {
            $logger->error('Database query ['.$queryString.'] failed with error', [pg_last_error()]);
        }
        
        return $result ? pg_fetch_all($result) : FALSE;
    }
    
    public function queryValue($queryString, array $params = [], $queryName = '') {
        $result = $this->query($queryString, $params, $queryName);
        return $result ? array_values($result[0])[0] : FALSE;
    }
    
    public function getLastError() {
        return pg_last_error($this->connection);
    }
}
?>
