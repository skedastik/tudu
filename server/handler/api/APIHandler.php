<?php
namespace Tudu\Handler\Api;

require_once __DIR__.'/../../core/AuthHandler.php';

/**
 * Request handler base class for all API endpoints
 */
abstract class APIHandler extends \Tudu\Core\AuthHandler {
    
    protected function rejectAuthentication() {
        $this->delegate->setResponseHeaders(['WWW-Authenticate' => 'tudu realm="api"']);
        $this->delegate->setResponseStatus(401);
        $this->delegate->send();
    }
}

?>
