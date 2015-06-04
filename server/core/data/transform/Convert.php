<?php
namespace Tudu\Core\Data\Transform;

use \Tudu\Core\Exception;

/**
 * Chainable data conversion transformer.
 */
final class Convert extends Transformer {
    
    // output types
    const OPT_OUTPUT_STRING = 'string';
    const OPT_OUTPUT_BOOL_STRING = 'boolean_string';
    const OPT_OUTPUT_PG_SQL_ARRAY = 'pg_sql_array_string';
    const OPT_OUTPUT_INTEGER = 'integer';
    const OPT_OUTPUT_FLOAT = 'float';
    
    // Option methods ----------------------------------------------------------
    
    /**
     * Convert input to string.
     * 
     * This simply casts the input to a string and outputs the result.
     */
    public function string() {
        $this->setOption(self::OPT_OUTPUT_STRING);
        return $this;
    }
    
    /**
     * Convert input to boolean string.
     * 
     * Output "truthiness" of input (based on PHP's rules) as 't' or 'f'.
     */
    public function booleanString() {
        $this->setOption(self::OPT_OUTPUT_BOOL_STRING);
        return $this;
    }
    
    /**
     * Convert input to a PostgreSQL array string.
     * 
     * Input must be a PHP array.
     */
    public function pgSqlArray() {
        $this->setOption(self::OPT_OUTPUT_PG_SQL_ARRAY);
        return $this;
    }
    
    /**
     * Convert input to integer.
     */
    public function integer() {
        $this->setOption(self::OPT_OUTPUT_INTEGER);
        return $this;
    }
    
    /**
     * Convert input to float.
     */
    public function float() {
        $this->setOption(self::OPT_OUTPUT_FLOAT);
        return $this;
    }
    
    // Processing methods ------------------------------------------------------
    
    static protected $functionMap = [
        self::OPT_OUTPUT_STRING => 'processToString',
        self::OPT_OUTPUT_BOOL_STRING => 'processToBooleanString',
        self::OPT_OUTPUT_PG_SQL_ARRAY => 'processToPgSqlArray',
        self::OPT_OUTPUT_INTEGER => 'processToInteger',
        self::OPT_OUTPUT_FLOAT => 'processToFloat'
    ];
    
    protected function processToString($data) {
        return (string)$data;
    }
    
    protected function processToBooleanString($data) {
        return $data ? 't' : 'f';
    }
    
    protected function processToPgSqlArray($data) {
        if (!is_array($data)) {
            throw new Exception\Internal('Non-array input passed to Transform\Convert with PostgreSQL array string output option selected.');
        }
        return self::arrayToPgSqlArray($data);
    }
    
    protected function processToInteger($data) {
        return intval($data);
    }
    
    protected function processToFloat($data) {
        return floatval($data);
    }
    
    protected function process($data) {
        return $this->applyOptions($data);
    }
    
    /**
     * Convert an array to a PostgreSQL array.
     * 
     * @param array $array
     * @return string
     */
    private function arrayToPgSqlArray(array $array) {
        foreach ($array as &$element) {
            if (is_array($element)) {
                $element = self::arrayToPgSqlArray($element);
            } else if (is_null($element)) {
                $element = 'null';
            } else if (!is_numeric($element)) {
                $element = '"'.str_replace('"', '\\"', $element).'"';
            }
        }
        return '{'.implode(', ', $array).'}';
    }
}
?>
