<?php
namespace Tudu\Test\Unit\Core\Encoder;

use \Tudu\Core\Encoder;
use \Tudu\Core\MediaType;

class JSONTest extends \PHPUnit_Framework_TestCase {
    
    public function testSupportsJSONMediaType() {
        $encoder = new Encoder\JSON();
        $this->assertTrue($encoder->supportsMediaType('application/json'));
    }
    
    public function testDoesNotSupportOtherMediaType() {
        $encoder = new Encoder\JSON();
        $this->assertFalse($encoder->supportsMediaType('application/xml'));
    }
    
    public function testGetMediaTypeReturnsJSON() {
        $encoder = new Encoder\JSON();
        $mediaType = $encoder->getMediaType();
        $this->assertEquals('application/json; charset=utf-8', $mediaType);
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
