<?php
namespace Tudu\Test\Core\Data\Model;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;
use \Tudu\Core\Chainable\Sentinel;
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
    
    public function testAllValidData() {
        $data = [
            'name' => 'John Doe',
            'email' => 'sooperdooper@abc.xyz'
        ];
        $mockModel = new MockModel($data);
        $this->assertFalse($mockModel->isNormalized());
        $errors = $mockModel->normalize();
        $this->assertTrue($mockModel->isNormalized());
        $this->assertNull($errors);
        $this->assertSame($data, $mockModel->asArray());
    }
    
    public function testAllValidDataButUnnormalized() {
        $data = [
            'name' => "   John Doe   \t",
            'email' => 'sooperdooper@abc.xyz'
        ];
        $mockModel = new MockModel($data);
        $this->assertFalse($mockModel->isNormalized());
        $errors = $mockModel->normalize();
        $this->assertTrue($mockModel->isNormalized());
        $this->assertNull($errors);
        $expected = [
            'name' => 'John Doe',
            'email' => 'sooperdooper@abc.xyz'
        ];
        $this->assertSame($expected, $mockModel->asArray());
    }
    
    public function testMixedData() {
        $mockModel = new MockModel([
            'name' => 'Jonathan Mynameis Waytoolong Andwillberejected',
            'email' => 'sooperdooper@abc.xyz'
        ]);
        $this->assertFalse($mockModel->isNormalized());
        $errors = $mockModel->normalize();
        $this->assertFalse($mockModel->isNormalized());
        $this->assertNotNull($errors);
        $this->assertTrue(!isset($errors['email']));
        $this->assertEquals('Name must be 5 to 35 characters in length.', $errors['name']);
    }

    public function testAllInvalidData() {
        $mockModel = new MockModel([
            'name' => 'Jonathan Mynameis Waytoolong Andwillberejected',
            'email' => 'sooperdooper@abc'
        ]);
        $this->assertFalse($mockModel->isNormalized());
        $errors = $mockModel->normalize();
        $this->assertFalse($mockModel->isNormalized());
        $this->assertNotNull($errors);
        $this->assertEquals('Name must be 5 to 35 characters in length.', $errors['name']);
        $this->assertEquals('Email address is invalid.', $errors['email']);
    }
    
    public function testDataWithSentinelValue() {
        $error = 'not found';
        $mockModel = new MockModel([
            'name' => 'John Doe',
            'email' => new Sentinel($error)
        ]);
        $this->assertFalse($mockModel->isNormalized());
        $errors = $mockModel->normalize();
        $this->assertFalse($mockModel->isNormalized());
        $this->assertNotNull($errors);
        $this->assertTrue(!isset($errors['name']));
        $this->assertEquals("Email address $error.", $errors['email']);
    }
    
    public function testWithFewerPropertiesThanNormalizers() {
        $mockModel = new MockModel([
            'name' => 'John Doe'
        ]);
        $this->assertFalse($mockModel->isNormalized());
        $errors = $mockModel->normalize();
        $this->assertTrue($mockModel->isNormalized());
        $this->assertNull($errors);
    }
    
    public function testNormalizerCaching() {
        $mockModel = new MockModel([]);
        $errors = $mockModel->normalize();
        $mockModel = new MockModel([]);
        $errors = $mockModel->normalize();
        $this->assertEquals(1, MockModel::getNormalizersMethodCallCount());
    }
    
    public function testSanitizerCaching() {
        $mockModel = new MockModel([]);
        $errors = $mockModel->normalize();
        $mockModel = $mockModel->getSanitizedCopy();
        $mockModel = new MockModel([]);
        $errors = $mockModel->normalize();
        $mockModel = $mockModel->getSanitizedCopy();
        $this->assertEquals(1, MockModel::getSanitizersMethodCallCount());
    }
    
    public function testRepeatedNormalize() {
        $mockModel = new MockModel([]);
        $mockModel->normalize();
        $this->assertNull($mockModel->normalize());
    }
    
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
