<?php
namespace Tudu\Handler\Api\Task;

use \Tudu\Data\Model\Task;
use \Tudu\Data\Repository;

/**
 * Request handler for /users/:user_id/tasks/
 */
final class Tasks extends Endpoint {
    
    protected function _getAllowedMethods() {
        return 'GET, POST';
    }
    
    protected function get() {
        echo 'Tasks->get()';
    }
    
    /**
     * POST to /users/:user_id/tasks/ to create a new task.
     */
    protected function post() {
        $this->negotiateContentType();
        $task = $this->importRequestData([
            Task::USER_ID,
            Task::DESCRIPTION
        ]);
        $repo = new Repository\Task($this->db);
        $taskId = $repo->createTask($task, $this->app->getRequestIp());
        $this->app->setResponseStatus(201);
        $this->app->setResponseHeaders([
            'Location' => $this->app->getFullRequestUrl().$taskId
        ]);
        $this->renderBody([
            Task::TASK_ID => $taskId
        ]);
    }
}

?>
