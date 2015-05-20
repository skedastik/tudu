<?php
namespace Tudu\Data\Repository;

use \Tudu\Core;
use \Tudu\Core\Chainable\Sentinel;
use \Tudu\Data\Model;
use \Tudu\Core\Data\Repository\Error;
use \Tudu\Core\Logger;

final class User extends Core\Data\Repository\Repository {
    
    /**
     * Fetch a single user with the given ID.
     * 
     * @param int $id User ID.
     * @return mixed User model on success, otherwise an Error object.
     */
    public function getByID($id) {
        $result = $this->db->query(
            'select user_id, email, password_hash, kvs, status, edate, cdate from tudu_user where user_id = $1;',
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
    
    /**
     * Sign up a new user.
     * 
     * @param string $email User's email address.
     * @param string $passwordHash A secure password hash.
     * @param string $ip User's IP address.
     * @return int|\Tudu\Core\Error New user's ID on success, otherwise an Error
     * object.
     */
    public function signupUser($email, $passwordHash, $ip) {
        $user = new Model\User([
            'email' => $email
        ]);
            
        $errors = $user->normalize();
        if (!is_null($errors)) {
            return Error::Validation($errors);
        }
        
        $email = $user->get('email');
        $result = $this->db->query(
            'select tudu.signup_user($1, $2, $3) as result;',
            [$email, $passwordHash,  $ip]
        );
        $result = (int)$result[0]['result'];
        
        if ($result == -1) {
            $user = new Model\User([
                'email' => new Sentinel(Error::ALREADY_IN_USE_CONTEXT)
            ]);
            return Error::AlreadyInUse($user->normalize());
        }
        
        $logger = Logger::getInstance();
        $logger->info("Signed up user [$email] with ID $result.");
        
        return $result;
    }
}
?>
