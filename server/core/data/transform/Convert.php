<?php
namespace Tudu\Core\Data\Transform;

use Tudu\Core\TuduException;

/**
 * Chainable data conversion transformer.
 */
final class Convert extends Transform {
    
    protected $outputType;
    protected $interpreter;
    
    // output types
    const OUTPUT_STRING = 'string';
    
    // ways of interpreting input
    const INTERPET_RAW = 'interpret_raw';
    const INTERPET_BOOLEAN = 'interpret_bool';
    
    public function __construct() {
        parent::__construct();
        $this->outputType = null;
        $this->interpreter = Convert::INTERPET_RAW;
    }
    
    // Output type option methods ----------------------------------------------
    
    /**
     * Convert input to string.
     * 
     * By default, this simply casts the input to a string and outputs the
     * result. Achieve more nuanced transformations by calling the various
     * option methods.
     */
    public function toString() {
        $this->outputType = Convert::OUTPUT_STRING;
        return $this;
    }
    
    // Interpretation option methods -------------------------------------------
    
    /**
     * Only applies when converting to string.
     * 
     * Output "truthiness" of input (based on PHP's rules) as 't' or 'f'.
     */
    public function boolean() {
        $this->ensureOutputType(Convert::OUTPUT_STRING);
        $this->interpreter = Convert::INTERPET_BOOLEAN;
        return $this;
    }
    
    /**
     * No-op, fluent function.
     */
    public function interpreting() {
        return $this;
    }
    
    private function ensureOutputType($outputType) {
        if ($this->outputType !== $outputType) {
            $error = 'Option method expected output type "'.$outputType.'", but ';
            $error .= is_null($this->outputType) ? 'output type has not been specified.' : 'output type is "'.$this->outputType.'"';
            throw new TuduException($error);
        }
    }
    
    // Processing methods ------------------------------------------------------
    
    static protected $dispatchTable = [
        Convert::INTERPET_RAW => 'interpretUntyped',
        Convert::INTERPET_BOOLEAN => 'interpretBoolean',
        Convert::OUTPUT_STRING => 'processToString'
    ];
    
    protected function interpretUntyped($data) {
        return (string)$data;
    }
    
    protected function interpretBoolean($data) {
        return $data ? 't' : 'f';
    }
    
    protected function processToString($data) {
        return $this->dispatch($this->interpreter, $data);
    }
    
    protected function process($data) {
        if (is_null($this->outputType)) {
            throw new TuduException('Convert::execute called without specifying output type.');
        }
        
        return $this->dispatch($this->outputType, $data);
    }
}
?>
