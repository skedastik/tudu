<?php
namespace Tudu\Core\Data\Validate\Sentinel;

/**
 * Data validator sentinel interface. You can implement Sentinel in a class and
 * pass an instance of that class to Validate::validate to force a validation
 * error. Useful for non-semantic data validation errors like "User ID not
 * found!".
 * 
 * Example:
 * 
 *    $validator = Validate\Email();
 *    $validator->validate(new Sentinel\NotFound());    // returns an error
 */
interface Sentinel {
    
    /**
     * Return an error string. Override this.
     * 
     * @return string Error string.
     */
    public function getError();
}
?>
