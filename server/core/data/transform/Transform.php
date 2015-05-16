<?php
namespace Tudu\Core\Data\Transform;

/**
 * Chainable data transformation base class.
 */
abstract class Transform extends \Tudu\Core\Chainable\OptionsChainable {
    
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
     * Shorthand factory function for Transform\Description.
     */
    public static function Description() {
        return new Description();
    }
}
?>
