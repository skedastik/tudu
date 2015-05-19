<?php
namespace Tudu\Test\Core\Data\Validate;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;
use \Tudu\Core\Chainable\Sentinel;
use \Tudu\Test\Mock\MockValidator;

class BasicTest extends \PHPUnit_Framework_TestCase {

    public function testBasicValidator() {
        $validator = Validate::Basic();

        $error = 'not found';
        $input = new Sentinel($error);
        $this->assertEquals($error, $validator->execute($input)->getValue());

        $input = 'whatever';
        $this->assertEquals($input, $validator->execute($input));
    }
    
    public function testApplyWithoutSpecifyingOptions() {
        $validator = new MockValidator();
        $this->setExpectedException('\Tudu\Core\TuduException');
        $validator->execute('whatever');
    }
}

class StringTest extends \PHPUnit_Framework_TestCase {

    public function testLengthLowerBound() {
        $validator = Validate::String()->length()->from(10);

        $input = 'this string is valid even if it is rather long';
        $this->assertEquals($input, $validator->execute($input));
        
        $validator = Validate::String()->length()->from(1);
        $input = '';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('must be at least 1 character in length', $result->getValue());
    }

    public function testLengthUpperBound() {
        $validator = Validate::String()->length()->upTo(5);

        $input = 'valid';
        $this->assertEquals($input, $validator->execute($input));
        
        $validator = Validate::String()->length()->upTo(1);
        $input = 'toooooooooooooooooo long';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('must be at most 1 character in length', $result->getValue());
    }

    public function testLengthRange() {
        $validator = Validate::String()->length()->from(10)->upTo(15);

        $input = 'valid string';
        $this->assertEquals($input, $validator->execute($input));

        $input = 'too short';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('must be 10 to 15 characters in length', $result->getValue());

        $input = 'toooooooooooooooooo long';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('must be 10 to 15 characters in length', $result->getValue());
    }
    
    public function testValidEmail() {
        $validator = Validate::String()->is()->validEmail();

        $input = 'valid@email.xyz';
        $this->assertEquals($input, $validator->execute($input));

        $input = '123@123.xyz';
        $this->assertEquals($input, $validator->execute($input));

        $input = '123@123.blarg.xyz';
        $this->assertEquals($input, $validator->execute($input));

        $input = '123.abc@123.blarg.xyz';
        $this->assertEquals($input, $validator->execute($input));

        $input = '@invalid@email@xyz';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('is invalid', $result->getValue());

        $input = '@invalid@email.com';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('is invalid', $result->getValue());

        $input = 'email.com';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('is invalid', $result->getValue());
    }
    
    public function testWithDescription() {
        $validator = Validate::String()->length()->from(10)
                   ->then(Transform::Description()->to('String'));

        $input = 'too short';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('String must be at least 10 characters in length.', $result->getValue());
    }
    
    public function testValidateNonStringInput() {
        $validator = Validate::String();
        $this->setExpectedException('\Tudu\Core\TuduException');
        $validator->execute([]);
    }
}

class NumberTest extends \PHPUnit_Framework_TestCase {

    public function testNonNumericInput() {
        $validator = Validate::Number();
        $this->setExpectedException('\Tudu\Core\TuduException');
        $validator->execute('this is not a number');
    }
    
    public function testIsPositive() {
        $validator = Validate::Number()->is()->positive();
        $result = $validator->execute(-135);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('must be a positive number', $result->getValue());
    }
}

class ChainingTest extends \PHPUnit_Framework_TestCase {

    public function testChainTwoValidators() {
        $validator = Validate::String()->is()->validEmail()
                   ->then(Validate::String()->length()->upTo(15));

        $input = 'valid@email.xyz';
        $this->assertEquals($input, $validator->execute($input));

        $input = '@invalid@email@xyz';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('is invalid', $result->getValue());

        $input = 'this_email_is@too_long.xyz';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('must be at most 15 characters in length', $result->getValue());
    }

    public function testChainThreeValidators() {
        $validator = Validate::String()->is()->validEmail()
                   ->then(Validate::String()->length()->from(15))
                   ->then(Validate::String()->length()->upTo(20));

        $input = 'just_right@valid.xyz';
        $this->assertEquals($input, $validator->execute($input));

        $input = '@invalid@email@xyz';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('is invalid', $result->getValue());

        $input = 'this_email_is@too_long.xyz';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('must be at most 20 characters in length', $result->getValue());

        $input = 'too@short.xyz';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('must be at least 15 characters in length', $result->getValue());
    }
}

class SentinelTest extends \PHPUnit_Framework_TestCase {

    public function testSentinel() {
        $validator = Validate::String()->is()->validEmail();

        $error = 'not found';
        $input = new Sentinel($error);
        $this->assertEquals($error, $validator->execute($input)->getValue());
    }

    public function testSentinelWithChaining() {
        $error = 'not found';
        $validator = Validate::String()->is()->validEmail()
                   ->then(Validate::String()->length()->upTo(5));
        $input = new Sentinel($error);
        $this->assertEquals($error, $validator->execute($input)->getValue());

        $validator = Validate::String()->length()->upTo(5)
                   ->then(Validate::String()->is()->validEmail());
        $input = new Sentinel($error);
        $this->assertEquals($error, $validator->execute($input)->getValue());
    }
}
  
?>
