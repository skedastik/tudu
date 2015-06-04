<?php
namespace Tudu\Core\Chainable;

use \Tudu\Core\Exception;

/**
 * Chainable multiplexer.
 * 
 * Execute Chainables across an array of input values.
 */
final class ForEvery extends Chainable {
    
    private $chainable;
    
    /**
     * Shorthand factory function for this class.
     * 
     * @param Tudu\Core\Chainable\Chainable $chainable The chainable to execute
     * on each element of input data. There is no need to call `done()` on the
     * input Chainable. This is performed automatically.
     */
    public static function Element(Chainable $chainable) {
        return new ForEvery($chainable);
    }
    
    /**
     * Constructor.
     * 
     * @param Tudu\Core\Chainable\Chainable $chainable The chainable to execute
     * on each element of input data. There is no need to call `done()` on the
     * input Chainable. This is performed automatically.
     */
    public function __construct(Chainable $chainable) {
        parent::__construct();
        $this->chainable = $chainable->done();
    }
    
    protected function process($data) {
        if (!is_array($data)) {
            throw new Exception\Internal('Non-array input passed to Chainable\ForEvery.');
        }
        foreach ($data as $idx => $input) {
            $result = $this->chainable->execute($input);
            if ($result instanceof Sentinel) {
                return $result;
            }
            $data[$idx] = $result;
        }
        return $data;
    }
    
    /**
     * No-op, fluent function.
     */
    public function asArray() {
        return $this;
    }
}
?>
