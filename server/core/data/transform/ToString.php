<?php
namespace Tudu\Core\Data\Transform;

/**
 * Chainable to-string transformer.
 * 
 * By default, this transformer simply casts the input to a string and outputs
 * the result. Achieve more nuanced transformations by calling the various
 * option methods.
 */
final class ToString extends Transform {
    
    protected $interpreter;
    
    const INTERPET_UNTYPED = 1;
    const INTERPET_BOOLEAN = 2;
    
    public function __construct() {
        parent::__construct();
        $this->interpreter = ToString::INTERPET_UNTYPED;
    }
    
    // Option methods ----------------------------------------------------------
    
    /**
     * No-op, fluent function.
     */
    public function interpreting() {
        return $this;
    }
    
    /**
     * Output "truthiness" of input (based on PHP's rules) as 't' or 'f'.
     */
    public function boolean() {
        $this->interpreter = ToString::INTERPET_BOOLEAN;
        return $this;
    }
    
    // Processing methods ------------------------------------------------------
    
    static protected $dispatchTable = [
        ToString::INTERPET_UNTYPED => 'interpretUntyped',
        ToString::INTERPET_BOOLEAN => 'interpretBoolean'
    ];
    
    protected function interpretUntyped($data) {
        return (string)$data;
    }
    
    protected function interpretBoolean($data) {
        return $data ? 't' : 'f';
    }
    
    protected function process($data) {
        return $this->dispatch($this->interpreter, $data);
    }
}
?>
