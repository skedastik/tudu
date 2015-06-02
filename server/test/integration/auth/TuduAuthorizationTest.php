<?php
namespace Tudu\Test\Integration\Api;

use \Tudu\Test\Mock\MockApp;
use \Tudu\Test\Integration\Database\DatabaseTest;
use \Tudu\Data\Repository;
use \Tudu\Core\Handler\Auth\Auth as AuthHandler;
use \Tudu\Handler\Auth\Contract\TuduAuthorization;
use \Tudu\Core\Encoder;
use \Tudu\Data\Model\User;

class TuduAuthorizationTest extends DatabaseTest {
    
    protected $app;
    protected $mockAuthentication;
    protected $userRepo;
    
    public function setUp() {
        parent::setUp();
        ob_start();
        $this->app = new MockApp();
        $this->app->setRequestMethod('POST');
        $this->app->addEncoder(new Encoder\JSON());
        $scheme = 'Test';
        $this->app->setRequestHeader('Authorization', "$scheme Test-Param");
        $this->mockAuthentication = $this->getMockBuilder('\Tudu\Core\Handler\Auth\Contract\Authentication')->getMock();
        $this->mockAuthentication->method('getScheme')->willReturn($scheme);
        $this->userRepo = new Repository\User($this->db);
    }
    
    public function testAuthorizedCredentialsShouldReturn200() {
        // create new user
        $user = new User([
            User::EMAIL => 'foo@bar.xyz',
            User::PASSWORD => 'password_hash'
        ]);
        $userId = $this->userRepo->signupUser($user, '127.0.0.1', true);
        $user = $this->userRepo->fetch(new User([
            User::USER_ID => $userId,
        ]));
        
        // simulate authenticated request by above user
        $this->mockAuthentication->method('authenticate')->willReturn($user);
        
        // simulate POST to resource owned by above user
        $this->app->setHandler(new AuthHandler(
            $this->app,
            $this->db,
            $this->mockAuthentication,
            new TuduAuthorization($userId)
        ));
        $this->app->run();
        
        // response status should be 200
        $this->assertEquals(200, $this->app->getResponseStatus());
    }
    
    public function testAuthenticCredentialsWithoutSpecifyingResourceOwnerShouldReturn200() {
        // create new user
        $user = new User([
            User::EMAIL => 'foo@bar.xyz',
            User::PASSWORD => 'password_hash'
        ]);
        $userId = $this->userRepo->signupUser($user, '127.0.0.1', true);
        $user = $this->userRepo->fetch(new User([
            User::USER_ID => $userId,
        ]));
        
        // simulate authenticated request by above user
        $this->mockAuthentication->method('authenticate')->willReturn($user);
        
        // simulate POST to public resource
        $this->app->setHandler(new AuthHandler(
            $this->app,
            $this->db,
            $this->mockAuthentication,
            new TuduAuthorization()
        ));
        $this->app->run();
        
        // response status should be 200
        $this->assertEquals(200, $this->app->getResponseStatus());
    }
    
    public function testAuthenticCredentialsWithDifferentResourceOwnerShouldReturn403() {
        // create new user
        $user = new User([
            User::EMAIL => 'foo@bar.xyz',
            User::PASSWORD => 'password_hash'
        ]);
        $userId = $this->userRepo->signupUser($user, '127.0.0.1', true);
        $user = $this->userRepo->fetch(new User([
            User::USER_ID => $userId,
        ]));
        
        // simulate authenticated request by above user
        $this->mockAuthentication->method('authenticate')->willReturn($user);
        
        // simulate POST to resource owned by different user
        $this->app->setHandler(new AuthHandler(
            $this->app,
            $this->db,
            $this->mockAuthentication,
            new TuduAuthorization(-1)
        ));
        $this->app->run();
        
        // response status should be 403
        $this->assertEquals(403, $this->app->getResponseStatus());
    }
    
    public function testInactiveUserShouldReturn403() {
        // create new user without auto-confirming (status will not be "active")
        $user = new User([
            User::EMAIL => 'foo@bar.xyz',
            User::PASSWORD => 'password_hash'
        ]);
        $userId = $this->userRepo->signupUser($user, '127.0.0.1');
        $user = $this->userRepo->fetch(new User([
            User::USER_ID => $userId,
        ]));
    
        // simulate authenticated request by above user
        $this->mockAuthentication->method('authenticate')->willReturn($user);
    
        // simulate POST to resource owned by above user
        $this->app->setHandler(new AuthHandler(
            $this->app,
            $this->db,
            $this->mockAuthentication,
            new TuduAuthorization($userId)
        ));
        $this->app->run();
    
        // response status should be 403
        $this->assertEquals(403, $this->app->getResponseStatus());
    }
    
    public function tearDown() {
        ob_end_clean();
        parent::tearDown();
    }
}
?>
