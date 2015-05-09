<?php
namespace Tudu\Core\Handler;

require_once __DIR__.'/../data/DbConnection.php';

use \Tudu\Core\Data\DbConnection;
use \Tudu\Core\Delegate;

/**
 * Request handler base class. A Handler is analogous to a unit of middleware
 * with a single responsibility (authentication, business processing, etc.).
 */
abstract class Handler {
    
    protected $delegate;
    protected $db;
    protected $context;
    
    /**
     * Constructor.
     * 
     * @param \Tudu\Core\Delegate\App $delegate Instance of an app delegate
     * implementation.
     * @param \Tudu\Core\Data\DbConnection $db Database connection instance.
     * @param array $context (optional) Associative array describing the context
     * of this request (route parameters, query parameters, etc.).
     */
    public function __construct(Delegate\App $delegate, DbConnection $db, array $context = []) {
        $this->delegate = $delegate;
        $this->db = $db;
        $this->context = $context;
    }
    
    /**
     * Handle the request.
     */
    abstract public function process();
}
    
?>
