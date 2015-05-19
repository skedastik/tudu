<?php
namespace Tudu\Test\Core\Data\Model;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;
use \Tudu\Core\Data\Validate\Error as Error;
use \Tudu\Test\Mock\MockModel;

class ModelSanitizeTest extends \PHPUnit_Framework_TestCase {
    
    public function testGetSanitizedCopy() {
        $data = [
            'name' => '<a href="#" >John</a> Doe<br />',
            'email' => 'sooper&dooper@abc.xyz'
        ];
        $model = new MockModel($data);
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
        $model = new MockModel([]);
        $this->assertFalse($model->isSanitized());
        $this->setExpectedException('\Tudu\Core\TuduException');
        $model->getSanitizedCopy();
    }
}
?>
