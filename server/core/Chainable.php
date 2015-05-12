<?php
namespace Tudu\Core;

/**
 * Chainable is a base class for function-like objects. These objects have only
 * one purpose: take some input X and produce some output Y. You compose larger
 * functions by chaining multiple instances of such objects together using
 * Chainable::then().
 */
abstract class Chainable {
    
    protected $next;
    protected $last;
    
    /**
     * Constructor. You must call this from subclass constructors.
     */
    public function __construct() {
        $this->next = null;
        $this->last = $this;
    }
    
    /**
     * Initiate processing of the chain. Override this to perform extra
     * processing.
     */
    public function execute($data) {
        return $this->process($data);
    }
    
    /**
     * Process input data, producing an output.
     * 
     * Call Chainable::pass() to pass data to the next object in the chain. To
     * break the chain at any point, simply do not call Chainable::pass().
     * 
     * @param mixed $data Input data.
     * @return mixed Output data.
     */
    abstract protected function process($data);
    
    /**
     * Pass data to the next object in the chain.
     * 
     * If this is the last object in the chain, the result of passing data to
     * Chainable::finalize() is returned.
     * 
     * @param mixed $data Input data.
     * @return mixed Output data.
     */
    final protected function pass($data) {
        if (is_null($this->next)) {
            return $this->finalize($data);
        }
        return $this->next->process($data);
    }
    
    /**
     * Finalize output of the object chain.
     * 
     * Override this to perform some extra processing at the end of the chain.
     * 
     * @param mixed $data Input data.
     * @return mixed Output data.
     */
    protected function finalize($data) {
        return $data;
    }
    
    /**
     * Add another object to the end of the chain.
     * 
     * @param \Tudu\Core\Chainable $chainable Chainable instance.
     */
    final public function then(Chainable $chainable) {
        $this->last->setNext($chainable);
        $this->last = $chainable;
        return $this;
    }
    
    /**
     * Set next object in chain. This is used internally by other Chainable
     * objects. Do not call directly.
     * 
     * @param \Tudu\Core\Chainable $chainable Another chainable object.
     */
    final public function setNext(Chainable $chainable) {
        $this->next = $chainable;
    }
}
?>
