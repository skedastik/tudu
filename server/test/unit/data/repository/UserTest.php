<?php
namespace Tudu\Test\Unit\Data\Repository;

use \Tudu\Data\Repository;
use \Tudu\Data\Model\User;
use \Tudu\Core\Error;

class UserTest extends \PHPUnit_Framework_TestCase {
    
    protected function setUp() {
        $this->db = $this->getMockBuilder('\Tudu\Core\Data\DbConnection')->disableOriginalConstructor()->getMock();
        $this->repo = new Repository\User($this->db);
    }
    
    public function testGetByIDShouldProduceNormalizedModel() {
        $mockResult = [[
            'user_id' => '123',
            'email'   => "  foo@bar.xyz  \t"
        ]];
        $expected = [
            'user_id' => 123,
            'email'   => 'foo@bar.xyz'
        ];
        $this->db->method('query')->willReturn($mockResult);
        $user = $this->repo->getByID(123);
        $this->assertTrue($user instanceof User);
        $this->assertTrue($user->isNormalized());
        $this->assertSame($expected, $user->asArray());
    }
    
    public function testGetByIDShouldGenerateResourceNotFoundErrorIfQueryFails() {
        $this->db->method('query')->willReturn(false);
        $id = 123;
        $error = $this->repo->getByID($id);
        $this->assertTrue($error instanceof Error);
        $this->assertEquals(Error::GENERIC, $error->getError());
    }
}
?>
