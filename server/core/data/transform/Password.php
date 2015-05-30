<?php
namespace Tudu\Core\Data\Transform;

use \Tudu\Core;

/**
 * Transform a plain text password into a secure hash string.
 */
final class Password extends Transformer {
    
    // Option methods ----------------------------------------------------------
    
    /**
     * Supply an instance of a Password delegate subclass to compute the
     * password hash.
     * 
     * @param \Tudu\Core\Delegate\Password $instance An instance of a Password
     * delegate subclass.
     */
    public function with(Core\Delegate\Password $delegate = null) {
        $this->setOption('delegate', $delegate);
        return $this;
    }
    
    // Processing methods ------------------------------------------------------
    
    protected function process($data) {
        if (!is_string($data)) {
            throw new Core\Exception('Non-string input passed to Transform\Password.');
        }
        $delegate = $this->getOption('delegate');
        if (is_null($delegate)) {
            throw new Core\Exception('No password delegate instance has been supplied to Transform\Password');
        }
        return $delegate->getHash($data);
    }
}
?>
