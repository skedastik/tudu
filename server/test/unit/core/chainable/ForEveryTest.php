<?php
namespace Tudu\Test\Unit\Core\Chainable;

use \Tudu\Core\Data\Validate\Validate;
use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Chainable\ForEvery;
use \Tudu\Core\Chainable\Sentinel;

class ForEveryTest extends \PHPUnit_Framework_TestCase {
    
    private $transformer;
    
    public function setUp() {
        $this->transformer = ForEvery::Element(
                             Transform::String()->trim()
                          -> then(Validate::String()->length()->upTo(10)));
    }
    
    public function testNonArrayInputShouldThrowAnException() {
        $this->setExpectedException('\Tudu\Core\Exception\Internal');
        $this->transformer->execute('not an array');
    }
    
    public function testEmptyArrayInputShouldReturnEmptyArray() {
        $input = [];
        $expected = [];
        $result = $this->transformer->execute($input);
        $this->assertSame($expected, $result);
    }
    
    public function testValidInputShouldReturnNormalizedData() {
        $input = [
            "  bumfuzzles  \t",
            'also valid'
        ];
        $expected = [
            'bumfuzzles',
            'also valid'
        ];
        $result = $this->transformer->execute($input);
        $this->assertSame($expected, $result);
    }
    
    public function testIncompatibleInputElementTypeShouldThrowAnException() {
        $input = [
            'valid',
            234
        ];
        $this->setExpectedException('\Tudu\Core\Exception\Internal');
        $this->transformer->execute($input);
    }
    
    public function testInvalidInputShouldReturnSentinel() {
        $input = [
            'this string is too long',
            'also valid'
        ];
        $result = $this->transformer->execute($input);
        $this->assertTrue($result instanceof Sentinel);
    }
}
?>
