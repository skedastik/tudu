<?php
namespace Tudu\Core;

/**
 * Tudu application error class.
 */
class Error {
    
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
