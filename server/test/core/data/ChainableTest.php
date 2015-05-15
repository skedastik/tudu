<?php
namespace Tudu\Test\Core\Data;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;
use \Tudu\Core\Data\Validate\Error as Error;
use \Tudu\Core\Chainable\Sentinel;

class ChainingTest extends \PHPUnit_Framework_TestCase {
    
    public function testTransformThenValidate() {
        $chain = Transform::Convert()->toString()->interpreting()->boolean()
               ->then(Validate::String()->length()->upTo(1));
        
        $input = 'truthy';
        $this->assertEquals('t', $chain->execute($input));
    }

    public function testValidateThenTransform() {
        $chain = Validate::String()->length()->upTo(1)
               ->then(Transform::Convert()->toString()->interpreting()->boolean());
        
        $input = 'truthy';
        $result = $chain->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('must be at most 1 character in length', $result->getValue());
    }

    public function testTransformThenValidateWithSentinel() {
        $chain = Transform::Convert()->toString()->interpreting()->boolean()
               ->then(Validate::String()->length()->from(5));
        
        $result1 = $chain->execute('error expected');
        $result2 = $chain->execute(new Sentinel(Error::NOT_FOUND));
        
        $this->assertTrue($result1 instanceof Sentinel);
        $this->assertTrue($result2 instanceof Sentinel);
        
        $this->assertEquals('must be at least 5 characters in length', $result1->getValue());
        $this->assertEquals('not found', $result2->getValue());
    }
    
    public function testChainingWithDescriptionTo() {
        $chain = Validate::String()->length()->upTo(15)
               ->then(Transform::DescriptionTo('Test string'));
        
        $input = 'valid string';
        $this->assertEquals($input, $chain->execute($input));
        
        $input = 'this string is invalid';
        $result = $chain->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('Test string must be at most 15 characters in length.', $result->getValue());
    }
    
    public function testChainingOfSentinels() {
        $chain = Validate::String()->length()->upTo(15)
               ->then(Transform::DescriptionTo('Test string'));
        
        $input = new Sentinel(Error::NOT_FOUND);
        $result = $chain->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('Test string not found.', $result->getValue());
    }
}
  
?>
