<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Data\Model;
use \Tudu\Data\Repository;
use \Tudu\Core\Delegate;
use \Tudu\Core\Data\DbConnection;

/**
 * Request handler base class for user resource endpoints.
 */
abstract class UserEndpoint extends \Tudu\Core\Handler\API {
    
    protected $userRepo;
    
    public function __construct(Delegate\App $delegate, DbConnection $db, array $context = []) {
        parent::__construct($delegate, $db, $context);
        $this->userRepo = new Repository\User($this->db);
    }
    
    protected function getModel() {
        return new Model\User();
    }
}

?>
