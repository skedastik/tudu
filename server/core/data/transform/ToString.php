<?php
namespace Tudu\Core\Data\Transform;

/**
 * To-string transformer.
 * 
 * By default, this transformer simply casts the input to a string and returns
 * the result. Achieve more nuanced transformations by calling the various
 * option methods.
 */
final class ToString extends Transform {
    
    protected $options;
    static protected $dispatchTable = [
        'untyped' => 'interpretUntyped',
        'boolean' => 'interpretBoolean'
    ];
    
    public function __construct() {
        $this->options = [
            'interpreter' => 'untyped'
        ];
    }
    
    /**
     * No-op, fluent function.
     */
    public function interpret() {
        return $this;
    }
    
    /**
     * Output "truthiness" of input (based on PHP's rules) as 't' or 'f'.
     */
    public function boolean() {
        $this->options['interpreter'] = 'boolean';
        return $this;
    }
    
    private function interpretUntyped($data) {
        return (string)$data;
    }
    
    private function interpretBoolean($data) {
        return $data ? 't' : 'f';
    }
    
    protected function process($data) {
        return $this->{self::$dispatchTable[$this->options['interpreter']]}($data);
    }
}
?>
