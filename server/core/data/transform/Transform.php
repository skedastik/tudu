<?php
namespace Tudu\Core\Data\Transform;

/**
 * Chainable data transformation base class.
 */
abstract class Transform extends \Tudu\Core\Chainable\Chainable {
    
    /**
     * When extending Transform, you can define a static $dispatchTable in the
     * subclass for string-based method dispatching. This is a convenient way of
     * mapping option strings to methods.
     */
    static protected $dispatchTable = [];
    
    /**
     * Shorthand factory function for Transform\ToString.
     */
    public static function ToString() {
        return new ToString();
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
     * input string by a subclass' dispatch table.
     * 
     * @param string $string The input string.
     * @param mixed ...$args Arguments to pass to the mapped method.
     * @return mixed The return value of the mapped method.
     */
    public function dispatch($string, ...$args) {
        return $this->{static::$dispatchTable[$string]}(...$args);
    }
}
?>
