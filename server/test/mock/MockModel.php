<?php
namespace Tudu\Test\Mock;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;

class MockModel extends \Tudu\Core\Data\Model {
    
    protected static $getNormalizersCallCount = 0;
    protected static $getSanitizersCallCount = 0;
    
    protected function getNormalizers() {
        self::$getNormalizersCallCount++;
        return [
            'name' => Transform::String()->trim()
                   -> then(Validate::String()->length()->from(5)->upTo(35))
                   -> then(Transform::Description()->to('Name')),
            
            'email' => Validate::String()->is()->validEmail()
                    -> then(Transform::Description()->to('Email address'))
        ];
    }
    
    protected function getSanitizers() {
        self::$getSanitizersCallCount++;
        return [
            'name-only' => [
                'name' => Transform::String()->stripTags(),
            ],
            
            'email-only' => [
                'email' => Transform::String()->escapeForHTML()
            ]
        ];
    }
    
    public static function getNormalizersMethodCallCount() {
        return self::$getNormalizersCallCount;
    }
    
    public static function getSanitizersMethodCallCount() {
        return self::$getSanitizersCallCount;
    }
}
?>
