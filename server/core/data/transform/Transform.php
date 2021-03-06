<?php
namespace Tudu\Core\Data\Transform;

/**
 * Shorthand factory functions for creating Transformer objects.
 */
final class Transform {
    
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
    
    /**
     * Shorthand factory function for Transform\HStore.
     */
    public static function HStore() {
        return new HStore();
    }
    
    /**
     * Shorthand factory function for Transform\Password.
     */
    public static function Password() {
        return new Password();
    }
    
    /**
     * Shorthand factory function for Transform\Extract.
     */
    public static function Extract() {
        return new Extract();
    }
}
?>
