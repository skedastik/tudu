<?php
namespace Tudu\Test\Integration\Api;

use \Tudu\Test\Mock\MockApp;
use \Tudu\Data\Repository;
use \Tudu\Data\Model\User;
use \Tudu\Data\Model\Task;
use \Tudu\Core\Encoder;
use \Tudu\Handler;
use \Tudu\Core\Data\Transform\Transform;

class TaskEndpointTest extends EndpointTest {
    
    private $app;
    private $userId;
    
    public function setUp() {
        parent::setUp();
        ob_start();
        $this->app = new MockApp();
        $this->app->addEncoder(new Encoder\JSON());
        $user = new User([
            User::EMAIL => 'foo@example.com',
            User::PASSWORD => 'mypassword'
        ]);
        $userRepo = new Repository\User($this->db);
        $this->userId = $userRepo->signupUser($user, '127.0.0.1', true);
        $this->app->setHandler(
            new Handler\Api\Task\Tasks($this->app, $this->db)
        );
        $this->taskRepo = new Repository\Task($this->db);
    }
    
    /**
     * @group todo
     */
    public function testValidPostShouldReturn201AndReturnAppropriateData() {
        // simulate a valid POST to /users/:user_id/tasks/
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setContext([
            Task::USER_ID => $this->userId
        ]);
        $description = 'Hello #world, I am #alive.';
        $this->app->setRequestBody('{
            "'.Task::DESCRIPTION.'": "'.$description.'"
        }');
        $this->app->run();
        
        $this->assertEquals(201, $this->app->getResponseStatus());
        
        // extract task ID from response body
        $taskId = $this->decodeOutputBuffer()[Task::TASK_ID];
        
        // task should have matching data
        $task = $this->taskRepo->fetch(new Task([
            Task::TASK_ID => $taskId
        ]));
        $this->assertSame($description, $task->get(Task::DESCRIPTION));
        
        $expectedTask = new Task([
            Task::TAGS => $description
        ]);
        $expectedTask->normalize();
        $this->assertSame($expectedTask->get(Task::TAGS), $task->get(Task::TAGS));
    }
    
    public function tearDown() {
        ob_end_clean();
        parent::tearDown();
    }
}
?>
