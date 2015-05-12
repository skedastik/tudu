<?php
namespace Tudu\Core\Data\Transform;

/**
 * Chainable data transformation base class.
 */
abstract class Transform extends \Tudu\Core\Chainable {
    
    /**
     * Shorthand factory function for Transform\ToString.
     */
    public static function ToString() { return new ToString(); }
}
?>
