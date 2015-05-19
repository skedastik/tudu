<?php
namespace Tudu\Data\Model;

use \Tudu\Core\Data\Model\Model;
use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;

/**
 * User model.
 */
final class User extends Model {
    
    protected function getNormalizers() {
        return [
            'user_id' => Transform::Convert()->to()->integer()
                      -> then(Validate::Number()->is()->positive())
                      -> then(Transform::Description()->to('User ID')),
            
            'email' => Transform::String()->trim()
                    -> then(Validate::String()->is()->validEmail()->with()->length()->upTo(64))
                    -> then(Transform::Description()->to('Email address'))
        ];
    }
    
    protected function getSanitizers() {
        return [
            'email' => Transform::String()->escapeForHTML()
        ];
    }
}
?>
