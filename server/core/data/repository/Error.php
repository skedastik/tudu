<?php
namespace Tudu\Core\Data\Repository;

use \Tudu\Core;

/**
 * Special repository error strings.
 */
class Error {
    
    // Repository error strings
    const GENERIC = 'Error';
    const VALIDATION = 'Validation Error';
    const RESOURCE_NOT_FOUND = 'Resource Not Found';
    const ALREADY_IN_USE = 'Already In Use';
    
    /**
     * Contextual error strings. These match the format described in the
     * Validator class documentation.
     */
    const RESOURCE_NOT_FOUND_CONTEXT = 'was not found';
    const ALREADY_IN_USE_CONTEXT = 'is already in use';
    
    /**
     * Shorthand factory function for generic errors.
     * 
     * @param string $description (optional)
     * @param array $context Key/value array describing the resource
     */
    public static function Generic($description = null, $context = null) {
        return new Core\Error(Error::GENERIC, $description, $context);
    }
    
    /**
     * Shorthand factory function for validation errors.
     * 
     * @param array $context Key/value array describing the resource
     */
    public static function Validation($context = null) {
        return new Core\Error(Error::VALIDATION, 'Invalid resource descriptor.', $context);
    }
    
    /**
     * Shorthand factory function for "already in use" errors.
     * 
     * @param array $context Key/value array describing the resource
     */
    public static function AlreadyInUse($context = null) {
        return new Core\Error(Error::ALREADY_IN_USE, null, $context);
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
