<?php
namespace Tudu\Handler\Api\Task;

use \Tudu\Data\Model\Task;

/**
 * Request handler base class for task endpoints
 */
abstract class Endpoint extends \Tudu\Core\Handler\API {
    
    protected function getModel() {
        return new Task();
    }
}

?>
