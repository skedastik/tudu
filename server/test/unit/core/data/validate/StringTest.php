<?php
namespace Tudu\Test\Unit\Core\Data\Validate;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;
use \Tudu\Core\Chainable\Sentinel;

class StringTest extends \PHPUnit_Framework_TestCase {

    public function testLengthLowerBoundShouldGenerateAnErrorGivenInputThatIsTooShort() {
        $validator = Validate::String()->length()->from(10);

        $input = 'this string is valid even if it is rather long';
        $this->assertEquals($input, $validator->execute($input));
        
        $validator = Validate::String()->length()->from(1);
        $input = '';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('must be at least 1 character long', $result->getValue());
    }

    public function testLengthUpperBoundShouldGenerateAnErrorGivenInputThatIsTooLong() {
        $validator = Validate::String()->length()->upTo(5);

        $input = 'valid';
        $this->assertEquals($input, $validator->execute($input));
        
        $validator = Validate::String()->length()->upTo(1);
        $input = 'toooooooooooooooooo long';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('must be at most 1 character long', $result->getValue());
    }

    public function testLengthRangeShouldGenerateAnErrorGivenInputWithLengthOutsideRange() {
        $validator = Validate::String()->length()->from(10)->upTo(15);

        $input = 'valid string';
        $this->assertEquals($input, $validator->execute($input));

        $input = 'too short';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('must be 10 to 15 characters long', $result->getValue());

        $input = 'toooooooooooooooooo long';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('must be 10 to 15 characters long', $result->getValue());
    }
    
    public function testValidateEmailShouldAcceptValidEmailAddresses() {
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
    
    public function testChainingStringValidatorWithDescriptionTransformShouldWork() {
        $validator = Validate::String()->length()->from(10)
                   ->then(Transform::Description()->to('String'))
                   ->done();
        $input = 'too short';
        $result = $validator->execute($input);
        $this->assertTrue($result instanceof Sentinel);
        $this->assertEquals('String must be at least 10 characters long.', $result->getValue());
    }
    
    public function testPassingNonStringInputToStringValidatorShouldThrowAnException() {
        $validator = Validate::String();
        $this->setExpectedException('\Tudu\Core\Exception\Internal');
        $validator->execute([]);
    }
}  
?>
