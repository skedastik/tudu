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
    public static function Generic(
        $description = null,
        $context = null
    ) {
        return new Core\Error(Error::GENERIC, $description, $context);
    }
    
    /**
     * Shorthand factory function for validation errors.
     * 
     * @param string $description (optional)
     * @param array $context Key/value array describing the resource
     */
    public static function Validation(
        $description = 'Invalid resource descriptor.',
        $context = null
    ) {
        return new Core\Error(Error::VALIDATION, $description, $context);
    }
    
    /**
     * Shorthand factory function for "already in use" errors.
     * 
     * @param string $description (optional)
     * @param array $context Key/value array describing the resource
     */
    public static function AlreadyInUse(
        $description = null,
        $context = null
    ) {
        return new Core\Error(Error::ALREADY_IN_USE, null, $context);
    }
    
    /**
     * Shorthand factory function for "resource not found" errors.
     * 
     * @param string $description (optional)
     * @param array $context Key/value array describing the resource
     */
    public static function ResourceNotFound(
        $description = 'The specified resource could not be found.',
        $context = null
    ) {
        return new Core\Error(Error::RESOURCE_NOT_FOUND, $description, $context);
    }
}
?>
