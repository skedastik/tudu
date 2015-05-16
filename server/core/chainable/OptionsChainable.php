<?php
namespace Tudu\Core\Chainable;

use \Tudu\Core\TuduException;

/**
 * Chainable base class with selectable options.
 * 
 * To enable clients to tune the behavior of Chainable object, extend
 * OptionsChainable.
 */
abstract class OptionsChainable extends Chainable {
    
    // options which are "selected" (i.e., turned "on")
    private $selectedOptions;
    
    // options which have values (i.e., [color => 'red'])
    private $valueOptions;
    
    /**
     * When extending OptionsChainable, you can define a static $functionMap as
     * a convenient way of mapping option strings to methods. See
     * OptionsChainable::dispatch().
     */
    static protected $functionMap = [];
    
    /**
     * Constructor. You must call this from subclass constructors.
     */
    public function __construct() {
        parent::__construct();
        $this->selectedOptions = [];
        $this->valueOptions = [];
    }
    
    /**
     * Add a selected option.
     * 
     * Use this method if your subclass allows various options to be selected at
     * the same time (i.e., they are not mutually exclusive). Calling addOption
     * again with the same option identifier has no effect.
     * 
     * @param string|int $option An option identifier.
     */
    final protected function addOption($option) {
        $this->selectedOptions[$option] = 1;
    }
    
    /**
     * Set the selected option, or set a value option.
     * 
     * Use this method to set value options.
     * 
     * Additionally, use this method if your subclass' options are mutually
     * exclusive (i.e., only a single option can be selected).
     * 
     * @param string|int $option An option identifier.
     * @param mixed $value (optional) Option value. Must not be NULL.
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
     * Apply selected option(s).
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
    final protected function apply($data) {
        if (empty($this->selectedOptions)) {
            throw new TuduException('OptionsChainable::apply() called, but no options have been selected.');
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
    final protected function dispatch($string, ...$args) {
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
