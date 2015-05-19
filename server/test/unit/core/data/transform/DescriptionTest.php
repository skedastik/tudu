<?php
namespace Tudu\Test\Unit\Core\Data\Transform;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Chainable\Sentinel;

class DescriptionTest extends \PHPUnit_Framework_TestCase {
    
    public function testDescription() {
        $transformer = Transform::Description()->to('Test thing');
        $error = 'not found';
        $input = new Sentinel($error);
        $this->assertEquals("Test thing $error.", $transformer->execute($input)->getValue());
    }
    
    public function testDescriptionWithNonSentinelInput() {
        $transformer = Transform::Description()->to('Test thing');
        $input = 'input';
        $this->assertEquals('input', $transformer->execute($input));
    }
}  
?>
