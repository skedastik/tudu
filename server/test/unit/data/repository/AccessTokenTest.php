<?php
namespace Tudu\Test\Unit\Data\Repository;

use \Tudu\Data\Repository;
use \Tudu\Data\Model\AccessToken;
use \Tudu\Core\Error;

class AccessTokenTest extends \PHPUnit_Framework_TestCase {

    protected function setUp() {
        $this->db = $this->getMockBuilder('\Tudu\Core\Data\DbConnection')->disableOriginalConstructor()->getMock();
        $this->repo = new Repository\AccessToken($this->db);
    }

    public function testGetByIDShouldProduceNormalizedModel() {
        $mockResult = [[
            'token_id' => '123',
            'user_id'  => '456'
        ]];
        $expected = [
            'token_id' => 123,
            'user_id'  => 456
        ];
        $this->db->method('query')->willReturn($mockResult);
        $accessToken = $this->repo->getByID(123);
        $this->assertTrue($accessToken instanceof AccessToken);
        $this->assertTrue($accessToken->isNormalized());
        $this->assertSame($expected, $accessToken->asArray());
    }

    public function testGetByIDShouldGenerateResourceNotFoundErrorIfQueryFails() {
        $this->db->method('query')->willReturn(false);
        $id = 123;
        $error = $this->repo->getByID($id);
        $this->assertTrue($error instanceof Error);
        $this->assertEquals(Error::GENERIC, $error->getError());
    }
}
?>
