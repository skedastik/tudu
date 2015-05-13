<?php
namespace Tudu\Core\Data\Repository;

use \Tudu\Core\Data\Model\User;
use \Tudu\Core\Data\Validate\Sentinel;

final class User extends Repository\Repository {
    
    public function getById($id) {
        $result = $this->db->query(
            'select (user_id, email, password_salt, password_hash, kvs, status, edate, cdate) from tudu_user where user_id = $1;',
            [$id]
        );
        
        if (is_null($result)) {
            $result = ['user_id' => Sentinel\Factory::NotFound()];
        }
        
        return new User($result);
    }
}
?>
