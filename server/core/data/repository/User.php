<?php
namespace Tudu\Core\Data\Repository;

use \Tudu\Core\Data\Model;

final class User extends Repository {
    
    public function getByID($id) {
        $result = $this->db->query(
            'select (user_id, email, password_salt, password_hash, kvs, status, edate, cdate) from tudu_user where user_id = $1;',
            [$id]
        );
        
        if ($result === false) {
            return Error::ResourceNotFound([ 'user_id' => $id ]);
        }
        
        return $this->prenormalize(new Model\User($result[0]));
    }
}
?>
