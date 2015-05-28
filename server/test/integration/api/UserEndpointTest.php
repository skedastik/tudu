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
        $this->passwordDelegate = new PHPass();
        $this->userRepo = new Repository\User($this->db);
    }
    
    public function testPostingAValidUserShouldSignUpANewUserAndReturnAppropriateData() {
        $password = 'mypassword';
        
        // simulate a valid POST to /users/
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setHandler(
            new Handler\Api\User\Users($this->app, $this->db, [], $this->passwordDelegate)
        );
        $this->app->setRequestBody('{
            "email": "foo@bar.xyz",
            "password": "'.$password.'"
        }');
        $this->app->run();
        
        // extract "user_id" property from response body
        $user_id = $this->decodeOutputBuffer()['user_id'];
        
        // user should have matching data
        $user = $this->userRepo->getByID($user_id);
        $this->assertSame('foo@bar.xyz', $user->get('email'));
        $this->assertTrue($this->passwordDelegate->compare($password, $user->get('password_hash')));
    }
    
    public function testValidPostToConfirmShouldConfirmExistingUser() {
        // create a new user
        $user_id = $this->userRepo->signupUser('foo@bar.xyz', 'password_hash', '127.0.0.1', true);
        $user = $this->userRepo->getByID($user_id);
        
        // extract signup token from user KVS
        $userKvs = $user->get('kvs');
        $transformer = Transform::HStore()->to()->keyValueArray();
        $signupToken = $transformer->execute($userKvs)['signup_token'];
        
        // simulate a valid POST to /users/:user_id/confirm
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setHandler(
            new Handler\Api\User\Confirm($this->app, $this->db, [
                'user_id' => $user_id
            ])
        );
        $this->app->setRequestBody('{
            "signup_token": "'.$signupToken.'"
        }');
        $this->app->run();
        
        // user should have "active" status
        $user = $this->userRepo->getByID($user_id);
        $this->assertEquals('active', $user->get('status'));
    }
    
    public function testValidPostToSigninShouldReturnAccessToken() {
        // create a new user
        $user_id = $this->userRepo->signupUser('foo@bar.xyz', 'password_hash', '127.0.0.1', true);
        $user = $this->userRepo->getByID($user_id);
        
        // simulate a valid POST to /signin
        $this->app->setRequestMethod('POST');
        $this->app->setHandler(
            new Handler\Api\User\Signin($this->app, $this->db, [
                'user_id' => $user_id
            ])
        );
        $this->app->run();
        
        // extract access token string from response body
        $tokenString = $this->decodeOutputBuffer()['access_token'];
        
        // token string should match that in database
        $tokenRepo = new Repository\AccessToken($this->db);
        $accessToken = $tokenRepo->getByUserIDAndTokenString($user_id, $tokenString);
        $this->assertEquals($tokenString, $accessToken->get('token_string'));
    }
    
    public function tearDown() {
        ob_end_clean();
        parent::tearDown();
    }
}
?>
