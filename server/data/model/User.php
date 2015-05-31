<?php
namespace Tudu\Data\Model;

use \Tudu\Core\Data\Model;
use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;
use \Tudu\Core\Delegate;
use \Tudu\Core\Exception;

/**
 * User model.
 */
final class User extends Model {
    
    const USER_ID       = 'user_id';
    const EMAIL         = 'email';
    const PASSWORD      = 'password';
    const NEW_PASSWORD  = 'new_password';
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
                           -> then(Transform::Password()->with(self::getPasswordDelegate()))
                           -> then(Transform::Description()->to('Password')),
            
            self::SIGNUP_TOKEN => Transform::Convert()->to()->string()
        ];
    }
    
    protected function getSanitizers() {
        return [
            Model::SCHEME_HTML => [
                self::EMAIL => Transform::String()->escapeForHTML()
            ]
        ];
    }
    
    protected static $propertyAliases = [
        self::NEW_PASSWORD => self::PASSWORD
    ];
    
    // User password delegate singleton ----------------------------------------
    
    private static $passwordDelegate = null;
    
    /**
     * Set user password delegate singleton.
     * 
     * This will be used to hash and compare user passwords for the life of the
     * application.
     * 
     * @param \Tudu\Core\Delegate\Password $passwordDelegate
     */
    public static function setPasswordDelegate(Delegate\Password $passwordDelegate) {
        if (self::$passwordDelegate !== NULL) {
            throw new Exception\Internal('A user password delegate has already been instantiated.');
        }
        self::$passwordDelegate = $passwordDelegate;
        return $passwordDelegate;
    }
    
    /**
     * Get user password delegate singleton.
     * 
     * @return \Tudu\Core\Delegate\Password
     */
    public static function getPasswordDelegate() {
        if (self::$passwordDelegate === NULL) {
            throw new Exception\Internal('No user password delegate has been set.');
        }
        return self::$passwordDelegate;
    }
}
?>
