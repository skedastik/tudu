<?php
namespace Tudu\Test\Unit\Core\Data\Transform;

use \Tudu\Core\Data\Transform\Transform;

class HStoreTest extends \PHPUnit_Framework_TestCase {
    
    public function testShouldConvertHStoreStringToKeyValueArray() {
        $transformer = Transform::HStore()->to()->keyValueArray();
        $input = '"a"=>"foo", "b"=>"newval", "c"=>NULL';
        $expected = [
            'a' => 'foo',
            'b' => 'newval',
            'c' => null
        ];
        $result = $transformer->execute($input);
        $this->assertSame($expected, $result);
    }
    
    public function testShouldReturnEmptyArrayForEmptyHStore() {
        $transformer = Transform::HStore()->to()->keyValueArray();
        $input = '';
        $expected = [];
        $result = $transformer->execute($input);
        $this->assertSame($expected, $result);
    }
    
    public function testShouldWorkForSingleKeyValuePair() {
        $transformer = Transform::HStore()->to()->keyValueArray();
        $input = '"a"=>"foo"';
        $expected = ['a' => 'foo'];
        $result = $transformer->execute($input);
        $this->assertSame($expected, $result);
    }
    
    public function testShouldWorkForEmptyStringValue() {
        $transformer = Transform::HStore()->to()->keyValueArray();
        $input = '"a"=>""';
        $expected = ['a' => ''];
        $result = $transformer->execute($input);
        $this->assertSame($expected, $result);
    }
    
    public function testShouldWorkForValuesContainingDoubleQuotes() {
        $transformer = Transform::HStore()->to()->keyValueArray();
        $input = '"a"=>""""';
        $expected = ['a' => '""'];
        $result = $transformer->execute($input);
        $this->assertSame($expected, $result);
    }
    
    public function testShouldProcessQuotedNullAsString() {
        $transformer = Transform::HStore()->to()->keyValueArray();
        $input = '"a"=>"NULL"';
        $expected = ['a' => 'NULL'];
        $result = $transformer->execute($input);
        $this->assertSame($expected, $result);
    }
    
    public function testShouldWorkForUTF8() {
        $transformer = Transform::HStore()->to()->keyValueArray();
        $input = '"a"=>"ﾀﾁﾂﾃ", "b"=>"سلام", "c"=>NULL';
        $expected = [
            'a' => 'ﾀﾁﾂﾃ',
            'b' => 'سلام',
            'c' => null
        ];
        $result = $transformer->execute($input);
        $this->assertSame($expected, $result);
    }
    
    public function testPassingNonStringInputToHStoreTransformerShouldThrowAnException() {
        $transformer = Transform::HStore();
        $this->setExpectedException('\Tudu\Core\Exception\Internal');
        $transformer->execute(1);
    }
}
?>
