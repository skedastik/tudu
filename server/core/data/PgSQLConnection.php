<?php
namespace Tudu\Core\Data;

require_once __DIR__.'/DbConnection.php';
require_once __DIR__.'/../Logger.php';

use \Tudu\Core\Data\DbConnection;
use \Tudu\Core\Logger;

class PgSQLConnection extends DbConnection {
    
    public function connect() {
        if ($this->connection === null) {
            $this->connection = pg_connect("host={$this->options['host']} port={$this->options['port']} dbname={$this->options['database']} user={$this->options['username']} password={$this->options['password']}");
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
}
?>
