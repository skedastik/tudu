<?php
namespace Tudu\Test\Integration\Database;

use \Tudu\Test\Integration\Database\DatabaseTest;
use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Data\Model\User;
use \Tudu\Data\Model\Task;
use \Tudu\Data\Repository;

/**
 * @group todo
 */
class TaskRepositoryTest extends DatabaseTest {
    
    protected $repo;
    
    public function setUp() {
        parent::setUp();
        $userRepo = new Repository\User($this->db);
        $user = new User([
            User::EMAIL => 'foo@example.com',
            User::PASSWORD => 'foopassword'
        ]);
        $this->userId = $userRepo->signupUser($user, '127.0.0.1');
        $this->repo = new Repository\Task($this->db);
    }
    
    public function testCreateTaskShouldSucceedGivenValidInputs() {
        $description = 'Test #description';
        $task = new Task([
            Task::USER_ID => $this->userId,
            Task::DESCRIPTION => $description,
            Task::TAGS => $description
        ]);
        $taskId = $this->repo->createTask($task, '127.0.0.1');
        $this->assertTrue($taskId >= 0);
    }
    
    public function testCreateTaskShouldSucceedGivenAnAbsenceOfTags() {
        $description = 'Tagless description';
        $task = new Task([
            Task::USER_ID => $this->userId,
            Task::DESCRIPTION => $description,
            Task::TAGS => $description
        ]);
        $taskId = $this->repo->createTask($task, '127.0.0.1');
        $this->assertTrue($taskId >= 0);
    }
    
    public function testCreateTaskShouldFailGivenInvalidUserId() {
        $description = 'Test #description';
        $task = new Task([
            Task::USER_ID => -1,
            Task::DESCRIPTION => $description,
            Task::TAGS => $description
        ]);
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $taskId = $this->repo->createTask($task, '127.0.0.1');
    }
    
    public function testFetchShouldSucceedGivenValidID() {
        $description = 'Test #description';
        $task = new Task([
            Task::USER_ID => $this->userId,
            Task::DESCRIPTION => $description,
            Task::TAGS => $description
        ]);
        $taskId = $this->repo->createTask($task, '127.0.0.1');
        $fetchedTask = $this->repo->fetch(new Task([
            Task::TASK_ID => $taskId,
        ]));
        $this->assertTrue($fetchedTask instanceof Task);
    }
    
    public function testFetchShouldFailGivenInvalidID() {
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->repo->fetch(new Task([
            Task::TASK_ID => -1,
        ]));
    }
}
?>
