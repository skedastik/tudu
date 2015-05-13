<?php
namespace Tudu\Core\Data\Transform;

/**
 * Chainable data transformation base class.
 */
abstract class Transform extends \Tudu\Core\Chainable\Chainable {
    
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
}
?>
