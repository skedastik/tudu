<?php
namespace Tudu\Core\Data\Repository;

use \Tudu\Core;

/**
 * Special repository error strings.
 */
class Error {
    
    // Repository error strings
    const VALIDATION = 'Validation Error';
    const RESOURCE_NOT_FOUND = 'Resource Not Found Error';
    
    /**
     * Shorthand factory function for validation errors.
     * 
     * @param array $context Key/value array describing the resource
     */
    public static function Validation($context = null) {
        return new Core\Error(Error::VALIDATION, 'Invalid resource descriptor.', $context);
    }
    
    /**
     * Shorthand factory function for "resource not found" errors.
     * 
     * @param array $context Key/value array describing the resource
     */
    public static function ResourceNotFound($context = null) {
        return new Core\Error(Error::RESOURCE_NOT_FOUND, 'The specified resource could not be found.', $context);
    }
}
?>
