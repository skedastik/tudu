<?php
namespace Tudu\Test\Integration\Database;

use \Tudu\Test\Integration\Database\DatabaseTest;
use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Data\Model\User;
use \Tudu\Data\Repository\User as UserRepo;
use \Tudu\Core;
use \Tudu\Core\Data\Repository;

class UserRepositoryTest extends DatabaseTest {
    
    protected $repo;
    
    public function setUp() {
        parent::setUp();
        $this->repo = new UserRepo($this->db);
    }
    
    public function testSignupUserShouldSucceedGivenValidEmail() {
        $user_id = $this->repo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1');
        $this->assertTrue($user_id >= 0);
    }
    
    public function testSignupUserShouldFailGivenMalformedEmail() {
        $email = 'foo@bar';
        $error = $this->repo->signupUser($email, 'unlikely_pw_hash', '127.0.0.1');
        $this->assertTrue($error instanceof Core\Error);
        $this->assertEquals(Repository\Error::VALIDATION, $error->getError());
    }
    
    public function testSignupUserShouldFailGivenEmailThatIsAlreadyTaken() {
        $email = 'foo@bar.com';
        $this->repo->signupUser($email, 'unlikely_pw_hash', '127.0.0.1');
        $error = $this->repo->signupUser($email, 'unlikely_pw_hash', '127.0.0.1');
        $this->assertTrue($error instanceof Core\Error);
        $this->assertEquals(Repository\Error::VALIDATION, $error->getError());
    }

    public function testGetByIDShouldFailGivenNonexistentID() {
        $error = $this->repo->getByID(1);
        $this->assertTrue($error instanceof Core\Error);
        $this->assertEquals(Repository\Error::GENERIC, $error->getError());
    }
    
    public function testGetByIDShouldSucceedGivenValidID() {
        $user_id = $this->repo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1');
        $user = $this->repo->getByID($user_id);
        $this->assertTrue($user instanceof User);
    }
    
    public function testConfirmUserShouldSucceedGivenCorrectSignupToken() {
        $email = 'foo@bar.com';
        $id = $this->repo->signupUser($email, 'unlikely_pw_hash', '127.0.0.1');
        $user = $this->repo->getByID($id);
        $signupToken = Transform::HStore()->execute($user->get('kvs'))['signup_token'];
        $confirmId = $this->repo->confirmUser($email, $signupToken, '127.0.0.1');
        $this->assertEquals($id, $confirmId);
    }
    
    public function testConfirmUserShouldFailGivenNonexistentEmailAddress() {
        $error = $this->repo->confirmUser('foo@bar.com', 'unlikely_signup_token', '127.0.0.1');
        $this->assertTrue($error instanceof Core\Error);
        $this->assertEquals(Repository\Error::GENERIC, $error->getError());
    }
    
    public function testConfirmUserShouldFailGivenInvalidSignupToken() {
        $email = 'foo@bar.com';
        $this->repo->signupUser($email, 'unlikely_pw_hash', '127.0.0.1');
        $error = $this->repo->confirmUser($email, 'unlikely_signup_token', '127.0.0.1');
        $this->assertTrue($error instanceof Core\Error);
        $this->assertEquals(Repository\Error::GENERIC, $error->getError());
    }
    
    public function testConfirmUserShouldFailForAlreadyConfirmedUser() {
        $email = 'foo@bar.com';
        $id = $this->repo->signupUser($email, 'unlikely_pw_hash', '127.0.0.1');
        $user = $this->repo->getByID($id);
        $signupToken = Transform::HStore()->execute($user->get('kvs'))['signup_token'];
        $this->repo->confirmUser($email, $signupToken, '127.0.0.1');
        $error = $this->repo->confirmUser($email, $signupToken, '127.0.0.1');
        $this->assertTrue($error instanceof Core\Error);
        $this->assertEquals(Repository\Error::GENERIC, $error->getError());
    }
    
    public function testSetUserPasswordHashShouldSucceedGivenValidInputs() {
        $user_id = $this->repo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1');
        $result_id = $this->repo->setUserPasswordHash($user_id, 'new_pw_hash', '127.0.0.1');
        $this->assertEquals($user_id, $result_id);
    }
}
?>
