<?php
namespace Tudu\Test\Unit\Data\Repository;

use \Tudu\Data\Repository;
use \Tudu\Data\Model\User;
use \Tudu\Core\Chainable\Sentinel;
use \Tudu\Core\Data\Repository\Error;

class UserTest extends \PHPUnit_Framework_TestCase {
    
    protected function setUp() {
        $this->db = $this->getMockBuilder('\Tudu\Core\Data\DbConnection')->disableOriginalConstructor()->getMock();
        $this->repo = new Repository\User($this->db);
    }
    
    public function testGetByIDShouldWorkGivenValidID() {
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
    
    public function testGetByIDShouldGenerateResourceNotFoundErrorIfQueryFails() {
        $this->db->method('query')->willReturn(false);
        $id = 123;
        $error = $this->repo->getByID($id);
        $this->assertTrue($error instanceof \Tudu\Core\Error);
        $user = new User([
            'user_id' => new Sentinel(Error::RESOURCE_NOT_FOUND_CONTEXT)
        ]);
        $expected = Error::ResourceNotFound($user->normalize());
        $this->assertSame($expected->asArray(), $error->asArray());
    }
}
?>
