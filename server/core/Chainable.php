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
     * Process input data, producing an output. Note that the visibility of this
     * method is "protected". When extending Chainable, expose a public method
     * with a more descriptive name like "add", or "validate" and call process()
     * from within that method.
     * 
     * Default behavior is to simply pass processing to the next object in the
     * chain.
     * 
     * @param mixed $data Input data.
     * @return mixed Output data.
     */
    protected function process($data) {
        return $this->pass($data);
    }
    
    /**
     * Pass processing to the next object in the chain.
     * 
     * When extending Chainable, you may break the chain at any point by simply
     * not calling Chainable::pass() from within Chainable::process().
     * 
     * @param mixed $data Input data.
     * @return mixed Output data.
     */
    final protected function pass($data) {
        if (is_null($this->next)) {
            return NULL;
        }
        return $this->next->process($data);
    }
    
    /**
     * Add another object to the end of the chain.
     * 
     * @param \Tudu\Core\Chainable $chainable Another chainable object.
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
