<?php
namespace Tudu\Core\Data\Validate;

/**
 * Shorthand factory functions for creating Validator objects.
 */
final class Validate {
    
    /**
     * Shorthand factory function for default validator.
     * 
     * The default validator doesn't perform any validation. It simply outputs
     * whatever was input. This means you can input an arbitrary error string
     * wrapped in a Sentinel object to force a validation error. Unless
     * otherwise specified, classes that extend Validate inherit this behavior.
     */
    public static function Basic() { return new Validator(); }
    
    /**
     * Shorthand factory function for Validate\String.
     */
    public static function String() { return new String(); }
    
    /**
     * Shorthand factory function for Validate\Number.
     */
    public static function Number() { return new Number(); }
}
?>
