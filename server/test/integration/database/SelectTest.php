<?php
namespace Tudu\Test\Integration\Database;

use \Tudu\Test\Integration\Database\DatabaseTest;

class SelectTest extends DatabaseTest {
    
    public function testSelect() {
        $result = $this->db->query('select 123 as val;');
        $this->assertNotFalse($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals(123, $result[0]['val']);
    }
}
?>
