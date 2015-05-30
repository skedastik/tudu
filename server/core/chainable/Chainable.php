<?php
namespace Tudu\Core\Chainable;

use \Tudu\Core\Exception;

/**
 * Chainable is a base class for function-like objects. These objects have only
 * one purpose: take some input X and produce some output Y. You compose larger
 * functions by chaining multiple instances together using Chainable::then().
 * 
 * To enable clients to tune the behavior of Chainable objects, use options.
 */
abstract class Chainable {
    
    protected $next;
    protected $prev;
    
    // options which are "selected" (i.e., turned "on")
    private $selectedOptions;
    
    // options which have values (i.e., [color => 'red'])
    private $valueOptions;
    
    /**
     * When extending Chainable, override this to map selectable option strings
     * to methods.
     */
    static protected $functionMap = [];
    
    /**
     * Constructor.
     */
    public function __construct() {
        $this->next = null;
        $this->prev = null;
        $this->selectedOptions = [];
        $this->valueOptions = [];
    }
    
    /**
     * Initiate processing.
     * 
     * @param mixed $data Input data.
     * @return mixed Output data.
     */
    final public function execute($data) {
        $first = $this;
        while ($first->prev) {
            $first = $first->prev;
        }
        return $first->preprocess($data);
    }
    
    /**
     * Preprocessing step.
     * 
     * If input data is a sentinel, the data is passed to processSentinel().
     * Otherwise, data is passed to process(). The result is then passed to the
     * next object in the chain.
     * 
     * @param mixed $data Input data.
     * @return mixed Output data.
     */
    final private function preprocess($data) {
        if ($data instanceof Sentinel) {
            $result = $this->processSentinel($data);
        } else {
            $result = $this->process($data);
        }
        return $this->pass($result);
    }
    
    /**
     * Process a sentinel input, producing an output.
     * 
     * Default behavior is to simply return the sentinel as is. Override for
     * custom behavior.
     * 
     * @param \Tudu\Core\Chainable\Sentinel $data Input sentinel.
     * @return mixed Output data.
     */
    protected function processSentinel(Sentinel $sentinel) {
        return $sentinel;
    }
    
    /**
     * Process input data, producing an output.
     * 
     * Default behavior is to simply return the input as is. Override this for
     * custom behavior.
     * 
     * @param mixed $data Input data.
     * @return mixed Output data.
     */
    protected function process($data) {
        return $data;
    }
    
    /**
     * Pass data to the next object in the chain.
     * 
     * If this is the last object in the chain, the data is returned as is.
     * 
     * @param mixed $data Input data.
     * @return mixed Output data.
     */
    final private function pass($data) {
        if (is_null($this->next)) {
            return $data;
        }
        return $this->next->preprocess($data);
    }
    
    /**
     * Chain another Chainable.
     * 
     * @param \Tudu\Core\Chainable $chainable Chainable instance.
     * @return \Tudu\Core\Chainable The next object in the chain.
     */
    final public function then(Chainable $chainable) {
        $this->next = $chainable;
        $chainable->setPrev($this);
        return $chainable;
    }
    
    /**
     * Used internally to set the previous object in the chain. Do not call
     * directly.
     * 
     * @param \Tudu\Core\Chainable $chainable Chainable instance.
     */
    final public function setPrev(Chainable $chainable) {
        $this->prev = $chainable;
    }
    
    /**
     * Add a selected option.
     * 
     * Use this method if your subclass allows multiple selectable options to be
     * selected at the same time. In other words, use this method when
     * selectable options are not mutually exclusive.
     * 
     * @param string|int $option An option identifier.
     */
    final protected function addOption($option) {
        $this->selectedOptions[$option] = 1;
    }
    
    /**
     * Set a value options, or set the selectable option.
     * 
     * Use this method for mutually exclusive selectable options.
     * 
     * @param string|int $option An option identifier.
     * @param mixed $value (optional) Option value. Defaults to NULL indicating
     * that this is not a value option. Pass a non-NULL value to set a value
     * option.
     */
    final protected function setOption($option, $value = null) {
        if (is_null($value)) {
            $this->selectedOptions = [ $option => 1 ];
        } else {
            $this->valueOptions[$option] = $value;
        }
    }
    
    /**
     * Get the value of a value option.
     * 
     * @return mixed The value of the option, or NULL if the option was not set.
     */
    final protected function getOption($option) {
        return isset($this->valueOptions[$option]) ? $this->valueOptions[$option] : null;
    }
    
    /**
     * Apply selectable option(s).
     * 
     * If multiple options were selected, they are applied in the order they
     * were selected.
     * 
     * This method invokes the methods mapped to the selected option strings by
     * a subclass' function map. The input data is sequentially passed to, and
     * transformed by each function. The final result is returned.
     * 
     * If any of the mapped methods returns a Sentinel object, subsequent
     * methods will NOT be invoked and the Sentinel object will be returned
     * immediately.
     * 
     * @param mixed $data Input data.
     * @return mixed Result of applying selected option methods to the input
     * data.
     */
    final protected function applyOptions($data) {
        if (empty($this->selectedOptions)) {
            throw new Exception\Internal('Chainable::applyOptions() called, but no options have been selected.');
        }
        foreach (array_keys($this->selectedOptions) as $option) {
            $data = $this->dispatch($option, $data);
            if ($data instanceof Sentinel) {
                return $data;
            }
        }
        return $data;
    }
    
    /**
     * Dispatch a method call mapped to the given string.
     * 
     * This method uses late static binding to call the method mapped to the
     * input string by a subclass' function map.
     * 
     * @param string $string The input string.
     * @param mixed ...$args Arguments to pass to the mapped method.
     * @return mixed The return value of the mapped method.
     */
    final private function dispatch($string, ...$args) {
        return $this->{static::$functionMap[$string]}(...$args);
    }
    
    /**
     * No-op, fluent function.
     */
    public function with() {
        return $this;
    }
    
    /**
     * No-op, fluent function.
     */
    public function is() {
        return $this;
    }
    
    /**
     * No-op, fluent function.
     */
    public function to() {
        return $this;
    }
}
?>
