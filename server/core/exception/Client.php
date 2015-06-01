<?php
namespace Tudu\Core\Exception;

use \Tudu\Core\Arrayable;

/**
 * Client exception class.
 * 
 * Client exceptions indicate user or client errors.
 */
class Client extends \Exception implements Arrayable {
    
    // override this constant in subclasses to a more specific string
    const ERROR_STRING = 'Error';
    
    // keys for array representation of exception
    const KEY_ERROR            = 'error';
    const KEY_DESCRIPTION      = 'description';
    const KEY_CONTEXT          = 'context';
    
    protected $description;
    protected $context;
    protected $httpStatusCode;
    
    /**
     * Constructor.
     * 
     * @param string $description (optional) Error description.
     * @param array $context (optional) Contextual information in the form of a
     * key/value array.
     * @param int $httpStatusCode (optional) Errors may be generated in response
     * to an HTTP requests. In such cases, you should tag the error with an
     * appropriate HTTP response code.
     * @param int $code (optional)
     * @param \Exception $previous (optional)
     */
    public function __construct(
        $description = null,
        array $context = null,
        $httpStatusCode = null,
        $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct(static::ERROR_STRING, $code, $previous);
        $this->error = static::ERROR_STRING;
        $this->description = $description;
        $this->context = $context;
        $this->httpStatusCode = $httpStatusCode;
    }
    
    /**
     * Return a key/value array representation of this exception.
     */
    public function asArray() {
        $error = [self::KEY_ERROR => static::ERROR_STRING];
        if (!is_null($this->description)) {
            $error[self::KEY_DESCRIPTION] = $this->description;
        }
        if (!is_null($this->context)) {
            $error[self::KEY_CONTEXT] = $this->context;
        }
        return $error;
    }
    
    /**
     * Return the client error description.
     */
    public function getDescription() {
        return $this->description;
    }
    
    /**
     * Return the client error context.
     */
    public function getContext() {
        return $this->context;
    }
    
    /**
     * Return the client error HTTP status code.
     */
    public function getHttpStatusCode() {
        return $this->httpStatusCode;
    }
}

?>
