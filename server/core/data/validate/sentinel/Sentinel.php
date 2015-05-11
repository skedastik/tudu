<?php
namespace Tudu\Core\Data\Validate\Sentinel;

/**
 * An interface for data validation "sentinels".
 * 
 * Sentinels are useful for non-semantic data validation errors like "User ID
 * not found!". Implement Sentinel and pass an instance to Validate::validate
 * to force a validation error.
 * 
 * Example:
 * 
 *    $validator = Validate\Email();
 *    
 *    // return a special "not found" validation error
 *    $validator->validate(new Sentinel\NotFound());
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
