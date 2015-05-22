<?php
namespace Tudu\Core;

/**
 * Tudu application error class.
 */
class Error {
    
    // Repository error types
    const NOTICE = 'Notice';
    const GENERIC = 'Error';
    const VALIDATION = 'Validation Error';
    const FATAL = 'Fatal Error';
    
    /**
     * Shorthand factory function for notices.
     * 
     * Notices are purely informational. They do not indicate errors.
     * 
     * @param string $description (optional)
     * @param array $context (optional) Key/value array containing contextual
     * error info.
     */
    public static function Notice(
        $description = null,
        $context = null,
        $httpStatusCode = null
    ) {
        return new Error(Error::NOTICE, $description, $context, $httpStatusCode);
    }
    
    /**
     * Shorthand factory function for generic errors.
     * 
     * @param string $description (optional)
     * @param array $context (optional) Key/value array containing contextual
     * error info.
     */
    public static function Generic(
        $description = null,
        $context = null,
        $httpStatusCode = null
    ) {
        return new Error(Error::GENERIC, $description, $context, $httpStatusCode);
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
        $context = null,
        $httpStatusCode = null
    ) {
        return new Error(Error::VALIDATION, $description, $context, $httpStatusCode);
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
        $context = null,
        $httpStatusCode = null
    ) {
        return new Error(Error::FATAL, null, $context, $httpStatusCode);
    }
    
    protected $error;
    protected $description;
    protected $context;
    protected $httpStatusCode;
    
    /**
     * Constructor.
     * 
     * @param string $error Error string.
     * @param string $description (optional) Error description.
     * @param array $context (optional) Contextual information in the form of a
     * key/value array.
     * @param int $httpStatusCode (optional) Errors may be generated in response
     * to an HTTP requests. In such cases, you should tag the error with an
     * appropriate HTTP response code.
     */
    public function __construct($error, $description = null, array $context = null, $httpStatusCode = null) {
        $this->error = $error;
        $this->description = $description;
        $this->context = $context;
        $this->httpStatusCode = $httpStatusCode;
    }
    
    /**
     * Return a key/value array representation of this error.
     * 
     * The HTTP status code will NOT be included in the key/value array.
     */
    public function asArray() {
        $error = ['error' => $this->error];
        if (!is_null($this->description)) {
            $error['description'] = $this->description;
        }
        if (!is_null($this->context)) {
            $error['context'] = $this->context;
        }
        return $error;
    }
    
    /**
     * Return the error string.
     */
    public function getError() {
        return $this->error;
    }
    
    /**
     * Return the error description.
     */
    public function getDescription() {
        return $this->description;
    }
    
    /**
     * Return the error context.
     */
    public function getContext() {
        return $this->context;
    }
    
    /**
     * Return the HTTP status code.
     */
    public function getHttpStatusCode() {
        return $this->context;
    }
}
?>
