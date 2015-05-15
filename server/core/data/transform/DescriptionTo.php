<?php
namespace Tudu\Core\Data\Transform;

use \Tudu\Core\Chainable\Sentinel;

/**
 * Chainable transformer for generating human-readable validation error strings.
 * 
 * DescriptionTo generates error string outputs from Sentinel inputs. If the
 * input is not a Sentinel object, DescriptionTo simply outputs the input.
 */
final class DescriptionTo extends Transform {
    
    protected $description;
    
    /**
     * Constructor.
     * 
     * Provide a custom description for generating human-readable validation
     * error strings.
     * 
     * You should emulate the following examples:
     * 
     *    "Email address"
     *    "Oops! It looks like your email address"
     *    "This email address"
     *    
     * Note the capitalization, as the description will appear at the beginning
     * of a sentence. Also, the last part of the description should always be a
     * noun, as the description will be followed by a verb.
     * 
     * @param string $description A description describing the data.
     */
    public function __construct($description) {
        parent::__construct();
        $this->description = $description;
    }
    
    /**
     * Sentinel encountered. Extract the error string from it, and return a
     * human-readable error string wrapped in a new sentinel.
     * 
     * @param \Tudu\Core\Chainable\Sentinel $sentinel Input sentinel.
     * @return \Tudu\Core\Chainable\Sentinel Output sentinel.
     */
    final protected function processSentinel(Sentinel $sentinel) {
        return new Sentinel($this->description.' '.$sentinel->getValue().'.');
    }
}
?>
