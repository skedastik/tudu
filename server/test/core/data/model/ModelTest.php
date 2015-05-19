<?php
namespace Tudu\Test\Core\Data\Model;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;
use \Tudu\Test\Mock\MockModel;

class ModelTest extends \PHPUnit_Framework_TestCase {
    
    public function testArrayConversion() {
        $data = [
            'name' => 'John Doe',
            'email' => 'sooperdooper@abc.xyz'
        ];
        $model = new MockModel($data);
        $this->assertSame($data, $model->asArray());
    }
    
    public function testGetSet() {
        $data = [
            'name' => 'John Doe',
            'email' => 'sooperdooper@abc.xyz'
        ];
        $model = new MockModel($data);
        $this->assertSame($data['name'], $model->get('name'));
        
        $newName = 'Jane Doe';
        $model->set('name', $newName);
        $expected = [
            'name' => $newName,
            'email' => 'sooperdooper@abc.xyz'
        ];
        $this->assertSame($expected, $model->asArray());
    }
    
    public function testMutation() {
        $model = new MockModel([
            'name' => 'John Doe',
            'email' => 'sooperdooper@abc.xyz'
        ]);
        $model->normalize();
        $model = $model->getSanitizedCopy();
        $this->assertTrue($model->isNormalized());
        $this->assertTrue($model->isSanitized());
        
        $model->fromArray([
            'name' => 'John Doe',
            'email' => 'sooperdooper@abc.xyz'
        ]);
        $this->assertFalse($model->isNormalized());
        $this->assertFalse($model->isSanitized());
        $model->normalize();
        $model = $model->getSanitizedCopy();
        $this->assertTrue($model->isNormalized());
        $this->assertTrue($model->isSanitized());
        
        $model->set('name', 'Jane Doe');
        $this->assertFalse($model->isNormalized());
        $this->assertFalse($model->isSanitized());
        $model->normalize();
        $model = $model->getSanitizedCopy();
        $this->assertTrue($model->isNormalized());
        $this->assertTrue($model->isSanitized());
    }
}
?>
