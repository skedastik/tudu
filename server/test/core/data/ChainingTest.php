<?php
namespace Tudu\Test\Core\Data;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;

class ChainingTest extends \PHPUnit_Framework_TestCase {
    
    public function testTransformThenValidate() {
        $chain = Transform::ToString()->interpret()->boolean()
            ->then(Validate::String()->length()->upTo(1));
        $this->assertNull($chain->execute('truthy'));
    }
    
    public function testValidateThenTransform() {
        $chain = Validate::String()->length()->upTo(1)
            ->then(Transform::ToString()->interpret()->boolean());
        $this->assertNotNull($chain->execute('truthy'));
    }
}
  
?>
