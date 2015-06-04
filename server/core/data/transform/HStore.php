<?php
namespace Tudu\Core\Data\Transform;

use \Tudu\Core\Exception;

/**
 * Transform a string representation of a PostgreSQL HSTORE to a key/value
 * array.
 * 
 * The transform may fail silently if the input string is malformed.
 */
final class HStore extends Transformer {
    
    // Option methods ----------------------------------------------------------
    
    /**
     * No-op, fluent function.
     */
    public function keyValueArray() {
        return $this;
    }
    
    // Processing methods ------------------------------------------------------
    
    protected function process($data) {
        if (!is_string($data)) {
            throw new Exception\Internal('Non-string input passed to Transform\HStore.');
        }
        
        if (empty($data)) {
            return [];
        }
        
        $kvStrings = explode(', ', $data);
        $kvPairs = array_map(
            function ($kvString) {
                $kvPair = explode('=>', $kvString);
                return array_map(
                    function ($string) {
                        // TODO: Explore un-escaping of double quotes.
                        if ($string[0] == '"') {
                            return mb_substr($string, 1, mb_strlen($string) - 2);
                        }
                        return null;
                    },
                    $kvPair
                );
            },
            $kvStrings
        );
        
        return array_combine(
            array_column($kvPairs, 0),
            array_column($kvPairs, 1)
        );
    }
}
?>
