<?php
namespace Tudu\Test\Fixture;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;

class FakeModel extends \Tudu\Core\Data\Model\Model {
    
    protected function getNormalizers() {
        return [
            'name'  => Transform::String()->trim()
                    -> then(Validate::String()->length()->from(5)->upTo(35))
                    -> then(Transform::Description()->to('Name')),
            'email' => Validate::String()->is()->validEmail()
                    -> then(Transform::Description()->to('Email address'))
        ];
    }
    
    protected function getSanitizers() {
        return [
            'name'  => Transform::String()->stripTags(),
            'email' => Transform::String()->escapeForHTML()
        ];
    }
}
?>
