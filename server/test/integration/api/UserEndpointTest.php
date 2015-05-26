<?php
namespace Tudu\Test\Integration\Api;

use \Tudu\Test\Mock\MockApp;
use \Tudu\Test\Integration\Database\DatabaseTest;
use \Tudu\Data\Repository;
use \Tudu\Core\Encoder;
use \Tudu\Handler;
use \Tudu\Delegate\PHPass;

class UserEndpointTest extends DatabaseTest {
    
    protected $repo;
    
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
        $this->app->setEncoder(new Encoder\JSON());
        $this->repo = new Repository\User($this->db);
    }
    
    public function testPostingAValidUserShouldSignUpANewUserWithMatchingData() {
        $this->handler = new Handler\Api\User\Users($this->app, $this->db);
        $this->app->setHandler($this->handler);
        
        $password = 'mypassword';
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setRequestBody('{
            "email": "foo@bar.xyz",
            "password": "'.$password.'"
        }');
        $this->app->run();
        
        $user_id = $this->decodeOutputBuffer()['user_id'];
        $user = $this->repo->getByID($user_id);
        $phpass = new PHPass();
        $this->assertSame('foo@bar.xyz', $user->get('email'));
        $this->assertTrue($phpass->compare($password, $user->get('password_hash')));
    }
    
    public function tearDown() {
        parent::tearDown();
        ob_end_clean();
    }
}
?>
