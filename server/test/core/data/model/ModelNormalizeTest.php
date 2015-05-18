<?php
namespace Tudu\Test\Core\Data\Model;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;
use \Tudu\Core\Chainable\Sentinel;
use \Tudu\Test\Fixture\FakeModel;

class ModelNormalizeTest extends \PHPUnit_Framework_TestCase {
    
    public function testAllValidData() {
        $data = [
            'name' => 'John Doe',
            'email' => 'sooperdooper@abc.xyz'
        ];
        $fakeModel = new FakeModel($data);
        $this->assertFalse($fakeModel->isNormalized());
        $errors = $fakeModel->normalize();
        $this->assertTrue($fakeModel->isNormalized());
        $this->assertNull($errors);
        $this->assertSame($data, $fakeModel->asArray());
    }
    
    public function testAllValidDataButUnnormalized() {
        $data = [
            'name' => "   John Doe   \t",
            'email' => 'sooperdooper@abc.xyz'
        ];
        $fakeModel = new FakeModel($data);
        $this->assertFalse($fakeModel->isNormalized());
        $errors = $fakeModel->normalize();
        $this->assertTrue($fakeModel->isNormalized());
        $this->assertNull($errors);
        $expected = [
            'name' => 'John Doe',
            'email' => 'sooperdooper@abc.xyz'
        ];
        $this->assertSame($expected, $fakeModel->asArray());
    }
    
    public function testMixedData() {
        $fakeModel = new FakeModel([
            'name' => 'Jonathan Mynameis Waytoolong Andwillberejected',
            'email' => 'sooperdooper@abc.xyz'
        ]);
        $this->assertFalse($fakeModel->isNormalized());
        $errors = $fakeModel->normalize();
        $this->assertFalse($fakeModel->isNormalized());
        $this->assertNotNull($errors);
        $this->assertTrue(!isset($errors['email']));
        $this->assertEquals('Name must be 5 to 35 characters in length.', $errors['name']);
    }

    public function testAllInvalidData() {
        $fakeModel = new FakeModel([
            'name' => 'Jonathan Mynameis Waytoolong Andwillberejected',
            'email' => 'sooperdooper@abc'
        ]);
        $this->assertFalse($fakeModel->isNormalized());
        $errors = $fakeModel->normalize();
        $this->assertFalse($fakeModel->isNormalized());
        $this->assertNotNull($errors);
        $this->assertEquals('Name must be 5 to 35 characters in length.', $errors['name']);
        $this->assertEquals('Email address is invalid.', $errors['email']);
    }
    
    public function testDataWithSentinelValue() {
        $error = 'not found';
        $fakeModel = new FakeModel([
            'name' => 'John Doe',
            'email' => new Sentinel($error)
        ]);
        $this->assertFalse($fakeModel->isNormalized());
        $errors = $fakeModel->normalize();
        $this->assertFalse($fakeModel->isNormalized());
        $this->assertNotNull($errors);
        $this->assertTrue(!isset($errors['name']));
        $this->assertEquals("Email address $error.", $errors['email']);
    }
    
    public function testWithFewerPropertiesThanNormalizers() {
        $fakeModel = new FakeModel([
            'name' => 'John Doe'
        ]);
        $this->assertFalse($fakeModel->isNormalized());
        $errors = $fakeModel->normalize();
        $this->assertTrue($fakeModel->isNormalized());
        $this->assertNull($errors);
    }
}
?>
