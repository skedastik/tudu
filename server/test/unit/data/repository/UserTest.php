<?php
namespace Tudu\Test\Unit\Data\Repository;

use \Tudu\Data\Repository;
use \Tudu\Data\Model\User;

class UserTest extends \PHPUnit_Framework_TestCase {
    
    protected function setUp() {
        $this->db = $this->getMockBuilder('\Tudu\Core\Database\DbConnection')->disableOriginalConstructor()->getMock();
        $this->repo = new Repository\User($this->db);
    }
    
    public function testFetchShouldProduceNormalizedModel() {
        $mockResult = [[
            User::USER_ID => '123',
            User::EMAIL   => 'foo@bar.xyz'
        ]];
        $this->db->method('query')->willReturn($mockResult);
        $user = $this->repo->fetch(new User([
            User::USER_ID => 123
        ]));
        $this->assertTrue($user->isNormalized());
    }
    
    public function testFetchShouldGenerateResourceNotFoundErrorIfQueryFails() {
        $this->db->method('query')->willReturn(false);
        $id = 123;
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->repo->fetch(new User([
            User::USER_ID => 123
        ]));
    }
}
?>
