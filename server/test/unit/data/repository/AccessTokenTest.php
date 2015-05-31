<?php
namespace Tudu\Test\Unit\Data\Repository;

use \Tudu\Data\Repository;
use \Tudu\Data\Model\AccessToken;

class AccessTokenTest extends \PHPUnit_Framework_TestCase {

    protected function setUp() {
        $this->db = $this->getMockBuilder('\Tudu\Core\Database\DbConnection')->disableOriginalConstructor()->getMock();
        $this->repo = new Repository\AccessToken($this->db);
    }

    public function testGetByIDShouldProduceNormalizedModel() {
        $mockResult = [[
            AccessToken::TOKEN_ID => '123',
            AccessToken::USER_ID  => '456'
        ]];
        $this->db->method('query')->willReturn($mockResult);
        $accessToken = $this->repo->getByID(new AccessToken([
            AccessToken::TOKEN_ID => 123
        ]));
        $this->assertTrue($accessToken->isNormalized());
    }

    public function testGetByIDShouldGenerateResourceNotFoundErrorIfQueryFails() {
        $this->db->method('query')->willReturn(false);
        $id = 123;
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->repo->getByID(new AccessToken([
            AccessToken::TOKEN_ID => $id
        ]));
    }
}
?>
