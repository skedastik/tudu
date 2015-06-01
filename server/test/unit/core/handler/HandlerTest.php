<?php
namespace Tudu\Test\Unit\Core\Handler;

use \Tudu\Test\Mock\MockApiHandler;
use \Tudu\Test\Mock\MockApp;
use \Tudu\Test\Mock\MockModel;
use \Tudu\Core\Encoder;

class HandlerTest extends \PHPUnit_Framework_TestCase {
    
    protected $app;
    protected $db;
    protected $handler;
    
    public function setUp() {
        ob_start();
        $this->db = $this->getMockBuilder('\Tudu\Core\Database\DbConnection')->disableOriginalConstructor()->getMock();
        $this->app = new MockApp();
        $this->app->addEncoder(new Encoder\JSON());
        $this->handler = new MockApiHandler($this->app, $this->db);
        $this->app->setHandler($this->handler);
    }
    
    public function testRenderBodyWithArrayShouldOutputValidJson() {
        $data = [
            'name' => 'John Doe'
        ];
        $this->handler->publicRenderBody($data);
        $result = json_decode(ob_get_contents(), true);
        $this->assertSame($data, $result);
    }
    
    public function testRenderBodyWithArrayableShouldOutputValidJson() {
        $data = [
            'name' => 'John Doe'
        ];
        $this->handler->publicRenderBody(new MockModel($data));
        $result = json_decode(ob_get_contents(), true);
        $this->assertSame($data, $result);
    }
    
    public function tearDown() {
        ob_end_clean();
    }
}

?>
