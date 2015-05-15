<?php
namespace Tudu\Core\Data\Transform;

/**
 * Chainable data transformation base class.
 */
abstract class Transform extends \Tudu\Core\Chainable\Chainable {
    
    /**
     * When extending Transform, you can define a static $functionMap as a
     * convenient way of mapping option strings to methods. See
     * Transform::dispatch().
     */
    static protected $functionMap = [];
    
    /**
     * Shorthand factory function for Transform\String.
     */
    public static function String() {
        return new String();
    }
    
    /**
     * Shorthand factory function for Transform\Convert.
     */
    public static function Convert() {
        return new Convert();
    }
    
    /**
     * Shorthand factory function for Transform\DescriptionTo.
     */
    public static function DescriptionTo($description) {
        return new DescriptionTo($description);
    }
    
    /**
     * Dispatch a method call mapped to the given string.
     * 
     * This method uses late static binding to call the method mapped to the
     * input string by a subclass' function map.
     * 
     * @param string $string The input string.
     * @param mixed ...$args Arguments to pass to the mapped method.
     * @return mixed The return value of the mapped method.
     */
    public function dispatch($string, ...$args) {
        return $this->{static::$functionMap[$string]}(...$args);
    }
}
?>
