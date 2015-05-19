<?php
namespace Tudu\Test\Unit\Data\Model;

use \Tudu\Data\Repository;
use \Tudu\Data\Model\User;

class UserTest extends \PHPUnit_Framework_TestCase {
    
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
