<?php
namespace Tudu\Test\Core\Data\Model;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;
use \Tudu\Core\Data\Validate\Error as Error;
use \Tudu\Test\Fixture\FakeModel;

class ModelSanitizeTest extends \PHPUnit_Framework_TestCase {
    
    public function testGetSanitizedCopy() {
        $data = [
            'name' => '<a href="#" >John</a> Doe<br />',
            'email' => 'sooper&dooper@abc.xyz'
        ];
        $model = new FakeModel($data);
        $this->assertFalse($model->isSanitized());
        
        $errors = $model->normalize();
        $copy = $model->getSanitizedCopy();
        
        $this->assertFalse($model->isSanitized());
        $this->assertTrue($copy->isSanitized());
        
        $expected = [
            'name' => 'John Doe',
            'email' => 'sooper&amp;dooper@abc.xyz'
        ];
        $this->assertSame($expected, $copy->asArray());
        $this->assertSame($data, $model->asArray());
    }
    
    public function testSanitizeWithUnnormalizedData() {
        $model = new FakeModel([]);
        $this->assertFalse($model->isSanitized());
        $this->setExpectedException('\Tudu\Core\TuduException');
        $model->getSanitizedCopy();
    }
}
?>
