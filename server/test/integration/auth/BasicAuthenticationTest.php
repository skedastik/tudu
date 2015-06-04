<?php
namespace Tudu\Test\Integration\Api;

use \Tudu\Test\Mock\MockApp;
use \Tudu\Test\Integration\Database\DatabaseTest;
use \Tudu\Data\Repository;
use \Tudu\Core\Handler\Auth\Auth as AuthHandler;
use \Tudu\Handler\Auth\Contract\BasicAuthentication;
use \Tudu\Core\Encoder;
use \Tudu\Data\Model\User;

/**
 * @group todo
 */
class BasicAuthenticationTest extends DatabaseTest {
    
    protected $email;
    protected $password;
    protected $userId;
    
    public function setUp() {
        parent::setUp();
        ob_start();
        $this->app = new MockApp();
        $this->app->addEncoder(new Encoder\JSON());
        $this->email = 'foobar@example.com';
        $this->password = 'test_password';
        $userRepo = new Repository\User($this->db);
        $user = new User([
            User::EMAIL => $this->email,
            User::PASSWORD => $this->password
        ]);
        $this->userId = $userRepo->signupUser($user, '127.0.0.1', true);
    }
    
    public function testValidCredentialsUsingUserIdShouldReturn200() {
        // simulate a POST to /signin with basic authentication
        $basicAuthentication = new BasicAuthentication($this->db);
        $this->app->setRequestMethod('POST');
        $credentials = BasicAuthentication::encodeCredentials($this->userId, $this->password);
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
    
    public function testValidCredentialsUsingEmailShouldReturn200() {
        // simulate a POST to /signin with basic authentication
        $basicAuthentication = new BasicAuthentication($this->db);
        $this->app->setRequestMethod('POST');
        $credentials = BasicAuthentication::encodeCredentials($this->email, $this->password);
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
    
    public function testInvalidPasswordShouldReturn401() {
        // simulate a POST to /signin with basic authentication
        $basicAuthentication = new BasicAuthentication($this->db);
        $this->app->setRequestMethod('POST');
        $credentials = BasicAuthentication::encodeCredentials($this->userId, 'wrong_password');
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
    
    public function testInvalidIdShouldReturn401() {
        // simulate a POST to /signin with basic authentication
        $basicAuthentication = new BasicAuthentication($this->db);
        $this->app->setRequestMethod('POST');
        $credentials = BasicAuthentication::encodeCredentials(-1, $this->password);
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
