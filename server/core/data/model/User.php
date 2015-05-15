<?php
namespace Tudu\Core\Data\Model;

use \Tudu\Core\Data\Transform;
use \Tudu\Core\Data\Validate;

/**
 * User model.
 */
final class User extends Model {
    
    protected function getNormalizers() {
        // TODO
        return [
            'user_id'       => Validate::Basic()->describeAs('User'),
            
            'email'         => Validate::Email()->describeAs('Email address')
                            -> then(Validate::String()->length()->from(5)->upTo(64)),
            
            'password_salt' => Validate::String()->length()->from(8)->upTo(64),
            
            'password_hash' => Validate::String()->length()->from(8)->upTo(256)
        ];
    }
    
    protected function getSanitizers() {
        // TODO
    }
}
?>
