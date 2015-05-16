<?php
namespace Tudu\Core\Data\Transform;

/**
 * Chainable data conversion transformer.
 */
final class Convert extends Transform {
    
    // output types
    const OPT_OUTPUT_STRING = 'string';
    const OPT_OUTPUT_BOOL_STRING = 'boolean_string';
    const OPT_OUTPUT_INTEGER = 'integer';
    const OPT_OUTPUT_FLOAT = 'float';
    
    // Option methods ----------------------------------------------------------
    
    /**
     * Convert input to string.
     * 
     * This simply casts the input to a string and outputs the result.
     */
    public function toString() {
        $this->setOption(self::OPT_OUTPUT_STRING);
        return $this;
    }
    
    /**
     * Convert input to string.
     * 
     * Output "truthiness" of input (based on PHP's rules) as 't' or 'f'.
     */
    public function toBooleanString() {
        $this->setOption(self::OPT_OUTPUT_BOOL_STRING);
        return $this;
    }
    
    /**
     * Convert input to integer.
     */
    public function toInteger() {
        $this->setOption(self::OPT_OUTPUT_INTEGER);
        return $this;
    }
    
    /**
     * Convert input to float.
     */
    public function toFloat() {
        $this->setOption(self::OPT_OUTPUT_FLOAT);
        return $this;
    }
    
    // Processing methods ------------------------------------------------------
    
    static protected $functionMap = [
        self::OPT_OUTPUT_STRING => 'processToString',
        self::OPT_OUTPUT_BOOL_STRING => 'processToBooleanString',
        self::OPT_OUTPUT_INTEGER => 'processToInteger',
        self::OPT_OUTPUT_FLOAT => 'processToFloat'
    ];
    
    protected function processToString($data) {
        return (string)$data;
    }
    
    protected function processToBooleanString($data) {
        return $data ? 't' : 'f';
    }
    
    protected function processToInteger($data) {
        return intval($data);
    }
    
    protected function processToFloat($data) {
        return floatval($data);
    }
    
    protected function process($data) {
        return $this->apply($data);
    }
}
?>
