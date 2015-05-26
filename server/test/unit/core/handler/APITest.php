<?php
namespace Tudu\Test\Unit\Core\Handler;

use \Tudu\Test\Mock\MockApiHandler;
use \Tudu\Test\Mock\MockApp;
use \Tudu\Core\Encoder;
use \Tudu\Core\MediaType;

class APITest extends \PHPUnit_Framework_TestCase {
    
    protected $app;
    protected $handler;
    
    public function setUp() {
        ob_start();
        $db = $this->getMockBuilder('\Tudu\Core\Data\DbConnection')->disableOriginalConstructor()->getMock();
        $this->app = new MockApp();
        $this->app->setEncoder(new Encoder\JSON());
        $this->handler = new MockApiHandler($this->app, $db);
        $this->app->setHandler($this->handler);
    }
    
    public function testValidPostReturns201() {
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setRequestBody('{
            "name": "John Doe",
            "email": "johndoe@foo.xyz"
        }');
        $this->app->run();
        $this->assertEquals(201, $this->app->getResponseStatus());
    }
    
    public function testValidPostReturnsApplicationJsonContentType() {
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setRequestBody('{
            "name": "John Doe",
            "email": "johndoe@foo.xyz"
        }');
        $this->app->run();
        $mediaType = new MediaType($this->app->getResponseHeader('Content-Type'));
        $this->assertNotFalse($mediaType->compare(new MediaType('application/json')));
    }
    
    public function testUnsupportedRequestMethodReturns405() {
        $this->app->setRequestMethod('PUT');
        $this->app->run();
        $this->assertEquals(405, $this->app->getResponseStatus());
    }
    
    public function testUnsupportedRequestMethodReturnsAllowHeader() {
        $this->app->setRequestMethod('PUT');
        $this->app->run();
        $this->assertEquals($this->handler->getAllowedMethods(), $this->app->getResponseHeader('Allow'));
    }
    
    public function testPostReturns406GivenUnsupportedAcceptHeaders() {
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setRequestHeader('Accept', 'text/xml');
        $this->app->setRequestBody('{
            "name": "John Doe",
            "email": "johndoe@foo.xyz"
        }');
        $this->app->run();
        $this->assertEquals(406, $this->app->getResponseStatus());
    }
    
    public function testPostReturns415GivenMissingContentType() {
        $this->app->setRequestMethod('POST');
        $this->app->run();
        $this->assertEquals(415, $this->app->getResponseStatus());
    }
    
    public function testPostReturns415GivenUnsupportedContentType() {
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'text/xml');
        $this->app->run();
        $this->assertEquals(415, $this->app->getResponseStatus());
    }
    
    public function testDecodeRequestBodyReturnsNormalizedDataGivenValidRequestBody() {
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'text/xml');
        $this->app->run();
        $this->assertEquals(415, $this->app->getResponseStatus());
    }
    
    public function testDecodeRequestBodyReturns400GivenBadlyFormattedJson() {
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setRequestBody('Invalid JSON');
        $this->app->run();
        $this->assertEquals(400, $this->app->getResponseStatus());
    }
    
    public function testDecodeRequestBodyReturns400GivenIncompletePropertyList() {
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setRequestBody('{
            "name": "John Doe"
        }');
        $this->app->run();
        $this->assertEquals(400, $this->app->getResponseStatus());
    }
    
    public function testDecodeRequestBodyReturns400GivenPropertiesThatFailToValidate() {
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setRequestBody('{
            "name": "Jonathan Mynameis Waytoolong Andwillberejected",
            "email": "bad@email"
        }');
        $this->app->run();
        $this->assertEquals(400, $this->app->getResponseStatus());
    }
    
    public function tearDown() {
        ob_end_clean();
    }
}
?>
