<?php
namespace Tudu\Test\Core\Data;

use \Tudu\Core\Data\Transform;
use \Tudu\Core\Data\Validate;

class ChainingTest extends \PHPUnit_Framework_TestCase {
    
    public function testTransformValidateChain() {
        $chain = (new Transform\ToString())->interpret()->boolean()
            ->then((new Validate\String())->length()->upTo(1));
        $this->assertNull($chain->execute('truthy'));
    }
}
  
?>
