<?php
namespace Tudu\Test\Unit\Core\Data\Transform;

use \Tudu\Core\Data\Transform\Transform;

/**
 * @group todo
 */
class ExtractTest extends \PHPUnit_Framework_TestCase {
    
    private $transformer;
    
    public function setUp() {
        $this->transformer = Transform::Extract()->hashtags()->asArray();
    }
    
    public function testNonStringInputShouldThrowAnException() {
        $this->setExpectedException('\Tudu\Core\Exception\Internal');
        $this->transformer->execute(123);
    }
    
    public function testExtractHashtagsShouldYieldArrayOfMatchingTags() {
        $input = 'Buy # noodles...#phoSure. #food&wine #foo#bar';
        $expected = [
            'phoSure',
            'food&wine',
            'foo',
            'bar'
        ];
        $this->assertSame($expected, $this->transformer->execute($input));
    }
    
    public function testExtractHashtagsShouldMatchUtf8Characters() {
        $input = '"a"=>"ﾀﾁﾂﾃ", "b"=>"سلام", "c"=>NULL';
        $input = '#ﾀﾁﾂﾃ. #food&سلام';
        $expected = [
            'ﾀﾁﾂﾃ',
            'food&سلام'
        ];
        $this->assertSame($expected, $this->transformer->execute($input));
    }
}
?>
