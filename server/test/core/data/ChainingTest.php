<?php
namespace Tudu\Test\Core\Data;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;

class ChainingTest extends \PHPUnit_Framework_TestCase {
    
    public function testTransformValidateChain() {
        $chain = Transform::ToString()->interpret()->boolean()
            ->then(Validate::String()->length()->upTo(1));
        $this->assertNull($chain->execute('truthy'));
    }
}
  
?>
