<?php
namespace Tudu\Core;

/**
 * Tudu application error class.
 */
class Error {
    
    protected $error;
    protected $description;
    protected $context;
    
    const KEY_ERROR = 'error';
    const KEY_DESCRIPTION = 'description';
    const KEY_CONTEXT = 'context';
    
    /**
     * Constructor.
     * 
     * @param string $error Error string.
     * @param string $description (optional) Error description.
     * @param array $context (optional) Contextual information in the form of a
     * key/value array.
     */
    public function __construct($error, $description = null, array $context = null) {
        $this->error = $error;
        $this->description = $description;
        $this->context = $context;
    }
    
    /**
     * Return a key/value array representation of this error.
     */
    public function asArray() {
        $error = [ Error::KEY_ERROR => $this->error ];
        if (!is_null($this->description)) {
            $error[Error::KEY_DESCRIPTION] = $this->description;
        }
        if (!is_null($this->context)) {
            $error[Error::KEY_CONTEXT] = $this->context;
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
}
?>
