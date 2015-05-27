<?php
namespace Tudu\Core\Handler;

use \Tudu\Core\Data\DbConnection;
use \Tudu\Core\Delegate;

/**
 * Request handler base class. A Handler is somewhat analogous to a unit of
 * middleware with a single responsibility (authentication, business processing,
 * etc.).
 */
abstract class Handler {
    
    protected $app;
    protected $db;
    
    /**
     * Constructor.
     * 
     * @param \Tudu\Core\Delegate\App $app Instance of an app delegate.
     * @param \Tudu\Core\Data\DbConnection $db Database connection instance.
     */
    public function __construct(Delegate\App $app, DbConnection $db) {
        $this->app = $app;
        $this->db = $db;
    }
    
    /**
     * Handle the request.
     */
    abstract public function process();
}
    
?>
