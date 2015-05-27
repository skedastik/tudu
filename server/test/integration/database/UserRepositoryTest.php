<?php
namespace Tudu\Test\Integration\Database;

use \Tudu\Test\Integration\Database\DatabaseTest;
use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Data\Model\User;
use \Tudu\Data\Repository\User as UserRepo;
use \Tudu\Core\Error;

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
    
    public function testSignupUserShouldCreateUserWithNormalizedData() {
        $pwHash = 'unlikely_pw_hash';
        $user_id = $this->repo->signupUser("   foo@bar.com   \t", $pwHash, '127.0.0.1');
        $user = $this->repo->getByID($user_id);
        $this->assertSame('foo@bar.com', $user->get('email'));
        $this->assertSame($pwHash, $user->get('password_hash'));
    }
    
    public function testSignupUserShouldCreateUserWithStatusInit() {
        $user_id = $this->repo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1');
        $user = $this->repo->getByID($user_id);
        $this->assertSame('init', $user->get('status'));
    }
    
    public function testSignupUserWithAutoConfirmShouldCreateUserWithStatusActive() {
        $user_id = $this->repo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1', true);
        $user = $this->repo->getByID($user_id);
        $this->assertSame('active', $user->get('status'));
    }
    
    public function testSignupUserShouldFailGivenEmailThatIsAlreadyTaken() {
        $email = 'foo@bar.com';
        $this->repo->signupUser($email, 'unlikely_pw_hash', '127.0.0.1');
        $error = $this->repo->signupUser($email, 'unlikely_pw_hash', '127.0.0.1');
        $this->assertTrue($error instanceof Error);
        $this->assertEquals(Error::VALIDATION, $error->getError());
    }

    public function testGetByIDShouldFailGivenNonexistentID() {
        $error = $this->repo->getByID(1);
        $this->assertTrue($error instanceof Error);
        $this->assertEquals(Error::GENERIC, $error->getError());
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
        $confirmId = $this->repo->confirmUser($id, $signupToken, '127.0.0.1');
        $this->assertEquals($id, $confirmId);
    }
    
    public function testConfirmUserShouldFailGivenNonexistentUserId() {
        $error = $this->repo->confirmUser(-1, 'unlikely_signup_token', '127.0.0.1');
        $this->assertTrue($error instanceof Error);
        $this->assertEquals(Error::GENERIC, $error->getError());
    }
    
    public function testConfirmUserShouldFailGivenInvalidSignupToken() {
        $email = 'foo@bar.com';
        $id = $this->repo->signupUser($email, 'unlikely_pw_hash', '127.0.0.1');
        $error = $this->repo->confirmUser($id, 'unlikely_signup_token', '127.0.0.1');
        $this->assertTrue($error instanceof Error);
        $this->assertEquals(Error::GENERIC, $error->getError());
    }
    
    public function testConfirmUserShouldFailForAlreadyConfirmedUser() {
        $email = 'foo@bar.com';
        $id = $this->repo->signupUser($email, 'unlikely_pw_hash', '127.0.0.1');
        $user = $this->repo->getByID($id);
        $signupToken = Transform::HStore()->execute($user->get('kvs'))['signup_token'];
        $this->repo->confirmUser($id, $signupToken, '127.0.0.1');
        $error = $this->repo->confirmUser($id, $signupToken, '127.0.0.1');
        $this->assertTrue($error instanceof Error);
        $this->assertEquals(Error::NOTICE, $error->getError());
    }
    
    public function testSetUserPasswordHashShouldSucceedGivenValidInputs() {
        $user_id = $this->repo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1');
        $result_id = $this->repo->setUserPasswordHash($user_id, 'new_pw_hash', '127.0.0.1');
        $this->assertEquals($user_id, $result_id);
    }
    
    public function testSetUserPasswordHashShouldFailGivenInvalidUserId() {
        $error = $this->repo->setUserPasswordHash(-1, 'new_pw_hash', '127.0.0.1');
        $this->assertTrue($error instanceof Error);
        $this->assertEquals(Error::GENERIC, $error->getError());
    }
    
    public function testSetUserEmailShouldSucceedGivenValidInput() {
        $user_id_in = $this->repo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1');
        $user_id_out = $this->repo->setUserEmail($user_id_in, 'baz@qux.xyz', '127.0.0.1');
        $this->assertEquals($user_id_in, $user_id_out);
    }
    
    public function testSetUserEmailShouldNormalizeEmail() {
        $user_id_in = $this->repo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1');
        $user_id_out = $this->repo->setUserEmail($user_id_in, " \n  baz@qux.xyz   ", '127.0.0.1');
        $user = $this->repo->getByID($user_id_in);
        $this->assertSame('baz@qux.xyz', $user->get('email'));
    }
    
    public function testSetUserEmailShouldFailGivenInvalidUserId() {
        $error = $this->repo->setUserEmail(-1, 'baz@qux.xyz', '127.0.0.1');
        $this->assertTrue($error instanceof Error);
        $this->assertEquals(Error::GENERIC, $error->getError());
    }
    
    public function testSetUserEmailShouldFailGivenIdenticalEmail() {
        $user_id = $this->repo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1');
        $error = $this->repo->setUserEmail($user_id, 'foo@bar.com', '127.0.0.1');
        $this->assertTrue($error instanceof Error);
        $this->assertEquals(Error::NOTICE, $error->getError());
    }
    
    public function testSetUserEmailToAlreadyUsedEmailShouldFail() {
        $this->repo->signupUser('baz@qux.xyz', 'unlikely_pw_hash', '127.0.0.1');
        $user_id_in = $this->repo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1');
        $error = $this->repo->setUserEmail($user_id_in, 'baz@qux.xyz', '127.0.0.1');
        $this->assertTrue($error instanceof Error);
        $this->assertEquals(Error::VALIDATION, $error->getError());
    }
}
?>
