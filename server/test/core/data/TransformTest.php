<?php
namespace Tudu\Test\Core\Data\Validate;

use \Tudu\Core\Data\Transform\Transform;

class ToStringTest extends \PHPUnit_Framework_TestCase {
    
    public function testNumberToString() {
        $transformer = Transform::ToString();
        $this->assertEquals('1', $transformer->execute(1));
        $this->assertEquals('1.5', $transformer->execute(1.5));
        $this->assertEquals('1.034E-15', $transformer->execute(10.34e-16));
    }

    public function testBoolToString() {
        $transformer = Transform::ToString()->interpret()->boolean();
        $this->assertEquals('t', $transformer->execute(true));
        $this->assertEquals('f', $transformer->execute(false));
        $this->assertEquals('t', $transformer->execute(1));
        $this->assertEquals('f', $transformer->execute(0));
        $this->assertEquals('t', $transformer->execute('1'));
        $this->assertEquals('f', $transformer->execute('0'));
        $this->assertEquals('f', $transformer->execute(null));
        $this->assertEquals('f', $transformer->execute(''));
        $this->assertEquals('f', $transformer->execute([]));
    }
}
  
?>
