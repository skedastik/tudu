<?php
namespace Tudu\Test\Integration\Database;

use \Tudu\Test\Integration\Database\DatabaseTest;

class QueryTest extends DatabaseTest {
    
    public function testQuerySelect() {
        $result = $this->db->query('select 123 as val;');
        $this->assertNotFalse($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals(123, $result[0]['val']);
    }
    
    public function testQueryValueSelect() {
        $result = $this->db->queryValue('select 123;');
        $this->assertNotFalse($result);
        $this->assertEquals(123, $result);
    }
}
?>
