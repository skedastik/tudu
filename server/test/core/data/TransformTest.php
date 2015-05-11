<?php
namespace Tudu\Test\Core\Data\Validate;

use \Tudu\Core\Data\Transform;

class ToStringTest extends \PHPUnit_Framework_TestCase {
    
    public function testNumberToString() {
        $transformer = new Transform\ToString();
        $this->assertEquals('1', $transformer->transform(1));
        $this->assertEquals('1.5', $transformer->transform(1.5));
        $this->assertEquals('1.034E-15', $transformer->transform(10.34e-16));
    }

    public function testBoolToString() {
        $transformer = (new Transform\ToString())->interpret()->boolean();
        $this->assertEquals('t', $transformer->transform(true));
        $this->assertEquals('f', $transformer->transform(false));
        $this->assertEquals('t', $transformer->transform(1));
        $this->assertEquals('f', $transformer->transform(0));
        $this->assertEquals('t', $transformer->transform('1'));
        $this->assertEquals('f', $transformer->transform('0'));
        $this->assertEquals('f', $transformer->transform(null));
        $this->assertEquals('f', $transformer->transform(''));
        $this->assertEquals('f', $transformer->transform([]));
    }
}
  
?>
