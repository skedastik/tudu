<?php
namespace Tudu\Core\Data\Validate\Sentinel;

/**
 * A resource "not found" sentinel.
 */
class NotFound implements Sentinel {
    
    public function getError() {
        return 'not found.';
    }
}
?>
