<?php
namespace Tudu\Core\Data\Repository;

require_once __DIR__.'/../DbConnection.php';
require_once __DIR__.'/../model/User.php';

use \Tudu\Core\Data\Model\User;

class User extends Repository {
    
    public function getById($id) {
        $result = $this->db->query(
            'select (user_id, email, password_salt, password_hash, kvs, status, edate, cdate) from tudu.get_user_by_id($1);',
            [$id]
        );
        
        if (is_null($result)) {
            $result = [
                // 'user_id' => /* TODO: Use validation sentinel */
            ]
        }
        
        return new User($result);
    }
}
?>
