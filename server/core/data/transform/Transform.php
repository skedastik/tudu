<?php
namespace Tudu\Core\Data\Transform;

/**
 * Chainable data transformations.
 */
class Transform extends \Tudu\Core\Chainable {
    
    final public function transform($data) {
        return $this->process($data);
    }
}
?>
