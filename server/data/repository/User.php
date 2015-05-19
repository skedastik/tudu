<?php
namespace Tudu\Data\Repository;

use \Tudu\Core;
use \Tudu\Conf\Conf;
use \Tudu\Core\Chainable\Sentinel;
use \Tudu\Data\Model;
use \Tudu\Core\Data\Repository\Error;

final class User extends Core\Data\Repository\Repository {
    
    public function getByID($id) {
        $result = $this->db->query(
            'select (user_id, email, password_salt, password_hash, kvs, status, edate, cdate) from tudu_user where user_id = $1;',
            [$id]
        );
        
        if ($result === false) {
            $user = new Model\User([
                'user_id' => new Sentinel(Error::RESOURCE_NOT_FOUND_CONTEXT)
            ]);
            return Error::ResourceNotFound($user->normalize());
        }
        
        return $this->prenormalize(new Model\User($result[0]));
    }
    
    public function signupNewUser($email, $password, $ip) {
        $user = new Model\User([
            'email' => $email
        ]);
        $errors = $user->normalize();
        if (!is_null($errors)) {
            return Error::Validation($errors);
        }
        
        // create or replace function tudu.signup_user(
        //     _email          varchar,
        //     _pw_salt        varchar,
        //     _pw_hash        varchar,
        //     _ip             inet default null,
        //     _kvs            hstore default '',
        //     _autoconfirm    boolean default false
        // ) returns bigint as $$
        
        $result = $this->db->query(
            /* TODO */
        );
    }
}
?>
