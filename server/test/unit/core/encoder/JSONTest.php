<?php
namespace Tudu\Test\Unit\Core\Encoder;

use \Tudu\Core\Encoder;
use \Tudu\Core\MediaType;

/**
 * @group todo
 */
class JSONTest extends \PHPUnit_Framework_TestCase {
    
    public function testGetMediaTypeShouldReturnJSONMediaType() {
        $encoder = new Encoder\JSON();
        $jsonMediaType = new MediaType('application/json; charset=utf-8');
        $this->assertTrue($jsonMediaType->compare($encoder->getMediaType()));
    }
    
    public function testShouldEncodeArrayAsValidJSON() {
        $encoder = new Encoder\JSON();
        $data = [
            'foo'  => 'bar',
            'baz'  => ['qux' => 'gar"ply'],
            'frob' => [1, 2, 3]
        ];
        $jsonEncoded = $encoder->encode($data);
        $this->assertSame(json_encode($data), $jsonEncoded);
    }
    
    public function testShouldDecodeValidJSONIntoArray() {
        $encoder = new Encoder\JSON();
        $data = [
            'foo'  => 'bar',
            'baz'  => ['qux' => 'gar"ply'],
            'frob' => [1, 2, 3]
        ];
        $jsonEncoded = json_encode($data);
        $this->assertSame($data, $encoder->decode($jsonEncoded));
    }
}
?>
