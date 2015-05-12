<?php
namespace Tudu\Core\Data\Transform;

/**
 * Chainable data transformation base class.
 */
abstract class Transform extends \Tudu\Core\Chainable {
    
    /**
     * Shorthand factory functions for subclasses.
     */
    public static function ToString() { return new ToString(); }
}
?>
