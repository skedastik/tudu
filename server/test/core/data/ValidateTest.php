<?php
namespace Tudu\Test\Core\Data\Validate;

require_once __DIR__.'/../../../core/data/validate/String.php';
require_once __DIR__.'/../../../../vendor/autoload.php';

use \Tudu\Core\Data\Validate;

class StringTest extends \PHPUnit_Framework_TestCase {

    public function testLengthValidation() {
        $validator = (new Validate\String())->length()->from(10)->to(15);
        $this->assertNull($validator->validate('valid string'));
        $this->assertNotNull($validator->validate('invalid'));
    }
}
  
?>
