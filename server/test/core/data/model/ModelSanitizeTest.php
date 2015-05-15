<?php
namespace Tudu\Test\Core\Data\Model;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;
use \Tudu\Core\Data\Validate\Error as Error;
use \Tudu\Test\Fixture\FakeModel;

class ModelSanitizeTest extends \PHPUnit_Framework_TestCase {
    
    public function testGetSanitizedCopy() {
        $model = new FakeModel([
            'name' => '<a href="#" >John</a> Doe<br />',
            'email' => 'sooper&dooper@abc.xyz'
        ]);
        $this->assertFalse($model->isSanitized());
        
        $errors = $model->normalize();
        $copy = $model->getSanitizedCopy();
        
        $this->assertFalse($model->isSanitized());
        $this->assertTrue($copy->isSanitized());
        $this->assertEquals('John Doe', $copy->get('name'));
        $this->assertEquals('sooper&amp;dooper@abc.xyz', $copy->get('email'));
        $this->assertEquals('<a href="#" >John</a> Doe<br />', $model->get('name'));
        $this->assertEquals('sooper&dooper@abc.xyz', $model->get('email'));
    }
    
    public function testSanitizeWithUnnormalizedData() {
        $model = new FakeModel([]);
        $this->assertFalse($model->isSanitized());
        $this->setExpectedException('\Tudu\Core\TuduException');
        $model->getSanitizedCopy();
    }
}
?>
