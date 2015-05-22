<?php
namespace Tudu\Test\Unit\Data\Transform;

use \Tudu\Delegate\PHPass;
use \Tudu\Core\Data\Transform\Transform;

class PHPasswordTest extends \PHPUnit_Framework_TestCase {
    
    public function testShouldGenerateHashThatResolvesToInputPassword() {
        $transformer = Transform::Password()->with()->delegate(new PHPass());
        $password = 'foo';
        $hash = $transformer->execute($password);
        $phpass = new PHPass();
        $this->assertTrue($phpass->compare($password, $hash));
    }
}
?>
