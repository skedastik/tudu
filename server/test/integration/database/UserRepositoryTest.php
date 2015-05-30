<?php
namespace Tudu\Test\Integration\Database;

use \Tudu\Test\Integration\Database\DatabaseTest;
use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Data\Model\User;
use \Tudu\Data\Repository\User as UserRepo;

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
    
    public function testGetByIDShouldSucceedGivenValidID() {
        $user_id = $this->repo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1');
        $user = $this->repo->getByID($user_id);
        $this->assertTrue($user instanceof User);
    }
    
    public function testGetByIDShouldFailGivenNonexistentID() {
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->repo->getByID(1);
    }
    
    public function testGetByEmailShouldSucceedGivenValidEmail() {
        $this->repo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1');
        $user = $this->repo->getByEmail('foo@bar.com');
        $this->assertTrue($user instanceof User);
    }
    
    public function testGetByEmailShouldFailGivenNonexistentEmail() {
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->repo->getByEmail('doesnt@exist.xyz');
    }
    
    public function testSignupUserShouldCreateUserWithNormalizedData() {
        $pwHash = 'unlikely_pw_hash';
        $user_id = $this->repo->signupUser("   foo@bar.com   \t", $pwHash, '127.0.0.1');
        $user = $this->repo->getByID($user_id);
        $this->assertSame('foo@bar.com', $user->get(User::EMAIL));
        $this->assertSame($pwHash, $user->get(User::PASSWORD_HASH));
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
        $this->setExpectedException('\Tudu\Core\Exception\Validation');
        $this->repo->signupUser($email, 'unlikely_pw_hash', '127.0.0.1');
    }
    
    public function testConfirmUserShouldSucceedGivenCorrectSignupToken() {
        $email = 'foo@bar.com';
        $id = $this->repo->signupUser($email, 'unlikely_pw_hash', '127.0.0.1');
        $user = $this->repo->getByID($id);
        $signupToken = Transform::HStore()->execute($user->get('kvs'))[User::SIGNUP_TOKEN];
        $confirmId = $this->repo->confirmUser($id, $signupToken, '127.0.0.1');
        $this->assertEquals($id, $confirmId);
    }
    
    public function testConfirmUserShouldFailGivenNonexistentUserId() {
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->repo->confirmUser(-1, 'unlikely_signup_token', '127.0.0.1');
    }
    
    public function testConfirmUserShouldFailGivenInvalidSignupToken() {
        $email = 'foo@bar.com';
        $id = $this->repo->signupUser($email, 'unlikely_pw_hash', '127.0.0.1');
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->repo->confirmUser($id, 'unlikely_signup_token', '127.0.0.1');
    }
    
    public function testConfirmUserShouldFailForAlreadyConfirmedUser() {
        $email = 'foo@bar.com';
        $id = $this->repo->signupUser($email, 'unlikely_pw_hash', '127.0.0.1');
        $user = $this->repo->getByID($id);
        $signupToken = Transform::HStore()->execute($user->get('kvs'))[User::SIGNUP_TOKEN];
        $this->repo->confirmUser($id, $signupToken, '127.0.0.1');
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->repo->confirmUser($id, $signupToken, '127.0.0.1');
    }
    
    public function testSetUserPasswordHashShouldSucceedGivenValidInputs() {
        $user_id = $this->repo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1');
        $result_id = $this->repo->setUserPasswordHash($user_id, 'new_pw_hash', '127.0.0.1');
        $this->assertEquals($user_id, $result_id);
    }
    
    public function testSetUserPasswordHashShouldFailGivenInvalidUserId() {
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->repo->setUserPasswordHash(-1, 'new_pw_hash', '127.0.0.1');
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
        $this->assertSame('baz@qux.xyz', $user->get(User::EMAIL));
    }
    
    public function testSetUserEmailShouldFailGivenInvalidUserId() {
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->repo->setUserEmail(-1, 'baz@qux.xyz', '127.0.0.1');
    }
    
    public function testSetUserEmailShouldFailGivenIdenticalEmail() {
        $user_id = $this->repo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1');
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->repo->setUserEmail($user_id, 'foo@bar.com', '127.0.0.1');
    }
    
    public function testSetUserEmailToAlreadyUsedEmailShouldFail() {
        $this->repo->signupUser('baz@qux.xyz', 'unlikely_pw_hash', '127.0.0.1');
        $user_id_in = $this->repo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1');
        $this->setExpectedException('\Tudu\Core\Exception\Validation');
        $this->repo->setUserEmail($user_id_in, 'baz@qux.xyz', '127.0.0.1');
    }
}
?>
