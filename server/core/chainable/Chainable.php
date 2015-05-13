<?php
namespace Tudu\Core\Chainable;

use \Tudu\Core\Chainable\Sentinel;

/**
 * Chainable is a base class for function-like objects. These objects have only
 * one purpose: take some input X and produce some output Y. You compose larger
 * functions by chaining multiple instances of such objects together using
 * Chainable::then().
 */
abstract class Chainable {
    
    protected $next;
    protected $prev;
    
    /**
     * Constructor. You must call this from subclass constructors.
     */
    public function __construct() {
        $this->next = null;
        $this->prev = null;
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
     * If input data is a sentinel, the data is passed to to processSentinel().
     * Otherwise, data is passed to process(). The result is then passed to the
     * next object in the chain.
     * 
     * @param mixed $data Input data.
     * @return mixed Output data.
     */
    final protected function preprocess($data) {
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
    final protected function pass($data) {
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
}
?>
