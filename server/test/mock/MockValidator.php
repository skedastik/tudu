<?php
namespace Tudu\Test\Mock;

use \Tudu\Core\Data\Validate\Validate;

class MockValidator extends \Tudu\Core\Data\Validate\Validator {
    
    protected function process($data) {
        return $this->applyOptions($data);
    }
}
?>
