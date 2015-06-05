<?php
namespace Tudu\Test\Unit\Data\Model;

use \Tudu\Core\Chainable\Sentinel;
use \Tudu\Data\Model\AccessToken;

class AccessTokenTest extends \PHPUnit_Framework_TestCase {
    
    public function testNormalizedAccessTokenShouldHaveNormalizedData() {
        $data = [
            AccessToken::TOKEN_ID => '123',
            AccessToken::USER_ID  => '456'
        ];
        $accessToken = new AccessToken($data);
        $accessToken->normalize();
        $this->assertTrue($accessToken->isNormalized());
        $expected = [
            AccessToken::TOKEN_ID => 123,
            AccessToken::USER_ID  => 456
        ];
        $this->assertSame($expected, $accessToken->asArray());
    }
}
?>
