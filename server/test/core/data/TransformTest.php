<?php
namespace Tudu\Test\Core\Data\Validate;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Chainable\Sentinel;
use \Tudu\Core\Data\Validate;

class ConvertTest extends \PHPUnit_Framework_TestCase {
    
    public function testConvertWithoutSpecifyingOutputType() {
        $transformer = Transform::Convert();
        $this->setExpectedException('\Tudu\Core\TuduException');
        $transformer->execute('whatever');
    }
    
    public function testConvertInterpretingBooleanWithoutSpecifyingOutputType() {
        $this->setExpectedException('\Tudu\Core\TuduException');
        $transformer = Transform::Convert()->interpreting()->boolean();
    }
    
    public function testNumberConvert() {
        $transformer = Transform::Convert()->toString();
        $this->assertEquals('1', $transformer->execute(1));
        $this->assertEquals('1.5', $transformer->execute(1.5));
        $this->assertEquals('1.034E-15', $transformer->execute(10.34e-16));
    }

    public function testBoolConvert() {
        $transformer = Transform::Convert()->toString()->interpreting()->boolean();
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

class DescriptionToTest extends \PHPUnit_Framework_TestCase {
    
    public function testDescriptionTo() {
        $transformer = Transform::DescriptionTo('Test thing');
        $input = new Sentinel(Validate\Error::NOT_FOUND);
        $this->assertEquals('Test thing not found.', $transformer->execute($input)->getValue());
    }
    
    public function testDescriptionToWithNonSentinelInput() {
        $transformer = Transform::DescriptionTo('Test thing');
        $input = 'input';
        $this->assertEquals('input', $transformer->execute($input));
    }
}

class StringTransformerTest extends \PHPUnit_Framework_TestCase {

    public function testEscapeForHTML() {
        $transformer = Transform::String()->escapeForHTML();
        $this->assertEquals('this &amp; that', $transformer->execute('this & that'));
    }
    
    public function testStripTags() {
        $transformer = Transform::String()->stripTags();
        $this->assertEquals(
            'this and that',
            $transformer->execute('<p><a href="#">this</a> and that</p><br />')
        );
    }

    public function testStringTransformerWithNonStringInput() {
        $transformer = Transform::String();
        $this->setExpectedException('\Tudu\Core\TuduException');
        $transformer->execute(1);
    }
}
  
?>
