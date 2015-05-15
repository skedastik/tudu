<?php
namespace Tudu\Core\Data\Transform;

use Tudu\Core\TuduException;

/**
 * Chainable string transformer.
 * 
 * Expects a string as input. Applies optional transforms before outputting.
 * Throws an exception if input is not a string.
 */
final class String extends Transform {
    
    protected $transforms;
    
    // string transforms
    const ESCAPE_HTML = 'escape_html';
    const STRIP_TAGS = 'string_tags';
    
    public function __construct() {
        parent::__construct();
        $this->transforms = [];
    }
    
    // Option methods ----------------------------------------------------------
    
    /**
     * Escape special characters for rendering as HTML.
     */
    public function escapeForHTML() {
        $this->transforms[String::ESCAPE_HTML] = 1;
        return $this;
    }
    
    /**
     * Strip HTML tags.
     */
    public function stripTags() {
        $this->transforms[String::STRIP_TAGS] = 1;
        return $this;
    }
    
    // Processing methods ------------------------------------------------------
    
    static protected $dispatchTable = [
        String::ESCAPE_HTML => 'processEscapeHTML',
        String::STRIP_TAGS => 'processStripTags'
    ];
    
    protected function processEscapeHTML($data) {
        return htmlspecialchars($data);
    }
    
    protected function processStripTags($data) {
        return strip_tags($data);
    }
    
    protected function process($data) {
        if (!is_string($data)) {
            throw new TuduException('Non-string input provided to Transform\String.');
        }
        
        foreach (array_keys($this->transforms) as $transform) {
            $data = $this->dispatch($transform, $data);
        }
        
        return $data;
    }
}
?>
