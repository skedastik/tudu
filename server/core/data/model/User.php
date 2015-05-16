<?php
namespace Tudu\Core\Data\Model;

use \Tudu\Core\Data\Transform;
use \Tudu\Core\Data\Validate;

/**
 * User model.
 */
final class User extends Model {
    
    protected function getNormalizers() {
        return [
            'user_id'       => Transform::String()->trim()
                            -> then(Validate::Basic())
                            -> then(Transform::Description()->to('User')),
            
            'email'         => Transform::String()->trim()
                            -> then(Validate::String()->is()->validEmail()
                            -> with()->length()->upTo(64))
                            -> then(Transform::Description()->to('Email address')),
            
            'password_salt' => Validate::String()->length()->upTo(64),
            
            'password_hash' => Validate::String()->length()->upTo(256)
        ];
    }
    
    protected function getSanitizers() {
        // TODO
    }
}
?>
