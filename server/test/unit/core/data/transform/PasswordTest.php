<?php
namespace Tudu\Test\Unit\Core\Data\Transform;

use \Tudu\Test\Mock\MockPass;
use \Tudu\Core\Data\Transform\Transform;

class PasswordTest extends \PHPUnit_Framework_TestCase {
    
    public function testShouldGenerateHashThatResolvesToInputPassword() {
        $transformer = Transform::Password()->with(new MockPass());
        $password = 'foo';
        $hash = $transformer->execute($password);
        $mockPass = new MockPass();
        $this->assertTrue($mockPass->compare($password, $hash));
    }
    
    public function testPassingNonStringInputToPasswordTransformerShouldThrowAnException() {
        $transformer = Transform::Password();
        $this->setExpectedException('\Tudu\Core\Exception');
        $transformer->execute(1);
    }
}
?>
