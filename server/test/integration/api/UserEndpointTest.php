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
        $this->app->addEncoder(new Encoder\JSON());
        $this->passwordDelegate = new PHPass();
        $this->userRepo = new Repository\User($this->db);
    }
    
    public function testPostingAValidUserShouldSignUpANewUserAndReturnAppropriateData() {
        $password = 'mypassword';
        
        // simulate a valid POST to /users/
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setHandler(
            new Handler\Api\User\Users($this->app, $this->db, $this->passwordDelegate)
        );
        $this->app->setRequestBody('{
            "email": "foo@bar.xyz",
            "password": "'.$password.'"
        }');
        $this->app->run();
        
        // extract "user_id" property from response body
        $userId = $this->decodeOutputBuffer()[User::USER_ID];
        
        // user should have matching data
        $user = $this->userRepo->getByID($userId);
        $this->assertSame('foo@bar.xyz', $user->get(User::EMAIL));
        $this->assertTrue($this->passwordDelegate->compare($password, $user->get(User::PASSWORD_HASH)));
    }
    
    public function testValidPostToConfirmShouldConfirmExistingUser() {
        // create a new user
        $userId = $this->userRepo->signupUser('foo@bar.xyz', 'password_hash', '127.0.0.1', true);
        $user = $this->userRepo->getByID($userId);
        
        // extract signup token from user KVS
        $userKvs = $user->get('kvs');
        $transformer = Transform::HStore()->to()->keyValueArray();
        $signupToken = $transformer->execute($userKvs)[User::SIGNUP_TOKEN];
        
        // simulate a valid POST to /users/:user_id/confirm
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setContext([
            User::USER_ID => $userId
        ]);
        $this->app->setHandler(
            new Handler\Api\User\Confirm($this->app, $this->db)
        );
        $this->app->setRequestBody('{
            "signup_token": "'.$signupToken.'"
        }');
        $this->app->run();
        
        // user should have "active" status
        $user = $this->userRepo->getByID($userId);
        $this->assertEquals('active', $user->get('status'));
    }
    
    public function testValidPostToSigninShouldReturnAccessToken() {
        // create a new user
        $userId = $this->userRepo->signupUser('foo@bar.xyz', 'password_hash', '127.0.0.1', true);
        $user = $this->userRepo->getByID($userId);
        
        // simulate a valid POST to /signin
        $this->app->setRequestMethod('POST');
        $this->app->setContext([
            Auth::AUTHENTICATED_USER_MODEL => $user
        ]);
        $this->app->setHandler(
            new Handler\Api\User\Signin($this->app, $this->db)
        );
        $this->app->run();
        
        // extract access token string from response body
        $tokenString = $this->decodeOutputBuffer()[AccessToken::TOKEN_STRING];
        
        // token string should match that in database
        $tokenRepo = new Repository\AccessToken($this->db);
        $accessToken = $tokenRepo->getByUserIDAndTokenString($userId, $tokenString);
        $this->assertTrue($accessToken instanceof AccessToken);
        $this->assertEquals(200, $this->app->getResponseStatus());
    }
    
    public function testMultipleValidPostsToSigninShouldSucceed() {
        // create a new user
        $userId = $this->userRepo->signupUser('foo@bar.xyz', 'password_hash', '127.0.0.1', true);
        $user = $this->userRepo->getByID($userId);
        
        // simulate two valid POSTs to /signin
        for ($i = 0; $i < 2; $i++) {
            $this->app->setRequestMethod('POST');
            $this->app->setContext([
                Auth::AUTHENTICATED_USER_MODEL => $user
            ]);
            $this->app->setHandler(
                new Handler\Api\User\Signin($this->app, $this->db)
            );
            $this->app->run();
            $this->assertEquals(200, $this->app->getResponseStatus());
        }
    }
    
    public function tearDown() {
        ob_end_clean();
        parent::tearDown();
    }
}
?>
