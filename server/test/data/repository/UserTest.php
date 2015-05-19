<?php
namespace Tudu\Test\Data\Repository;

use \Tudu\Core;
use \Tudu\Data\Repository;
use \Tudu\Data\Model\User;

class UserTest extends \PHPUnit_Framework_TestCase {
    
    protected function setUp() {
        $this->db = $this->getMockBuilder('\Tudu\Core\Data\DbConnection')->disableOriginalConstructor()->getMock();
        $this->repo = new Repository\User($this->db);
    }
    
    public function testGetByID() {
        $mockResult = [[
            'user_id' => 123,
            'email'   => 'foo@bar.xyz'
        ]];
        $this->db->method('query')->willReturn($mockResult);
        $user = $this->repo->getByID(123);
        $this->assertTrue($user instanceof User);
        $this->assertTrue($user->isNormalized());
        $this->assertSame($mockResult[0], $user->asArray());
    }
    
    public function testGetByIDWithFailedQuery() {
        $this->db->method('query')->willReturn(false);
        $id = 123;
        $error = $this->repo->getByID($id);
        $this->assertTrue($error instanceof \Tudu\Core\Error);
        $expected = Core\Data\Repository\Error::ResourceNotFound([ 'user_id' => $id ]);
        $this->assertSame($expected->asArray(), $error->asArray());
    }
}
?>