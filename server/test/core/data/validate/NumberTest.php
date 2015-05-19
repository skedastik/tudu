<?php
namespace Tudu\Test\Core\Data\Validate;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;
use \Tudu\Core\Chainable\Sentinel;

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
?>
