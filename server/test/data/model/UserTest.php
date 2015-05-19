<?php
namespace Tudu\Test\Data\Model;

use \Tudu\Data\Repository;
use \Tudu\Data\Model\User;

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
    
    public function testSanitizeUser() {
        $data = [
            'user_id' => '123',
            'email' => 'foo&baz@bar.xyz'
        ];
        $user = new User($data);
        $user->normalize();
        $user = $user->getSanitizedCopy();
        $this->assertTrue($user->isSanitized());
        $expected = [
            'user_id' => 123,
            'email' => 'foo&amp;baz@bar.xyz'
        ];
        $this->assertSame($expected, $user->asArray());
    }
}
?>