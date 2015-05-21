<?php
namespace Tudu\Core\Data\Repository;

use \Tudu\Core;

/**
 * Special repository error strings.
 */
class Error {
    
    // Repository error types
    const GENERIC = 'Error';
    const VALIDATION = 'Validation Error';
    const FATAL = 'Fatal Error';
    
    /**
     * Shorthand factory function for generic errors.
     * 
     * @param string $description (optional)
     * @param array $context (optional) Key/value array containing contextual
     * error info.
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
     * Useful for validation errors that may have resulted from user input. Such
     * errors should provide a key/value array as context, where each key is an
     * indentifier (possibly corresponding to form field names), and each value
     * is a human-readable validation error string.
     * 
     * @param string $description (optional)
     * @param array $context (optional) Key/value array containing contextual
     * error info.
     */
    public static function Validation(
        $description = null,
        $context = null
    ) {
        return new Core\Error(Error::VALIDATION, $description, $context);
    }
    
    /**
     * Shorthand factory function for fatal errors.
     * 
     * Fatal errors indicate an unrecoverable error condition.
     * 
     * @param string $description (optional)
     * @param array $context (optional) Key/value array containing contextual
     * error info.
     */
    public static function Fatal(
        $description = null,
        $context = null
    ) {
        return new Core\Error(Error::FATAL, null, $context);
    }
}
?>
