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
    
    public function testConvertNumericToString() {
        $transformer = Transform::Convert()->to()->string();
        $this->assertSame('1', $transformer->execute(1));
        $this->assertSame('1.5', $transformer->execute(1.5));
        $this->assertSame('1.034E-15', $transformer->execute(10.34e-16));
    }

    public function testConvertToInteger() {
        $transformer = Transform::Convert()->to()->integer();
        $this->assertSame(42, $transformer->execute(42));
        $this->assertSame(4, $transformer->execute(4.2));
        $this->assertSame(42, $transformer->execute('42'));
        $this->assertSame(42, $transformer->execute('+42'));
        $this->assertSame(-42, $transformer->execute('-42'));
        $this->assertSame(34, $transformer->execute(042));
        $this->assertSame(42, $transformer->execute('042'));
        $this->assertSame(100, $transformer->execute(1e2));
        $this->assertSame(1, $transformer->execute('1e2'));
        $this->assertSame(26, $transformer->execute(0x1A));
    }
    
    public function testConvertToFloat() {
        $transformer = Transform::Convert()->to()->float();
        $this->assertSame(42.0, $transformer->execute(42));
        $this->assertSame(4.2, $transformer->execute(4.2));
        $this->assertSame(42.0, $transformer->execute('42'));
        $this->assertSame(42.0, $transformer->execute('+42'));
        $this->assertSame(-42.0, $transformer->execute('-42'));
        $this->assertSame(34.0, $transformer->execute(042));
        $this->assertSame(42.0, $transformer->execute('042'));
        $this->assertSame(100.0, $transformer->execute(1e2));
        $this->assertSame(100.0, $transformer->execute('1e2'));
        $this->assertSame(26.0, $transformer->execute(0x1A));
        $this->assertSame(1.655678E+274, $transformer->execute('1.655678e274'));
        $this->assertSame(3.14, $transformer->execute('3.14'));
        $this->assertSame(3.14, $transformer->execute('3.14foobarbaz'));
        $this->assertSame(0.0, $transformer->execute('foobarbaz3.14'));
    }
}

class DescriptionTest extends \PHPUnit_Framework_TestCase {
    
    public function testDescription() {
        $transformer = Transform::Description()->to('Test thing');
        $error = 'not found';
        $input = new Sentinel($error);
        $this->assertEquals("Test thing $error.", $transformer->execute($input)->getValue());
    }
    
    public function testDescriptionWithNonSentinelInput() {
        $transformer = Transform::Description()->to('Test thing');
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
    
    public function testTrim() {
        $transformer = Transform::String()->trim();
        $this->assertEquals('foo', $transformer->execute('foo'));
        $this->assertEquals('foo', $transformer->execute("foo \n\t\r"));
        $this->assertEquals('foo', $transformer->execute("\n\t\r foo"));
        $this->assertEquals('foo', $transformer->execute("\n\t\r foo \n\t\r"));
    }

    public function testStringTransformerWithNonStringInput() {
        $transformer = Transform::String();
        $this->setExpectedException('\Tudu\Core\TuduException');
        $transformer->execute(1);
    }
}
  
?>
