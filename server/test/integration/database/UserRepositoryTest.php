<?php
namespace Tudu\Test\Integration\Database;

use \Tudu\Test\Integration\Database\DatabaseTest;
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

    public function testGetByIDShouldFailGivenNonexistentID() {
        $error = $this->repo->getByID(1);
        $this->assertTrue($error instanceof Core\Error);
        $this->assertEquals(Repository\Error::RESOURCE_NOT_FOUND, $error->getError());
    }
    
    public function testSignupUserShouldSucceedGivenValidEmail() {
        $user_id = $this->repo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1');
        $this->assertTrue($user_id >= 0);
    }
    
    public function testGetByIDShouldSucceedGivenValidID() {
        $user_id = $this->repo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1');
        $user = $this->repo->getByID($user_id);
        $this->assertTrue($user instanceof User);
    }
    
    public function testSignupUserShouldCreateUserWithMatchingData() {
        $password = 'password';
        $email = 'foo@bar.com';
        $pwHash = 'unlikely_pw_hash';
        $user_id = $this->repo->signupUser($email, $pwHash, '127.0.0.1');
        $user = $this->repo->getByID($user_id);
        $this->assertSame($email, $user->get('email'));
        $this->assertSame($pwHash, $user->get('password_hash'));
    }
    
    public function testSignupUserShouldFailGivenInvalidEmail() {
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
        $this->assertEquals(Repository\Error::ALREADY_IN_USE, $error->getError());
    }
    
    public function testConfirmUserShouldFailGivenNonexistentEmailAddress() {
        $error = $this->repo->confirmUser('foo@bar.com', 'kjsdfjksnba', '127.0.0.1');
        $this->assertTrue($error instanceof Core\Error);
        $this->assertEquals(Repository\Error::RESOURCE_NOT_FOUND, $error->getError());
    }
    
    public function testConfirmUserShouldFailGivenInvalidSignupToken() {
        $email = 'foo@bar.com';
        $this->repo->signupUser($email, 'unlikely_pw_hash', '127.0.0.1');
        $error = $this->repo->confirmUser($email, 'kjsdfjksnba', '127.0.0.1');
        $this->assertTrue($error instanceof Core\Error);
        $this->assertEquals(Repository\Error::GENERIC, $error->getError());
    }
    
    public function testConfirmUserShouldFailForAlreadyConfirmedUser() {
        $email = 'foo@bar.com';
        $id = $this->repo->signupUser($email, 'unlikely_pw_hash', '127.0.0.1');
        $user = $this->repo->getByID($id);
        // TODO: Extract signup token from kvs HSTORE.
        // $error = $this->repo->confirmUser($email, 'kjsdfjksnba', '127.0.0.1');
        // $this->assertTrue($error instanceof Core\Error);
        // $this->assertEquals(Repository\Error::GENERIC, $error->getError());
    }
}
?>
