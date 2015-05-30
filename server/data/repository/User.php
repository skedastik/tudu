<?php
namespace Tudu\Data\Repository;

use \Tudu\Core\Data\Repository;
use \Tudu\Core\Exception;
use \Tudu\Data\Model;

final class User extends Repository {
    
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
            throw new Exception\Client('User not found.');
        }
        return $this->prenormalize(new Model\User($result[0]));
    }
    
    /**
     * Fetch a single user with the given email address.
     * 
     * @param string $email Email address.
     * @return mixed User model on success, otherwise an Error object.
     */
    public function getByEmail($email) {
        $result = $this->db->query(
            'select user_id, email, password_hash, kvs, status, edate, cdate from tudu_user where email = $1;',
            [$email]
        );
        if ($result === false) {
            throw new Exception\Client('User not found.');
        }
        return $this->prenormalize(new Model\User($result[0]));
    }
    
    /**
     * Sign up a new user.
     * 
     * @param string $email Email address.
     * @param string $passwordHash A secure password hash.
     * @param string $ip IP address.
     * @param bool $autoConfirm (optional) Automatically confirm user.
     * @return int|\Tudu\Core\Error New user's ID on success, Error otherwise.
     */
    public function signupUser($email, $passwordHash, $ip, $autoConfirm = false) {
        $result = $this->db->queryValue(
            'select tudu.signup_user($1, $2, $3, \'\', $4);',
            [$email, $passwordHash, $ip, $autoConfirm ? 't' : 'f']
        );
        if ($result == -1) {
            throw new Exception\Validation(null, [Model\User::EMAIL => 'Email address is already in use.'], 409);
        }
        return $result;
    }
    
    /**
     * Confirm an existing user's email address using their signup token.
     * 
     * @param int $id User ID.
     * @param string $signupToken Signup token.
     * @param string $ip IP address.
     * @return int|Tudu\Core\Error User's ID on success, Error otherwise.
     */
    public function confirmUser($id, $signupToken, $ip) {
        $result = $this->db->queryValue(
            'select tudu.confirm_user($1, null, $2, $3);',
            [$id, $signupToken, $ip]
        );
        switch ($result) {
            case -1:
                throw new Exception\Client('User not found.', null, 404);
            case -2:
                throw new Exception\Client('Signup token does not match.', null, 409);
            case -3:
                throw new Exception\Client('User has already been confirmed.', null, 409);
        }
        return $result;
    }
    
    /**
     * Update a user's password hash.
     * 
     * @param int $id User ID.
     * @param string $newPasswordHash New password hash.
     * @param string $ip IP address.
     * @return int|Tudu\Core\Error User's ID on success, Error otherwise.
     */
    public function setUserPasswordHash($id, $newPasswordHash, $ip) {
        $result = $this->db->queryValue(
            'select tudu.set_user_password_hash($1, $2, $3);',
            [$id, $newPasswordHash, $ip]
        );
        if ($result == -1) {
            throw new Exception\Client('User ID not found.', null, 404);
        }
        return $result;
    }
    
    /**
     * Update a user's email address.
     * 
     * @param int $id User ID.
     * @param string $email New email address.
     * @param string $ip IP address.
     * @return int|Tudu\Core\Error User's ID on success, Error otherwise.
     */
    public function setUserEmail($id, $email, $ip) {
        $result = $this->db->queryValue(
            'select tudu.set_user_email($1, $2, $3);',
            [$id, $email, $ip]
        );
        switch ($result) {
            case -1:
                throw new Exception\Client('User ID not found.', null, 404);
            case -2:
                throw new Exception\Client('Provided email address is identical to current email address.', null, 409);
            case -3:
                throw new Exception\Validation(null, [Model\User::EMAIL => 'Email address is already in use.'], 409);
        }
        return $result;
    }
}
?>
