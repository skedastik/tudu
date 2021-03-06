<?php
namespace Tudu\Core\Data\Transform;

use \Tudu\Core\Exception;

/**
 * Chainable string transformer.
 * 
 * Expects a string as input. Applies optional transforms before outputting.
 * Throws an exception if input is not a string.
 */
final class String extends Transformer {
    
    // string transforms
    const OPT_ESCAPE_HTML     = 'escape_html';
    const OPT_STRIP_TAGS      = 'string_tags';
    const OPT_TRIM_WHITESPACE = 'trim_whitespace';
    
    // Option methods ----------------------------------------------------------
    
    /**
     * Escape special characters for rendering as HTML.
     */
    public function escapeForHTML() {
        $this->addOption(self::OPT_ESCAPE_HTML);
        return $this;
    }
    
    /**
     * Strip HTML tags.
     */
    public function stripTags() {
        $this->addOption(self::OPT_STRIP_TAGS);
        return $this;
    }
    
    /**
     * Trim whitepsace both before and after input string. Whitespace includes
     * spaces, tabs, and newline characters.
     */
    public function trim() {
        $this->addOption(self::OPT_TRIM_WHITESPACE);
        return $this;
    }
    
    /**
     * No-op, fluent function.
     */
    public function length() {
        return $this;
    }
    
    // Processing methods ------------------------------------------------------
    
    static protected $functionMap = [
        self::OPT_ESCAPE_HTML => 'processEscapeHTML',
        self::OPT_STRIP_TAGS => 'processStripTags',
        self::OPT_TRIM_WHITESPACE => 'processTrim'
    ];
    
    protected function processEscapeHTML($data) {
        return htmlspecialchars($data);
    }
    
    protected function processStripTags($data) {
        return strip_tags($data);
    }
    
    protected function processTrim($data) {
        return preg_replace('/^\s*(.+?)\s*$/s', '$1', $data);
    }
    
    protected function process($data) {
        if (!is_string($data)) {
            throw new Exception\Internal('Non-string input passed to Transform\String.');
        }
        return $this->applyOptions($data);
    }
}
?>
