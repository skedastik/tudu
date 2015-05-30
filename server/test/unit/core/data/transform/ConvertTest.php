<?php
namespace Tudu\Test\Unit\Core\Data\Transform;

use \Tudu\Core\Data\Transform\Transform;

class ConvertTest extends \PHPUnit_Framework_TestCase {
    
    public function testConvertingWithoutSpecifyingOutputTypeShouldThrowAnException() {
        $transformer = Transform::Convert();
        $this->setExpectedException('\Tudu\Core\Exception\Internal');
        $transformer->execute('whatever');
    }
    
    public function testConvertNumericToStringShouldWork() {
        $transformer = Transform::Convert()->to()->string();
        $this->assertSame('1', $transformer->execute(1));
        $this->assertSame('1.5', $transformer->execute(1.5));
        $this->assertSame('1.034E-15', $transformer->execute(10.34e-16));
    }

    public function testConvertToIntegerShouldWork() {
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
    
    public function testConvertToFloatShouldWork() {
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
?>
