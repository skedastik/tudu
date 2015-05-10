<?php
namespace Tudu\Test\Core\Data\Validate;

require_once __DIR__.'/../../../core/data/validate/String.php';
require_once __DIR__.'/../../../core/data/validate/Email.php';
require_once __DIR__.'/../../../core/data/validate/sentinel/NotFound.php';
require_once __DIR__.'/../../../../vendor/autoload.php';

use \Tudu\Core\Data\Validate;
use \Tudu\Core\Data\Validate\Sentinel;

class StringTest extends \PHPUnit_Framework_TestCase {

    public function testLengthLowerBound() {
        $validator = Validate\String()->length()->from(10);
        $this->assertNull($validator->validate('this string is valid even if it is rather long'));
        $this->assertNotNull($validator->validate('too short'));
    }

    public function testLengthUpperBound() {
        $validator = Validate\String()->length()->upto(5);
        $this->assertNull($validator->validate('valid'));
        $this->assertNotNull($validator->validate('toooooooooooooooooo long'));
    }

    public function testLengthRange() {
        $validator = Validate\String()->length()->from(10)->upto(15);
        $this->assertNull($validator->validate('valid string'));
        $this->assertNotNull($validator->validate('too short'));
        $this->assertNotNull($validator->validate('toooooooooooooooooo long'));
    }
}

class EmailTest extends \PHPUnit_Framework_TestCase {

    public function testEmailFormat() {
        $validator = Validate\Email();
        $this->assertNull($validator->validate('valid@email.xyz'));
        $this->assertNull($validator->validate('123@123.xyz'));
        $this->assertNull($validator->validate('123@123.blarg.xyz'));
        $this->assertNull($validator->validate('123.abc@123.blarg.xyz'));
        $this->assertNotNull($validator->validate('@invalid@email@xyz'));
        $this->assertNotNull($validator->validate('invalid@email'));
    }
}

class ChainingTest extends \PHPUnit_Framework_TestCase {

    public function testChainTwoValidators() {
        $validator = Validate\Email()->also(Validate\String()->length()->upto(15));
        $this->assertNull($validator->validate('valid@email.xyz'));
        $this->assertNotNull($validator->validate('@invalid@email@xyz'));
        $this->assertNotNull($validator->validate('this_email_is@too_long.xyz'));
    }

    public function testChainThreeValidators() {
        $validator = Validate\Email()
            ->also(Validate\String()->length()->from(15))
            ->also(Validate\String()->length()->upto(20));
        $this->assertNull($validator->validate('just_right@valid.xyz'));
        $this->assertNotNull($validator->validate('@invalid@email@xyz'));
        $this->assertNotNull($validator->validate('this_email_is@too_long.xyz'));
        $this->assertNotNull($validator->validate('too@short.xyz'));
    }
}

class SentinelTest extends \PHPUnit_Framework_TestCase {

    public function testSentinel() {
        $validator = Validate\Email();
        $this->assertNotNull($validator->validate(new Sentinel\NotFound()));
    }
    
    public function testSentinelWithChaining() {
        $validator = Validate\Email()->also(Validate\String()->length()->upto(5));
        $this->assertNotNull($validator->validate(new Sentinel\NotFound()));
    }
}
  
?>
