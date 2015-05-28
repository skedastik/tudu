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
    
    protected function getNormalizers() {
        return [
            'user_id' => Transform::Convert()->to()->integer(),
            
            'email' => Transform::Convert()->to()->string()
                    -> then(Transform::String()->trim())
                    -> then(Validate::String()->is()->validEmail()->with()->length()->upTo(64))
                    -> then(Transform::Description()->to('Email address')),
            
            'password' => Transform::Convert()->to()->string()
                       -> then(Validate::String()->length()->from(8))
                       -> then(Transform::Description()->to('Password'))
        ];
    }
    
    protected function getSanitizers() {
        return [
            'html' => [
                'email' => Transform::String()->escapeForHTML()
            ]
        ];
    }
}
?>
