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
        $model = new User([
            User::EMAIL => 'foo@bar.com',
            User::PASSWORD => 'unlikely_pw_hash'
        ]);
        $userId = $this->userRepo->signupUser($model, '127.0.0.1', true);
        $this->user = $this->userRepo->fetch(new User([
            User::USER_ID => $userId,
        ]));
    }
    
    public function testCreateAccessTokenShouldSucceedGivenValidInputs() {
        $model = new AccessToken([
            AccessToken::USER_ID => $this->user->get(User::USER_ID),
            AccessToken::TOKEN_STRING => 'token_string',
            AccessToken::TOKEN_TYPE => AccessToken::TYPE_LOGIN,
            AccessToken::TTL => '1 week'
        ]);
        $tokenId = $this->tokenRepo->createAccessToken($model, false, '127.0.0.1');
        $this->assertTrue($tokenId >= 0);
    }
    
    public function testCreateAccessTokenShouldFailGivenInvalidUserId() {
        $model = new AccessToken([
            AccessToken::USER_ID => -1,
            AccessToken::TOKEN_STRING => 'token_string',
            AccessToken::TOKEN_TYPE => AccessToken::TYPE_LOGIN,
            AccessToken::TTL => '1 week'
        ]);
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->tokenRepo->createAccessToken($model, false, '127.0.0.1');
    }
    
    public function testCreateAccessTokenShouldFailGivenNonUniqueTokenString() {
        $model = new AccessToken([
            AccessToken::USER_ID => $this->user->get(User::USER_ID),
            AccessToken::TOKEN_STRING => 'token_string',
            AccessToken::TOKEN_TYPE => AccessToken::TYPE_LOGIN,
            AccessToken::TTL => '1 week'
        ]);
        $this->tokenRepo->createAccessToken($model, false, '127.0.0.1');
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->tokenRepo->createAccessToken($model, false, '127.0.0.1');
    }
    
    public function testRevokeActiveAccessTokensShouldSucceedGivenValidInputs() {
        $model = new AccessToken([
            AccessToken::USER_ID => $this->user->get(User::USER_ID),
            AccessToken::TOKEN_STRING => 'token_string',
            AccessToken::TOKEN_TYPE => AccessToken::TYPE_LOGIN,
            AccessToken::TTL => '1 week'
        ]);
        $this->tokenRepo->createAccessToken($model, false, '127.0.0.1');
        $revokeCount = $this->tokenRepo->revokeActiveAccessTokens($model, '127.0.0.1');
        $this->assertEquals(1, $revokeCount);
    }
    
    public function testRevokeActiveAccessTokensShouldFailIfNoActiveTokensExist() {
        $model = new AccessToken([
            AccessToken::USER_ID => $this->user->get(User::USER_ID),
            AccessToken::TOKEN_TYPE => AccessToken::TYPE_LOGIN
        ]);
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->tokenRepo->revokeActiveAccessTokens($model, '127.0.0.1');
    }
    
    public function testValidateAccessTokenShouldSucceedGivenValidInputs() {
        $model = new AccessToken([
            AccessToken::USER_ID => $this->user->get(User::USER_ID),
            AccessToken::TOKEN_STRING => 'token_string',
            AccessToken::TOKEN_TYPE => AccessToken::TYPE_LOGIN,
            AccessToken::TTL => '1 week'
        ]);
        $this->tokenRepo->createAccessToken($model, false, '127.0.0.1');
        $result = $this->tokenRepo->validateAccessToken($model, 'token_string');
        $this->assertTrue($result);
    }
    
    public function testValidateAccessTokenShouldFailGivenInvalidTokenString() {
        $model = new AccessToken([
            AccessToken::USER_ID => $this->user->get(User::USER_ID),
            AccessToken::TOKEN_STRING => 'token_string',
            AccessToken::TOKEN_TYPE => AccessToken::TYPE_LOGIN,
            AccessToken::TTL => '1 week'
        ]);
        $this->tokenRepo->createAccessToken($model, false, '127.0.0.1');
        $model->set(AccessToken::TOKEN_STRING, 'mismatched_token_string');
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->tokenRepo->validateAccessToken($model);
    }
    
    public function testValidateAccessTokenShouldFailGivenMismatchedTokenType() {
        $model = new AccessToken([
            AccessToken::USER_ID => $this->user->get(User::USER_ID),
            AccessToken::TOKEN_STRING => 'token_string',
            AccessToken::TOKEN_TYPE => AccessToken::TYPE_LOGIN,
            AccessToken::TTL => '1 week'
        ]);
        $this->tokenRepo->createAccessToken($model, false, '127.0.0.1');
        $model->set(AccessToken::TOKEN_TYPE, AccessToken::TYPE_PASSWORD_RESET);
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->tokenRepo->validateAccessToken($model);
    }
    
    public function testValidateAccessTokenShouldFailGivenRevokedToken() {
        $model = new AccessToken([
            AccessToken::USER_ID => $this->user->get(User::USER_ID),
            AccessToken::TOKEN_STRING => 'token_string',
            AccessToken::TOKEN_TYPE => AccessToken::TYPE_LOGIN,
            AccessToken::TTL => '1 week'
        ]);
        $this->tokenRepo->createAccessToken($model, false, '127.0.0.1');
        $this->tokenRepo->revokeActiveAccessTokens($model, '127.0.0.1');
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->tokenRepo->validateAccessToken($model);
    }
    
    public function testValidateAccessTokenShouldFailGivenExpiredToken() {
        $model = new AccessToken([
            AccessToken::USER_ID => $this->user->get(User::USER_ID),
            AccessToken::TOKEN_STRING => 'token_string',
            AccessToken::TOKEN_TYPE => AccessToken::TYPE_LOGIN,
            AccessToken::TTL => '0 seconds'
        ]);
        $this->tokenRepo->createAccessToken($model, false, '127.0.0.1');
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->tokenRepo->validateAccessToken($model);
    }
}
?>
