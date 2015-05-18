<?php
namespace Tudu\Test\Core\Data;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;
use \Tudu\Core\Chainable\Sentinel;

class ChainTransformValidateTest extends \PHPUnit_Framework_TestCase {
    
    public function testTransformThenValidate() {
        $chain = Transform::Convert()->to()->booleanString()
               ->then(Validate::String()->length()->upTo(1));
        
        $input = 'truthy';
        $this->assertEquals('t', $chain->execute($input));
    }

    public function testValidateThenTransform() {
        $chain = Validate::String()->length()->upTo(1)
               ->then(Transform::Convert()->to()->booleanString());
        
        $input = 'truthy';
        $result = $chain->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('must be at most 1 character in length', $result->getValue());
    }

    public function testTransformThenValidateWithSentinel() {
        $chain = Transform::Convert()->to()->booleanString()
               ->then(Validate::String()->length()->from(5));
        
        $error = 'not found';
        $result1 = $chain->execute('error expected');
        $result2 = $chain->execute(new Sentinel($error));
        
        $this->assertTrue($result1 instanceof Sentinel);
        $this->assertTrue($result2 instanceof Sentinel);
        
        $this->assertEquals('must be at least 5 characters in length', $result1->getValue());
        $this->assertEquals($error, $result2->getValue());
    }
    
    public function testChainingWithDescription() {
        $chain = Validate::String()->length()->upTo(15)
               ->then(Transform::Description()->to('Test string'));
        
        $input = 'valid string';
        $this->assertEquals($input, $chain->execute($input));
        
        $input = 'this string is invalid';
        $result = $chain->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('Test string must be at most 15 characters in length.', $result->getValue());
    }
    
    public function testChainingOfSentinels() {
        $chain = Validate::String()->length()->upTo(15)
               ->then(Transform::Description()->to('Test string'));
        
        $error = 'not found';
        $input = new Sentinel($error);
        $result = $chain->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals("Test string $error.", $result->getValue());
    }
}
  
?>
