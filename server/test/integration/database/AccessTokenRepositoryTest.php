<?php
namespace Tudu\Test\Integration\Database;

use \Tudu\Test\Integration\Database\DatabaseTest;
use \Tudu\Data\Model\User;
use \Tudu\Data\Model\AccessToken;
use \Tudu\Data\Repository\User as UserRepo;
use \Tudu\Data\Repository\AccessToken as AccessTokenRepo;

class AccessTokenRepositoryTest extends DatabaseTest {
    
    protected $userRepo;
    protected $tokenRepo;
    protected $user;
    
    public function setUp() {
        parent::setUp();
        $this->userRepo = new UserRepo($this->db);
        $this->tokenRepo = new AccessTokenRepo($this->db);
        $userId = $this->userRepo->signupUser('foo@bar.com', 'unlikely_pw_hash', '127.0.0.1', true);
        $this->user = $this->userRepo->getById($userId);
    }
    
    public function testCreateAccessTokenShouldSucceedGivenValidInputs() {
        $tokenId = $this->tokenRepo->createAccessToken($this->user->get(User::USER_ID), 'token_string', 'login', '1 week', false, '127.0.0.1');
        $this->assertTrue($tokenId >= 0);
    }
    
    public function testCreateAccessTokenShouldFailGivenInvalidUserId() {
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->tokenRepo->createAccessToken(-1, 'token_string', 'login', '1 week', false, '127.0.0.1');
    }
    
    public function testCreateAccessTokenShouldFailGivenNonUniqueTokenString() {
        $this->tokenRepo->createAccessToken($this->user->get(User::USER_ID), 'token_string', 'login', '1 week', false, '127.0.0.1');
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->tokenRepo->createAccessToken($this->user->get(User::USER_ID), 'token_string', 'login', '1 week', false, '127.0.0.1');
    }
    
    public function testRevokeActiveAccessTokensShouldSucceedGivenValidInputs() {
        $this->tokenRepo->createAccessToken($this->user->get(User::USER_ID), 'token_string', 'login', '1 week', false, '127.0.0.1');
        $revokeCount = $this->tokenRepo->revokeActiveAccessTokens($this->user->get(User::USER_ID), 'login', '127.0.0.1');
        $this->assertEquals(1, $revokeCount);
    }
    
    public function testRevokeActiveAccessTokensShouldFailIfNoActiveTokensExist() {
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->tokenRepo->revokeActiveAccessTokens($this->user->get(User::USER_ID), 'login', '127.0.0.1');
    }
    
    public function testValidateAccessTokenShouldSucceedGivenValidInputs() {
        $this->tokenRepo->createAccessToken($this->user->get(User::USER_ID), 'token_string', 'login', '1 week', false, '127.0.0.1');
        $result = $this->tokenRepo->validateAccessToken($this->user->get(User::USER_ID), 'token_string');
        $this->assertEquals(0, $result);
    }
    
    public function testValidateAccessTokenShouldFailGivenInvalidTokenString() {
        $this->tokenRepo->createAccessToken($this->user->get(User::USER_ID), 'token_string', 'login', '1 week', false, '127.0.0.1');
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->tokenRepo->validateAccessToken($this->user->get(User::USER_ID), 'invalid_token_string');
    }
    
    public function testValidateAccessTokenShouldFailGivenRevokedToken() {
        $this->tokenRepo->createAccessToken($this->user->get(User::USER_ID), 'token_string', 'login', '1 week', false, '127.0.0.1');
        $this->tokenRepo->revokeActiveAccessTokens($this->user->get(User::USER_ID), 'login', '127.0.0.1');
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->tokenRepo->validateAccessToken($this->user->get(User::USER_ID), 'token_string');
    }
    
    public function testValidateAccessTokenShouldFailGivenExpiredToken() {
        $this->tokenRepo->createAccessToken($this->user->get(User::USER_ID), 'token_string', 'login', '0 seconds', false, '127.0.0.1');
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->tokenRepo->validateAccessToken($this->user->get(User::USER_ID), 'token_string');
    }
}
?>
