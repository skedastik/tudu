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
            return Error::ResourceNotFound('User ID not found.');
        }
        return $this->prenormalize(new Model\User($result[0]));
    }
    
    /**
     * Sign up a new user.
     * 
     * @param string $email Email address.
     * @param string $passwordHash A secure password hash.
     * @param string $ip IP address.
     * @return int|\Tudu\Core\Error New user's ID on success, Error otherwise.
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
            return Error::AlreadyInUse(null, ['email' => 'Email address already in use.']);
        }
        
        $logger = Logger::getInstance();
        $logger->info("Signed up user [$email] with ID $result.");
        
        return $result;
    }
    
    /**
     * Confirm an existing user's email address.
     * 
     * @param string $email Email address.
     * @param string $signupToken Signup token.
     * @param string $ip IP address.
     * @return int|Tudu\Core\Error User's ID on success, Error otherwise.
     */
    public function confirmUser($email, $signupToken, $ip) {
        $result = $this->db->query(
            'select tudu.confirm_user(null, $1, $2, $3) as result;',
            [$email, $signupToken, $ip]
        );
        $result = (int)$result[0]['result'];
        
        switch ($result) {
            case -1:
                return Error::ResourceNotFound('Email address not found.');
                break;
            case -2:
                return Error::Generic('Signup token does not match.');
                break;
            case -3:
                return Error::Generic('User has already been confirmed.');
                break;
        }
        
        $logger = Logger::getInstance();
        $logger->info("Confirmed user [$email] with ID $result.");
        
        return $result;
    }
}
?>
