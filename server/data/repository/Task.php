<?php
namespace Tudu\Data\Repository;

use \Tudu\Core\Data\Repository;
use \Tudu\Core\Exception;
use \Tudu\Data\Model\Task as TaskModel;
use \Tudu\Core\Data\Model;

final class Task extends Repository {
    
    /**
     * Fetch a single task with matching ID.
     * 
     * @param \Tudu\Core\Data\Model $task Task model to match against (task ID
     * required).
     * @return \Tudu\Core\Data\Model A normalized model populated with data.
     */
    public function fetch(Model $task) {
        $this->normalize($task);
        $result = $this->db->query(
            'select task_id, user_id, description, tags, finished_date, kvs, status, edate, cdate from tudu_task where task_id = $1;',
            [$task->get(TaskModel::TASK_ID)]
        );
        if ($result === false) {
            throw new Exception\Client('Task not found.');
        }
        return new TaskModel($result[0], true);
    }
    
    /**
     * Create a new task for an existing user.
     * 
     * @param \Tudu\Core\Data\Model $task Task model to export (user ID and
     * description required). Tags are automatically extracted from the
     * description.
     * @param string $ip IP address.
     * @return \Tudu\Core\Data\Model A normalized model populated with data.
     */
    public function createTask(Model $task, $ip) {
        $task->set(TaskModel::TAGS, $task->get(TaskModel::DESCRIPTION));
        $this->normalize($task);
        $result = $this->db->queryValue(
            'select tudu.create_task($1, $2, $3, $4)',
            [
                $task->get(TaskModel::USER_ID),
                $task->get(TaskModel::DESCRIPTION),
                $task->get(TaskModel::TAGS),
                $ip
            ]
        );
        if ($result == -1) {
            throw new Exception\Client('User ID not found.', null, 404);
        }
        return $result;
    }
    
    /**
     * Update existing task.
     * 
     * @param \Tudu\Core\Data\Model $task Task model to export (task ID
     * required, description optional). Tags are automatically extracted from
     * the description if provided.
     * @param string $ip IP address.
     * @return int Task ID.
     */
    public function updateTask(Model $task, $ip) {
        if ($task->hasProperty(TaskModel::DESCRIPTION)) {
            $task->set(TaskModel::TAGS, $task->get(TaskModel::DESCRIPTION));
        }
        $this->normalize($task);
        $result = $this->db->queryValue(
            'select tudu.update_task($1, $2, $3, $4)',
            [
                $task->get(TaskModel::TASK_ID),
                $task->get(TaskModel::DESCRIPTION),
                $task->get(TaskModel::TAGS),
                $ip
            ]
        );
        switch ($result) {
            case -1:
                throw new Exception\Client('Task ID not found.', null, 404);
            case -2:
                throw new Exception\Client('Task has been deleted.', null, 410);
            case -3:
                throw new Exception\Client('Task is not in an alterable state.', null, 409);
        }
        return $result;
    }
}
?>
