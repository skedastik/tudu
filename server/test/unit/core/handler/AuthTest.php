<?php
namespace Tudu\Test\Unit\Core\Handler;

use \Tudu\Conf\Conf;
use \Tudu\Core\Handler\Auth\Auth;
use \Tudu\Test\Mock\MockAuthentication;
use \Tudu\Test\Mock\MockAuthorization;
use \Tudu\Test\Mock\MockApp;
use \Tudu\Test\Mock\MockModel;
use \Tudu\Core\MediaType;

class AuthTest extends \PHPUnit_Framework_TestCase {
    
    protected $app;
    protected $handler;
    
    public function setUp() {
        ob_start();
        $this->db = $this->getMockBuilder('\Tudu\Core\Data\DbConnection')->disableOriginalConstructor()->getMock();
        $this->app = new MockApp();
        $this->authentication = new MockAuthentication();
        $this->authorization = new MockAuthorization();
        $this->handler = new Auth($this->app, $this->db, $this->authentication, $this->authorization);
        $this->app->setHandler($this->handler);
    }
    
    public function testOmittingAuthorizationHeaderReturns401() {
        $this->app->run();
        $this->assertEquals(401, $this->app->getResponseStatus());
    }
    
    public function testOmittingAuthorizationHeaderReturnsWWWAuthenticateResponseHeader() {
        $this->app->run();
        $wwwAuthHeader = $this->app->getResponseHeader('WWW-Authenticate');
        $expected = $this->authentication->getScheme().' realm="'.Conf::AUTHENTICATION_REALM.'"';
        $this->assertEquals($expected, $wwwAuthHeader);
    }
    
    public function testMalformedAuthorizationHeaderReturns401() {
        $this->app->setRequestHeader('Authorization', 'foo');
        $this->app->run();
        $this->assertEquals(401, $this->app->getResponseStatus());
    }
    
    public function testInvalidAuthorizationSchemeReturns401() {
        $this->app->setRequestHeader('Authorization', 'Invalid-Scheme Param');
        $this->app->run();
        $this->assertEquals(401, $this->app->getResponseStatus());
    }
    
    public function testInauthenticCredentialsReturn401() {
        $this->app->setRequestHeader('Authorization', 'Test Melissa');
        $this->app->run();
        $this->assertEquals(401, $this->app->getResponseStatus());
    }
    
    public function testUnauthorizedUserReturns403() {
        $this->app->setRequestHeader('Authorization', 'Test Wendy');
        $this->app->run();
        $this->assertEquals(403, $this->app->getResponseStatus());
    }
    
    public function testAuthFailureReturnsJsonResponse() {
        $this->app->setRequestHeader('Authorization', 'foo');
        $this->app->run();
        $mediaTypeString = $this->app->getResponseHeader('Content-Type');
        $this->assertFalse(empty($mediaTypeString));
        $mediaType = new MediaType($mediaTypeString);
        $this->assertNotFalse($mediaType->compare(new MediaType('application/json')));
    }
    
    public function testValidAuthorizationHeaderReturns200() {
        $this->app->setRequestHeader('Authorization', 'Test Jane');
        $this->app->run();
        $this->assertEquals(200, $this->app->getResponseStatus());
    }
    
    public function tearDown() {
        ob_end_clean();
    }
}
?>
