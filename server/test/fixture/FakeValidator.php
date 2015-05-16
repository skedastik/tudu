<?php
namespace Tudu\Test\Fixture;

use \Tudu\Core\Data\Validate\Validate;

class FakeValidator extends \Tudu\Core\Data\Validate\Validator {
    
    protected function process($data) {
        return $this->apply($data);
    }
}
?>
