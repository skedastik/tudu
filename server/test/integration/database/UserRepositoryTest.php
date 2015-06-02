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
        $user = new User([
            User::EMAIL => 'foo@bar.com',
            User::PASSWORD => 'unlikely_pw_hash'
        ]);
        $user_id = $this->repo->signupUser($user, '127.0.0.1');
    }
    
    public function testFetchShouldSucceedGivenValidID() {
        $user = new User([
            User::EMAIL => 'foo@bar.com',
            User::PASSWORD => 'unlikely_pw_hash'
        ]);
        $userId = $this->repo->signupUser($user, '127.0.0.1');
        $user = $this->repo->fetch(new User([
            User::USER_ID => $userId,
        ]));
    }
    
    public function testFetchShouldFailGivenNonexistentID() {
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $user = new User([
            User::USER_ID => -1
        ]);
        $this->repo->fetch($user);
    }
    
    public function testFetchShouldSucceedGivenValidEmail() {
        $user = new User([
            User::EMAIL => 'foo@bar.com',
            User::PASSWORD => 'unlikely_pw_hash'
        ]);
        $this->repo->signupUser($user, '127.0.0.1');
        $user = $this->repo->fetch($user);
    }
    
    public function testFetchShouldFailGivenNonexistentEmail() {
        $user = new User([
            User::EMAIL => 'foo@bar.com',
        ]);
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->repo->fetch($user);
    }
    
    public function testSignupUserShouldCreateUserWithNormalizedData() {
        $user = new User([
            User::EMAIL => "   foo@bar.com   \t",
            User::PASSWORD => 'unlikely_pw_hash'
        ]);
        $userId = $this->repo->signupUser($user, '127.0.0.1');
        $user = $this->repo->fetch(new User([
            User::USER_ID => $userId,
        ]));
        $this->assertSame('foo@bar.com', $user->get(User::EMAIL));
    }
    
    public function testSignupUserShouldCreateUserWithStatusInit() {
        $user = new User([
            User::EMAIL => 'foo@bar.com',
            User::PASSWORD => 'unlikely_pw_hash'
        ]);
        $userId = $this->repo->signupUser($user, '127.0.0.1');
        $user = $this->repo->fetch(new User([
            User::USER_ID => $userId,
        ]));
        $this->assertSame('init', $user->get('status'));
    }
    
    public function testSignupUserWithAutoConfirmShouldCreateUserWithStatusActive() {
        $user = new User([
            User::EMAIL => 'foo@bar.com',
            User::PASSWORD => 'unlikely_pw_hash'
        ]);
        $userId = $this->repo->signupUser($user, '127.0.0.1', true);
        $user = $this->repo->fetch(new User([
            User::USER_ID => $userId,
        ]));
        $this->assertSame('active', $user->get('status'));
    }
    
    public function testSignupUserShouldFailGivenEmailThatIsAlreadyTaken() {
        $user = new User([
            User::EMAIL => 'foo@bar.com',
            User::PASSWORD => 'unlikely_pw_hash'
        ]);
        $this->repo->signupUser($user, '127.0.0.1');
        $this->setExpectedException('\Tudu\Core\Exception\Validation');
        $this->repo->signupUser($user, '127.0.0.1');
    }
    
    public function testConfirmUserShouldSucceedGivenCorrectSignupToken() {
        $user = new User([
            User::EMAIL => 'foo@bar.com',
            User::PASSWORD => 'unlikely_pw_hash'
        ]);
        $userId = $this->repo->signupUser($user, '127.0.0.1');
        $user = $this->repo->fetch(new User([
            User::USER_ID => $userId,
        ]));
        $tokenString = Transform::HStore()->execute($user->get('kvs'))[User::SIGNUP_TOKEN];
        $user = new User([
            User::USER_ID => $userId,
            User::SIGNUP_TOKEN => $tokenString
        ]);
        $confirmId = $this->repo->confirmUser($user, '127.0.0.1');
    }
    
    public function testConfirmUserShouldFailGivenNonexistentUserId() {
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $user = new User([
            User::USER_ID => -1,
            User::SIGNUP_TOKEN => 'unlikely_signup_token'
        ]);
        $this->repo->confirmUser($user, '127.0.0.1');
    }
    
    public function testConfirmUserShouldFailGivenInvalidSignupToken() {
        $user = new User([
            User::EMAIL => 'foo@bar.com',
            User::PASSWORD => 'unlikely_pw_hash'
        ]);
        $userId = $this->repo->signupUser($user, '127.0.0.1');
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $user = new User([
            User::USER_ID => $userId,
            User::SIGNUP_TOKEN => 'bad_signup_token'
        ]);
        $this->repo->confirmUser($user, '127.0.0.1');
    }
    
    public function testConfirmUserShouldFailForAlreadyConfirmedUser() {
        $user = new User([
            User::EMAIL => 'foo@bar.com',
            User::PASSWORD => 'unlikely_pw_hash'
        ]);
        $userId = $this->repo->signupUser($user, '127.0.0.1');
        $user = $this->repo->fetch(new User([
            User::USER_ID => $userId,
        ]));
        $signupToken = Transform::HStore()->execute($user->get('kvs'))[User::SIGNUP_TOKEN];
        $user = new User([
            User::USER_ID => $userId,
            User::SIGNUP_TOKEN => $signupToken
        ]);
        $this->repo->confirmUser($user, '127.0.0.1');
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->repo->confirmUser($user, '127.0.0.1');
    }
    
    public function testUpdateUserPasswordHashShouldSucceedGivenValidInputs() {
        $user = new User([
            User::EMAIL => 'foo@bar.com',
            User::PASSWORD => 'unlikely_pw_hash'
        ]);
        $userId = $this->repo->signupUser($user, '127.0.0.1');
        $user = new User([
            User::USER_ID => $userId,
            User::PASSWORD => 'new_pw_hash'
        ]);
        $this->repo->updateUser($user, '127.0.0.1');
    }
    
    public function testUpdateUserPasswordHashShouldFailGivenInvalidUserId() {
        $user = new User([
            User::USER_ID => -1,
            User::PASSWORD => 'password'
        ]);
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->repo->updateUser($user, '127.0.0.1');
    }
    
    public function testUpdateUserEmailShouldSucceedGivenValidInput() {
        $user = new User([
            User::EMAIL => 'foo@bar.com',
            User::PASSWORD => 'unlikely_pw_hash'
        ]);
        $userId = $this->repo->signupUser($user, '127.0.0.1');
        $user = new User([
            User::USER_ID => $userId,
            User::EMAIL => 'baz@qux.xyz',
        ]);
        $this->repo->updateUser($user, '127.0.0.1');
    }
    
    public function testUpdateUserEmailShouldNormalizeEmail() {
        $user = new User([
            User::EMAIL => 'foo@bar.com',
            User::PASSWORD => 'unlikely_pw_hash'
        ]);
        $userId = $this->repo->signupUser($user, '127.0.0.1');
        $user = new User([
            User::USER_ID => $userId,
            User::EMAIL => " \n  baz@qux.xyz   ",
        ]);
        $this->repo->updateUser($user, '127.0.0.1');
        $user = $this->repo->fetch($user);
        $this->assertSame('baz@qux.xyz', $user->get(User::EMAIL));
    }
    
    public function testUpdateUserEmailShouldFailGivenInvalidUserId() {
        $user = new User([
            User::USER_ID => -1,
            User::EMAIL => 'foo@bar.com',
        ]);
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->repo->updateUser($user, '127.0.0.1');
    }
    
    public function testUpdateUserEmailToAlreadyUsedEmailShouldFail() {
        $user1 = new User([
            User::EMAIL => 'baz@qux.xyz',
            User::PASSWORD => 'unlikely_pw_hash'
        ]);
        $this->repo->signupUser($user1, '127.0.0.1');
        $user2 = new User([
            User::EMAIL => 'foo@bar.xyz',
            User::PASSWORD => 'unlikely_pw_hash'
        ]);
        $userId = $this->repo->signupUser($user2, '127.0.0.1');
        $user2 = new User([
            User::USER_ID => $userId,
            User::EMAIL => 'baz@qux.xyz',
        ]);
        $this->setExpectedException('\Tudu\Core\Exception\Validation');
        $this->repo->updateUser($user2, '127.0.0.1');
    }
}
?>
