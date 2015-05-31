<?php
namespace Tudu\Test\Integration\Api;

use \Tudu\Test\Mock\MockApp;
use \Tudu\Test\Integration\Database\DatabaseTest;
use \Tudu\Data\Repository;
use \Tudu\Core\Handler\Auth\Auth as AuthHandler;
use \Tudu\Handler\Auth\Contract\BasicAuthentication;
use \Tudu\Core\Encoder;
use \Tudu\Data\Model\User;

class BasicAuthenticationTest extends DatabaseTest {
    
    protected $userRepo;
    
    public function setUp() {
        parent::setUp();
        ob_start();
        $this->app = new MockApp();
        $this->app->addEncoder(new Encoder\JSON());
        $this->userRepo = new Repository\User($this->db);
    }
    
    public function testValidCredentialsUsingUserIdShouldReturn200() {
        // create a new user
        $password = 'test_password';
        $user = new User([
            User::EMAIL => 'foo@bar.xyz',
            User::PASSWORD => $password
        ]);
        $userId = $this->userRepo->signupUser($user, '127.0.0.1', true);
        
        // simulate a POST to /signin with basic authentication
        $basicAuthentication = new BasicAuthentication($this->db);
        $this->app->setRequestMethod('POST');
        $credentials = BasicAuthentication::encodeCredentials($userId, $password);
        $this->app->setRequestHeader('Authorization', $basicAuthentication->getScheme().' '.$credentials);
        $this->app->setHandler(new AuthHandler(
            $this->app,
            $this->db,
            $basicAuthentication
        ));
        $this->app->run();
        
        // response status should be 200
        $this->assertEquals(200, $this->app->getResponseStatus());
    }
    
    /**
     * @group todo
     */
    public function testValidCredentialsUsingEmailShouldReturn200() {
        // create a new user
        $email = 'foo@bar.xyz';
        $password = 'test_password';
        $user = new User([
            User::EMAIL => $email,
            User::PASSWORD => $password
        ]);
        $userId = $this->userRepo->signupUser($user, '127.0.0.1', true);
        
        // simulate a POST to /signin with basic authentication
        $basicAuthentication = new BasicAuthentication($this->db);
        $this->app->setRequestMethod('POST');
        $credentials = BasicAuthentication::encodeCredentials($email, $password);
        $this->app->setRequestHeader('Authorization', $basicAuthentication->getScheme().' '.$credentials);
        $this->app->setHandler(new AuthHandler(
            $this->app,
            $this->db,
            $basicAuthentication
        ));
        $this->app->run();
        
        // response status should be 200
        $this->assertEquals(200, $this->app->getResponseStatus());
    }
    
    public function testInvalidCredentialsShouldReturn401() {
        // create a new user
        $user = new User([
            User::EMAIL => 'foo@bar.xyz',
            User::PASSWORD => 'test_password'
        ]);
        $userId = $this->userRepo->signupUser($user, '127.0.0.1', true);

        // simulate a POST to /signin with basic authentication
        $basicAuthentication = new BasicAuthentication($this->db);
        $this->app->setRequestMethod('POST');
        $credentials = BasicAuthentication::encodeCredentials($userId, 'wrong_password');
        $this->app->setRequestHeader('Authorization', $basicAuthentication->getScheme().' '.$credentials);
        $this->app->setHandler(new AuthHandler(
            $this->app,
            $this->db,
            $basicAuthentication
        ));
        $this->app->run();

        // response status should be 401
        $this->assertEquals(401, $this->app->getResponseStatus());
    }
    
    public function tearDown() {
        ob_end_clean();
        parent::tearDown();
    }
}
?>
