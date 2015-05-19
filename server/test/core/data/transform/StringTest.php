<?php
namespace Tudu\Test\Core\Data\Transform;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Chainable\Sentinel;

class StringTest extends \PHPUnit_Framework_TestCase {

    public function testEscapeForHTML() {
        $transformer = Transform::String()->escapeForHTML();
        $this->assertEquals('this &amp; that', $transformer->execute('this & that'));
    }
    
    public function testStripTags() {
        $transformer = Transform::String()->stripTags();
        $this->assertEquals(
            'this and that',
            $transformer->execute('<p><a href="#">this</a> and that</p><br />')
        );
    }
    
    public function testTrim() {
        $transformer = Transform::String()->trim();
        $this->assertEquals('foo', $transformer->execute('foo'));
        $this->assertEquals('foo', $transformer->execute("foo \n\t\r"));
        $this->assertEquals('foo', $transformer->execute("\n\t\r foo"));
        $this->assertEquals('foo bar', $transformer->execute("\n\t\r      foo bar      \n\t\r"));
    }

    public function testStringTransformerWithNonStringInput() {
        $transformer = Transform::String();
        $this->setExpectedException('\Tudu\Core\TuduException');
        $transformer->execute(1);
    }
}
  
?>
