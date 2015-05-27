<?php
namespace Tudu\Test\Integration\Api;

use \Tudu\Test\Mock\MockApp;
use \Tudu\Test\Integration\Database\DatabaseTest;
use \Tudu\Data\Repository;
use \Tudu\Core\Encoder;
use \Tudu\Handler;
use \Tudu\Delegate\PHPass;
use \Tudu\Core\Data\Transform\Transform;

class UserEndpointTest extends DatabaseTest {
    
    protected $userRepo;
    
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
        $this->userRepo = new Repository\User($this->db);
    }
    
    public function testPostingAValidUserShouldSignUpANewUserAndReturnAppropriateData() {
        $this->app->setHandler(
            new Handler\Api\User\Users($this->app, $this->db)
        );
        
        $password = 'mypassword';
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setRequestBody('{
            "email": "foo@bar.xyz",
            "password": "'.$password.'"
        }');
        $this->app->run();
        
        $result = $this->decodeOutputBuffer();
        $user_id = $result['user_id'];
        $user = $this->userRepo->getByID($user_id);
        $phpass = new PHPass();
        $this->assertSame('foo@bar.xyz', $user->get('email'));
        $this->assertTrue($phpass->compare($password, $user->get('password_hash')));
    }
    
    public function testPostingToConfirmShouldConfirmAnExistingUser() {
        $this->app->setHandler(
            new Handler\Api\User\Users($this->app, $this->db)
        );
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        
        $this->app->setRequestBody('{
            "email": "foo@bar.xyz",
            "password": "mypassword"
        }');
        $this->app->run();
        $result = $this->decodeOutputBuffer();
        $user_id = $result['user_id'];
        $user = $this->userRepo->getByID($user_id);
        
        $userKvs = $user->get('kvs');
        $transformer = Transform::HStore()->to()->keyValueArray();
        $signupToken = $transformer->execute($userKvs)['signup_token'];
        $this->app->setHandler(
            new Handler\Api\User\Confirm($this->app, $this->db, [
                'user_id' => $user_id
            ])
        );
        $this->app->setRequestBody('{
            "signup_token": "'.$signupToken.'"
        }');
        $this->app->run();
        $user = $this->userRepo->getByID($user_id);
        $this->assertEquals('active', $user->get('status'));
    }
    
    public function tearDown() {
        ob_end_clean();
        parent::tearDown();
    }
}
?>
