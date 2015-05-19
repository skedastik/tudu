<?php
namespace Tudu\Test\Core\Data\Model;

use \Tudu\Core\Data\Repository;
use \Tudu\Core\Data\Model\User;

class UserTest extends \PHPUnit_Framework_TestCase {
    
    protected function setUp() {
        $this->db = $this->getMockBuilder('\Tudu\Core\Data\DbConnection')->disableOriginalConstructor()->getMock();
        $this->repo = new Repository\User($this->db);
    }
    
    public function testNormalizeUser() {
        $data = [
            'user_id' => '123',
            'email' => "   foo@bar.xyz   \t"
        ];
        $user = new User($data);
        $errors = $user->normalize();
        $this->assertTrue($user->isNormalized());
        $expected = [
            'user_id' => 123,
            'email' => 'foo@bar.xyz'
        ];
        $this->assertSame($expected, $user->asArray());
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
        $expected = Repository\Error::ResourceNotFound([ 'user_id' => $id ]);
        $this->assertSame($expected->asArray(), $error->asArray());
    }
}
?>
