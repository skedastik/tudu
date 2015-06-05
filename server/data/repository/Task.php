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
        $result = $this->db->query(
            'select tudu.create_task($1, $2, $3, $4)',
            [
                $task->get(TaskModel::USER_ID),
                $task->get(TaskModel::DESCRIPTION),
                $task->get(TaskModel::TAGS),
                $ip
            ]
        );
    }
}
?>
