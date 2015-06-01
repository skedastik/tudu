<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Data\Model\User;

/**
 * Request handler base class for user endpoints
 */
abstract class Endpoint extends \Tudu\Core\Handler\API {
    
    protected function getModel() {
        return new User();
    }
}

?>
