<?php
namespace Tudu\Test\Core\Data\Validate;

use \Tudu\Core\Data\Validate;
use \Tudu\Core\Data\Validate\Sentinel;

class StringTest extends \PHPUnit_Framework_TestCase {

    public function testLengthLowerBound() {
        $validator = (new Validate\String())->length()->from(10);
        $this->assertNull($validator->execute('this string is valid even if it is rather long'));
        $this->assertNotNull($validator->execute('too short'));
    }

    public function testLengthUpperBound() {
        $validator = (new Validate\String())->length()->upTo(5);
        $this->assertNull($validator->execute('valid'));
        $this->assertNotNull($validator->execute('toooooooooooooooooo long'));
    }

    public function testLengthRange() {
        $validator = (new Validate\String())->length()->from(10)->upTo(15);
        $this->assertNull($validator->execute('valid string'));
        $this->assertNotNull($validator->execute('too short'));
        $this->assertNotNull($validator->execute('toooooooooooooooooo long'));
    }
}

class EmailTest extends \PHPUnit_Framework_TestCase {

    public function testEmailFormat() {
        $validator = new Validate\Email();
        $this->assertNull($validator->execute('valid@email.xyz'));
        $this->assertNull($validator->execute('123@123.xyz'));
        $this->assertNull($validator->execute('123@123.blarg.xyz'));
        $this->assertNull($validator->execute('123.abc@123.blarg.xyz'));
        $this->assertNotNull($validator->execute('@invalid@email@xyz'));
        $this->assertNotNull($validator->execute('invalid@email'));
    }
}

class ChainingTest extends \PHPUnit_Framework_TestCase {

    public function testChainTwoValidators() {
        $validator = (new Validate\Email())->then((new Validate\String())->length()->upTo(15));
        $this->assertNull($validator->execute('valid@email.xyz'));
        $this->assertNotNull($validator->execute('@invalid@email@xyz'));
        $this->assertNotNull($validator->execute('this_email_is@too_long.xyz'));
    }

    public function testChainThreeValidators() {
        $validator = (new Validate\Email())
            ->then((new Validate\String())->length()->from(15))
            ->then((new Validate\String())->length()->upTo(20));
        $this->assertNull($validator->execute('just_right@valid.xyz'));
        $this->assertNotNull($validator->execute('@invalid@email@xyz'));
        $this->assertNotNull($validator->execute('this_email_is@too_long.xyz'));
        $this->assertNotNull($validator->execute('too@short.xyz'));
    }
}

class SentinelTest extends \PHPUnit_Framework_TestCase {

    public function testSentinel() {
        $validator = new Validate\Email();
        $this->assertNotNull($validator->execute(new Sentinel\NotFound()));
    }
    
    public function testSentinelWithChaining() {
        $validator = (new Validate\Email())->then((new Validate\String())->length()->upTo(5));
        $this->assertNotNull($validator->execute(new Sentinel\NotFound()));
    }
}
  
?>
