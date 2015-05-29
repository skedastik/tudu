<?php
namespace Tudu\Data\Model;

use \Tudu\Core\Data\Model;
use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;
use \Tudu\Delegate\PHPass;

/**
 * User model.
 */
final class User extends Model {
    
    const USER_ID       = 'user_id';
    const EMAIL         = 'email';
    const PASSWORD      = 'password';
    const PASSWORD_HASH = 'password_hash';
    const SIGNUP_TOKEN  = 'signup_token';
    
    protected function getNormalizers() {
        return [
            self::USER_ID => Transform::Convert()->to()->integer(),
            
            self::EMAIL => Transform::Convert()->to()->string()
                        -> then(Transform::String()->trim())
                        -> then(Validate::String()->is()->validEmail()->with()->length()->upTo(64))
                        -> then(Transform::Description()->to('Email address')),
            
            self::PASSWORD => Transform::Convert()->to()->string()
                           -> then(Validate::String()->length()->from(8))
                           -> then(Transform::Description()->to('Password'))
        ];
    }
    
    protected function getSanitizers() {
        return [
            Model::SCHEME_HTML => [
                self::EMAIL => Transform::String()->escapeForHTML()
            ]
        ];
    }
}
?>
