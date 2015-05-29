<?php
namespace Tudu\Test\Unit\Data\Model;

use \Tudu\Data\Repository;
use \Tudu\Data\Model\User;

class UserTest extends \PHPUnit_Framework_TestCase {
    
    public function testNormalizedUserShouldHaveNormalizedData() {
        $data = [
            User::USER_ID => '123',
            User::EMAIL => "   foo@bar.xyz   \t"
        ];
        $user = new User($data);
        $user->normalize();
        $this->assertTrue($user->isNormalized());
        $expected = [
            User::USER_ID => 123,
            User::EMAIL => 'foo@bar.xyz'
        ];
        $this->assertSame($expected, $user->asArray());
    }
    
    public function testSanitizedUserShouldHaveSanitizedData() {
        $data = [
            User::USER_ID => '123',
            User::EMAIL => 'foo&baz@bar.xyz'
        ];
        $user = new User($data);
        $user->normalize();
        $user = $user->getSanitizedCopy('html');
        $this->assertTrue($user->isSanitized());
        $expected = [
            User::USER_ID => 123,
            User::EMAIL => 'foo&amp;baz@bar.xyz'
        ];
        $this->assertSame($expected, $user->asArray());
    }
}
?>
