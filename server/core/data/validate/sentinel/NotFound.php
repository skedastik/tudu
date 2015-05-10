<?php
namespace Tudu\Core\Data\Validate\Sentinel;

require_once __DIR__.'/Sentinel.php';

/**
 * A resource "not found" sentinel.
 */
class NotFound implements Sentinel {
    
    public function getError() {
        return 'not found.';
    }
}
?>
