<?php
namespace Tudu\Test\Unit\Core;

use \Tudu\Core\MediaType;

class MediaTypeTest extends \PHPUnit_Framework_TestCase {
    
    public function testShouldParseValidMediaTypesCorrectly() {
        $mediaType = new MediaType('application/json');
        $this->assertSame('application', $mediaType->getType());
        $this->assertSame('json', $mediaType->getSubtype());
        $this->assertNull($mediaType->getParameterAttribute());
        $this->assertNull($mediaType->getParameterValue());
        
        $mediaType = new MediaType('application/json   ;  ');
        $this->assertSame('application', $mediaType->getType());
        $this->assertSame('json', $mediaType->getSubtype());
        $this->assertNull($mediaType->getParameterAttribute());
        $this->assertNull($mediaType->getParameterValue());

        $mediaType = new MediaType('application/json; charset=utf-8');
        $this->assertSame('application', $mediaType->getType());
        $this->assertSame('json', $mediaType->getSubtype());
        $this->assertSame('charset', $mediaType->getParameterAttribute());
        $this->assertSame('utf-8', $mediaType->getParameterValue());
        
        $mediaType = new MediaType('   Application  /  JSON   ; Charset =     "   UTF-8  " ');
        $this->assertSame('application', $mediaType->getType());
        $this->assertSame('json', $mediaType->getSubtype());
        $this->assertSame('charset', $mediaType->getParameterAttribute());
        $this->assertSame('utf-8', $mediaType->getParameterValue());
        
        $mediaType = new MediaType('   Application  /  JSON   ; Charset =     "   UTF-8  "         ;  ');
        $this->assertSame('application', $mediaType->getType());
        $this->assertSame('json', $mediaType->getSubtype());
        $this->assertSame('charset', $mediaType->getParameterAttribute());
        $this->assertSame('utf-8', $mediaType->getParameterValue());
    }
    
    public function testLooseComparisonShouldCompareContentTypesLoosely() {
        $mediaType1 = new MediaType('application/json');
        $mediaType2 = new MediaType('application/json');
        $this->assertTrue($mediaType1->compare($mediaType2));
        $this->assertTrue($mediaType2->compare($mediaType1));
        
        $mediaType1 = new MediaType('application/json');
        $mediaType2 = new MediaType('application/xml');
        $this->assertFalse($mediaType1->compare($mediaType2));
        $this->assertFalse($mediaType2->compare($mediaType1));
        
        $mediaType1 = new MediaType('text/xml');
        $mediaType2 = new MediaType('application/xml');
        $this->assertFalse($mediaType1->compare($mediaType2));
        $this->assertFalse($mediaType2->compare($mediaType1));
        
        $mediaType1 = new MediaType('application/json; charset=utf-8');
        $mediaType2 = new MediaType('application/json; charset=utf-8');
        $this->assertTrue($mediaType1->compare($mediaType2));
        $this->assertTrue($mediaType2->compare($mediaType1));
        
        $mediaType1 = new MediaType('application/json; charset=utf-8');
        $mediaType2 = new MediaType('application/json; charset=iso-8859-1');
        $this->assertTrue($mediaType1->compare($mediaType2));
        $this->assertTrue($mediaType2->compare($mediaType1));
    }
    
