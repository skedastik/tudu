<?php
namespace Tudu\Test\Integration\Database;

use \Tudu\Conf\Conf;
use \Tudu\Core\Data\PgSQLConnection;

abstract class DatabaseTest extends \PHPUnit_Framework_TestCase {
    
    protected $db;
    
    protected function setUp() {
        $this->db = new PgSQLConnection([
            'host'     => Conf::DB_HOST,
            'database' => Conf::DB_NAME,
            'username' => Conf::DB_USERNAME,
            'password' => Conf::DB_PASSWORD
        ]);
        $this->db->query('begin;');
    }
    
    protected function tearDown() {
        $this->db->query('rollback;');
        $this->db->close();
    }
}
?>
