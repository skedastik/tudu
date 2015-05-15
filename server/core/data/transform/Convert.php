<?php
namespace Tudu\Core\Data\Transform;

use Tudu\Core\TuduException;

/**
 * Chainable data conversion transformer.
 */
final class Convert extends Transform {
    
    protected $outputType;
    
    // output types
    const OUTPUT_STRING = 'string';
    const OUTPUT_BOOLEAN_STRING = 'boolean_string';
    
    public function __construct() {
        parent::__construct();
        $this->outputType = null;
    }
    
    // Option methods ----------------------------------------------------------
    
    /**
     * Convert input to string.
     * 
     * This simply casts the input to a string and outputs the result.
     */
    public function toString() {
        $this->outputType = Convert::OUTPUT_STRING;
        return $this;
    }
    
    /**
     * Convert input to string.
     * 
     * Output "truthiness" of input (based on PHP's rules) as 't' or 'f'.
     */
    public function toBooleanString() {
        $this->outputType = Convert::OUTPUT_BOOLEAN_STRING;
        return $this;
    }
    
    // Processing methods ------------------------------------------------------
    
    static protected $dispatchTable = [
        Convert::OUTPUT_STRING => 'processToString',
        Convert::OUTPUT_BOOLEAN_STRING => 'processToBooleanString'
    ];
    
    protected function processToString($data) {
        return (string)$data;
    }
    
    protected function processToBooleanString($data) {
        return $data ? 't' : 'f';
    }
    
    protected function process($data) {
        if (is_null($this->outputType)) {
            throw new TuduException('Convert::execute called without specifying output type.');
        }
        
        return $this->dispatch($this->outputType, $data);
    }
}
?>
