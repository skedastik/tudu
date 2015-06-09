<?php
namespace Tudu\Test\Integration\Api;

use \Tudu\Test\Mock\MockApp;
use \Tudu\Test\Integration\Database\DatabaseTest;
use \Tudu\Data\Repository;
use \Tudu\Data\Model\AccessToken;
use \Tudu\Data\Model\User;
use \Tudu\Core\Encoder;
use \Tudu\Core\Handler\Auth\Auth;
use \Tudu\Handler;
use \Tudu\Core\Data\Transform\Transform;

class TaskEndpointTest extends DatabaseTest {
    
    private $app;
    private $userId;
    
    /**
     * Decode JSON data from output buffer, returning an array.
     */
    private function decodeOutputBuffer() {
        return json_decode(ob_get_contents(), true);
    }
    
    public function setUp() {
        parent::setUp();
        ob_start();
        $this->app = new MockApp();
        $this->app->addEncoder(new Encoder\JSON());
        $user = new User([
            User::EMAIL => 'foo@example.com',
            User::PASSWORD => 'pw_hash'
        ]);
        $userRepo = new Repository\User($this->db);
        $this->userId = $this->repo->signupUser($user, '127.0.0.1', true);
        $this->app->setHandler(
            new Handler\Api\User\Tasks($this->app, $this->db)
        );
        $this->taskRepo = new Repository\Task($this->db);
    }
    
    public function testValidPostShouldReturn201AndReturnAppropriateData() {
        // simulate a valid POST to /users/:user_id/tasks/
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setContext([
            Task::USER_ID => $this->userId
        ]);
        $description = "Hello #world, I am #alive.";
        $this->app->setRequestBody('{
            "'.Task::DESCRIPTION.'": "'.$description.'",
        }');
        $this->app->run();
        
        // extract "task_id" property from response body
        $taskId = $this->decodeOutputBuffer()[Task::TASK_ID];
        
        // task should have matching data
        $task = $this->taskRepo->fetch(new Task([
            Task::TASK_ID => $taskId
        ]));
        $this->assertSame($description, $task->get(Task::DESCRIPTION));
        // TODO: compare tags
    }
    
    public function tearDown() {
        ob_end_clean();
        parent::tearDown();
    }
}
?>
