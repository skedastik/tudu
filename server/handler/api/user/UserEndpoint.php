<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Data\Model\User;

/**
 * Request handler for user resource endpoints.
 */
class UserEndpoint extends \Tudu\Core\Handler\API {
    
    protected function getModel() {
        return new User();
    }
}

?>