    public function testStrictComparisonShouldCompareContentTypesStrictly() {
        $mediaType1 = new MediaType('application/json');
        $mediaType2 = new MediaType('application/json');
        $this->assertTrue($mediaType1->compareStrict($mediaType2));
        $this->assertTrue($mediaType2->compareStrict($mediaType1));
        
        $mediaType1 = new MediaType('application/json');
        $mediaType2 = new MediaType('application/xml');
        $this->assertFalse($mediaType1->compareStrict($mediaType2));
        $this->assertFalse($mediaType2->compareStrict($mediaType1));
        
        $mediaType1 = new MediaType('text/json');
        $mediaType2 = new MediaType('application/json');
        $this->assertFalse($mediaType1->compareStrict($mediaType2));
        $this->assertFalse($mediaType2->compareStrict($mediaType1));
        
        $mediaType1 = new MediaType('application/json; charset=utf-8');
        $mediaType2 = new MediaType('application/json; charset=utf-8');
        $this->assertTrue($mediaType1->compareStrict($mediaType2));
        $this->assertTrue($mediaType2->compareStrict($mediaType1));
        
        $mediaType1 = new MediaType('application/json; charset=utf-8');
        $mediaType2 = new MediaType('application/json; charset=iso-8859-1');
        $this->assertFalse($mediaType1->compareStrict($mediaType2));
        $this->assertFalse($mediaType2->compareStrict($mediaType1));
        
        $mediaType1 = new MediaType('application/json');
        $mediaType2 = new MediaType('application/json; charset=utf-8');
        $this->assertFalse($mediaType1->compareStrict($mediaType2));
        $this->assertFalse($mediaType2->compareStrict($mediaType1));
    }
    
    public function testCompareWildcardTypeShouldAlwaysReturnTrue() {
        $mediaType1 = new MediaType('*/*');
        $mediaType2 = new MediaType('application/json; charset=utf-8');
        $this->assertTrue($mediaType1->compareStrict($mediaType2));
        $this->assertTrue($mediaType2->compareStrict($mediaType1));
        
        $mediaType1 = new MediaType('*/*');
        $mediaType2 = new MediaType('application/json');
        $this->assertTrue($mediaType1->compareStrict($mediaType2));
        $this->assertTrue($mediaType2->compareStrict($mediaType1));
        
        $mediaType1 = new MediaType('*/*');
        $mediaType2 = new MediaType('application/*');
        $this->assertTrue($mediaType1->compareStrict($mediaType2));
        $this->assertTrue($mediaType2->compareStrict($mediaType1));
        
        $mediaType1 = new MediaType('*/*');
        $mediaType2 = new MediaType('*/*');
        $this->assertTrue($mediaType1->compareStrict($mediaType2));
        $this->assertTrue($mediaType2->compareStrict($mediaType1));
    }
    
    public function testCompareWildcardSubtypeShouldReturnTrueIfTypesMatch() {
        $mediaType1 = new MediaType('application/*');
        $mediaType2 = new MediaType('application/json; charset=utf-8');
        $this->assertTrue($mediaType1->compareStrict($mediaType2));
        $this->assertTrue($mediaType2->compareStrict($mediaType1));
        
        $mediaType1 = new MediaType('application/*');
        $mediaType2 = new MediaType('application/json');
        $this->assertTrue($mediaType1->compareStrict($mediaType2));
        $this->assertTrue($mediaType2->compareStrict($mediaType1));
        
        $mediaType1 = new MediaType('application/*');
        $mediaType2 = new MediaType('application/*');
        $this->assertTrue($mediaType1->compareStrict($mediaType2));
        $this->assertTrue($mediaType2->compareStrict($mediaType1));
        
        $mediaType1 = new MediaType('application/*');
        $mediaType2 = new MediaType('text/json; charset=utf-8');
        $this->assertFalse($mediaType1->compareStrict($mediaType2));
        $this->assertFalse($mediaType2->compareStrict($mediaType1));
        
        $mediaType1 = new MediaType('application/*');
        $mediaType2 = new MediaType('text/json');
        $this->assertFalse($mediaType1->compareStrict($mediaType2));
        $this->assertFalse($mediaType2->compareStrict($mediaType1));
        
        $mediaType1 = new MediaType('application/*');
        $mediaType2 = new MediaType('text/*');
        $this->assertFalse($mediaType1->compareStrict($mediaType2));
        $this->assertFalse($mediaType2->compareStrict($mediaType1));
    }
    
    public function testAsStringShouldReturnAnAppropriateMediaTypeString() {
        $mediaType = new MediaType('application/json; charset=utf-8');
        $this->assertSame('application/json; charset=utf-8', $mediaType->asString());
    }
}
?>
