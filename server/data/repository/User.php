<?php
namespace Tudu\Data\Repository;

use \Tudu\Core\Data\Repository;
use \Tudu\Core\Exception;
use \Tudu\Data\Model\User as UserModel;
use \Tudu\Core\Data\Model;

final class User extends Repository {
    
    /**
     * Fetch a single user with matching ID or email address.
     * 
     * @param \Tudu\Core\Data\Model $user User model to match against (user ID
     * OR email address required).
     * @return \Tudu\Core\Data\Model A normalized model populated with data.
     */
    public function fetch(Model $user) {
        $this->normalize($user);
        if ($user->hasProperty(UserModel::USER_ID)) {
            $column = UserModel::USER_ID;
            $param = $user->get(UserModel::USER_ID);
        } else {
            $column = UserModel::EMAIL;
            $param = $user->get(UserModel::EMAIL);
        }
        $result = $this->db->query(
            'select user_id, email, password_hash, kvs, status, edate, cdate from tudu_user where '.$column.' = $1;',
            [$param]
        );
        if ($result === false) {
            throw new Exception\Client('User not found.');
        }
        $newUser = new UserModel($result[0]);
		$newUser->normalize(false);
		return $newUser;
    }
    
    /**
     * Sign up a new user.
     * 
     * @param \Tudu\Core\Data\Model $user User model to sign up (email and
     * password hash required).
     * @param string $ip IP address.
     * @param bool $autoConfirm (optional) Automatically confirm user. Defaults
     * to FALSE.
     * @return int New user's ID on success.
     */
    public function signupUser(Model $user, $ip, $autoConfirm = false) {
        $this->normalize($user);
        $result = $this->db->queryValue(
            'select tudu.signup_user($1, $2, $3, \'\', $4);',
            [
                $user->get(UserModel::EMAIL),
                $user->get(UserModel::PASSWORD),
                $ip,
                $autoConfirm ? 't' : 'f'
            ]
        );
        if ($result == -1) {
            throw new Exception\Validation(null, [UserModel::EMAIL => 'Email address is already in use.'], 409);
        }
        return $result;
    }
    
    /**
     * Confirm an existing user's email address using their signup token.
     * 
     * @param \Tudu\Core\Data\Model $user User model to confirm (user ID and
     * sign-up token required).
     * @param string $ip IP address.
     * @return int User's ID on success.
     */
    public function confirmUser(Model $user, $ip) {
        $this->normalize($user);
        $result = $this->db->queryValue(
            'select tudu.confirm_user($1, null, $2, $3);',
            [
                $user->get(UserModel::USER_ID),
                $user->get(UserModel::SIGNUP_TOKEN),
                $ip
            ]
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
     * Update an existing user.
     * 
     * User with matching ID will have its email and password hash updated to
     * match.
     * 
     * @param \Tudu\Core\Data\Model $user User model. User ID required. Email
     * and password optional.
     * @param string $ip IP address.
     * @return int User's ID on success.
     */
    public function updateUser(Model $user, $ip) {
        $this->normalize($user);
        $result = $this->db->queryValue(
            'select tudu.update_user($1, $2, $3, $4);',
            [
                $user->get(UserModel::USER_ID),
                $user->get(UserModel::EMAIL),
                $user->get(UserModel::PASSWORD),
                $ip
            ]
        );
        switch ($result) {
            case -1:
                throw new Exception\Client('User ID not found.', null, 404);
            case -2:
                throw new Exception\Validation(
                    null,
                    [UserModel::EMAIL => 'Email address is already in use.'],
                    409
                );
        }
        return $result;
    }
}
?>
