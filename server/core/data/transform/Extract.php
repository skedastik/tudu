<?php
namespace Tudu\Core\Data\Transform;

use \Tudu\Core\Exception;

/**
 * Chainable string data extraction.
 */
final class Extract extends Transformer {
    
    const OPT_HASHTAGS = 'hashtags';
    
    // Option methods ----------------------------------------------------------
    
    /**
     * Extract an array of unique hashtags from a string.
     * 
     * Hash tags will not include leading hash character (#). An empty array is
     * returned if no hashtags are found.
     */
    public function hashtags() {
        $this->setOption(self::OPT_HASHTAGS);
        return $this;
    }
    
    /**
     * No-op, fluent function.
     */
    public function asArray() {
        return $this;
    }
    
    // Processing methods ------------------------------------------------------
    
    static protected $functionMap = [
        self::OPT_HASHTAGS => 'processHashtags'
    ];
    
    protected function processHashtags($data) {
        if (preg_match_all('/#([\p{L}0-9&]+)/u', $data, $matches)) {
            return array_unique($matches[1]);
        }
        return [];
    }
    
    protected function process($data) {
        if (!is_string($data)) {
            throw new Exception\Internal('Non-string input passed to Extract transformer.');
        }
        return $this->applyOptions($data);
    }
}
?>
