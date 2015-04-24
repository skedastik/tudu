<?php
namespace Tudu\Core\Data;

abstract class DB {
    protected $options;
    protected $connection;
    
    function __construct($options) {
        $this->connection = null;
        $this->options = [
            'host'     => $options['host'],
            'database' => $options['database'],
            'username' => $options['username'],
            'password' => $options['password']
        ];
    }
    
    abstract function connect();
    abstract function query();
    
    protected function getConnectionString() {
       return "host=$this->options[host] dbname=$this->options[database] user=$this->options[username] password=$this->options[password]";
    }
}
?>