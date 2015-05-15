<?php
namespace Tudu\Core\Data\Repository;

use \Tudu\Core\Data\Model;
use \Tudu\Core\Data\Validate\Sentinel;

final class User extends Repository {
    
    public function getById($id) {
        // TODO
        
        $result = $this->db->query(
            'select (user_id, email, password_salt, password_hash, kvs, status, edate, cdate) from tudu_user where user_id = $1;',
            [$id]
        );
        
        if (is_null($result)) {
            $result = ['user_id' => Sentinel\Factory::NotFound()];
        }
        
        return new Model\User($result);
    }
}
?>
