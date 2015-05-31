<?php
namespace Tudu\Test\Unit\Core\Handler;

use \Tudu\Test\Mock\MockApiHandler;
use \Tudu\Test\Mock\MockApp;
use \Tudu\Test\Mock\MockModel;
use \Tudu\Core\Encoder;
use \Tudu\Core\MediaType;

class APITest extends \PHPUnit_Framework_TestCase {
    
    protected $app;
    protected $handler;
    
    public function setUp() {
        ob_start();
        $this->db = $this->getMockBuilder('\Tudu\Core\Database\DbConnection')->disableOriginalConstructor()->getMock();
        $this->app = new MockApp();
        $this->app->addEncoder(new Encoder\JSON());
        $this->handler = new MockApiHandler($this->app, $this->db);
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
    
    public function testPostWithWildcardAcceptHeaderReturns201() {
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Accept', '*/*');
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
    
    public function testOptionsRequestReturns200() {
        $this->app->setRequestMethod('OPTIONS');
        $this->app->run();
        $this->assertEquals(200, $this->app->getResponseStatus());
    }
    
    public function testOptionsRequestReturnsAllowHeader() {
        $this->app->setRequestMethod('OPTIONS');
        $this->app->run();
        $this->assertEquals($this->handler->getAllowedMethods(), $this->app->getResponseHeader('Allow'));
    }
    
    public function testUnsupportedRequestMethodReturns405() {
        $this->app->setRequestMethod('GET');
        $this->app->run();
        $this->assertEquals(405, $this->app->getResponseStatus());
        
        $this->app->setResponseStatus(null);
        $this->app->setRequestMethod('PATCH');
        $this->app->run();
        $this->assertEquals(405, $this->app->getResponseStatus());
        
        $this->app->setResponseStatus(null);
        $this->app->setRequestMethod('DELETE');
        $this->app->run();
        $this->assertEquals(405, $this->app->getResponseStatus());
        
        $this->app->setResponseStatus(null);
        $this->app->setRequestMethod('HEAD');
        $this->app->run();
        $this->assertEquals(405, $this->app->getResponseStatus());
    }
    
    public function testUnsupportedRequestMethodReturnsAllowHeader() {
        $this->app->setRequestMethod('GET');
        $this->app->run();
        $this->assertEquals($this->handler->getAllowedMethods(), $this->app->getResponseHeader('Allow'));
    }
    
    public function testPostReturns406GivenUnsupportedAcceptHeaders() {
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setRequestHeader('Accept', 'text/xml, application/xml');
        $this->app->setRequestBody('{
            "name": "John Doe",
            "email": "johndoe@foo.xyz"
        }');
        $this->app->run();
        $this->assertEquals(406, $this->app->getResponseStatus());
    }
    
    public function testPostReturns415GivenNonEmptyRequestBodyWithMissingContentType() {
        $this->app->setRequestMethod('POST');
        $this->app->setRequestBody('non-empty request body');
        $this->app->run();
        $this->assertEquals(415, $this->app->getResponseStatus());
    }
    
    public function testPostReturns415GivenNonEmptyRequestBodyUnsupportedContentType() {
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'text/xml');
        $this->app->setRequestBody('non-empty request body');
        $this->app->run();
        $this->assertEquals(415, $this->app->getResponseStatus());
    }
    
    public function testImportRequestDataReturns400GivenBadlyFormattedJson() {
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setRequestBody('Invalid JSON');
        $this->app->run();
        $this->assertEquals(400, $this->app->getResponseStatus());
    }
    
    public function testImportRequestDataReturns400GivenIncompletePropertyList() {
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setRequestBody('{
            "name": "John Doe"
        }');
        $this->app->run();
        $this->assertEquals(400, $this->app->getResponseStatus());
    }
    
    public function testImportRequestDataReturns400GivenRequestBodyThatFailsToValidate() {
        $this->app->setRequestMethod('POST');
        $this->app->setRequestHeader('Content-Type', 'application/json');
        $this->app->setRequestBody('{
            "name": "Jonathan Mynameis Waytoolong Andwillberejected",
            "email": "bad@email"
        }');
        $this->app->run();
        $this->assertEquals(400, $this->app->getResponseStatus());
    }
    
    public function testImportRequestDataReturnsNormalizedContextData() {
        $this->app->setContext([
            'name' => '   John Doe   '
        ]);
        $this->handler = new MockApiHandler($this->app, $this->db);
        $this->app->setHandler($this->handler);
        $this->app->setRequestMethod('PUT');
        $this->app->run();
        $responseBody = ob_get_contents();
        $this->assertEquals('John Doe', $responseBody);
    }
    
    public function testImportRequestDataReturns400GivenContextDataThatFailsToValidate() {
        $this->app->setContext([
            'name' => 'Jonathan Mynameis Waytoolong Andwillberejected'
        ]);
        $this->handler = new MockApiHandler($this->app, $this->db);
        $this->app->setHandler($this->handler);
        $this->app->setRequestMethod('PUT');
        $this->app->run();
        $this->assertEquals(400, $this->app->getResponseStatus());
    }
    
    public function tearDown() {
        ob_end_clean();
    }
}
?>
