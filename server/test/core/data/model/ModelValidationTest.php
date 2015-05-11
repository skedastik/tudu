<?php
namespace Tudu\Test\Core\Data\Model;

use \Tudu\Core\Data\Validate;
use \Tudu\Core\Data\Validate\Sentinel;

class FakeModel extends \Tudu\Core\Data\Model\Model {
    
    protected function getValidationMatrix() {
        return [
            'name'  => Validate\String()->length()->from(5)->upTo(10),
            'email' => Validate\Email()
        ];
    }
}

class ModelValidationTest extends \PHPUnit_Framework_TestCase {
    
    public function testAllValidData() {
        $fakeModel = new FakeModel([
            'name' => 'John Doe',
            'email' => 'sooperdooper@abc.xyz'
        ]);
        $this->assertFalse($fakeModel->isValid());
        $errors = $fakeModel->validate();
        $this->assertTrue($fakeModel->isValid());
        $this->assertNull($errors);
    }
    
    public function testMixedData() {
        $fakeModel = new FakeModel([
            'name' => 'Jonathan Nametoolong',
            'email' => 'sooperdooper@abc.xyz'
        ]);
        $this->assertFalse($fakeModel->isValid());
        $errors = $fakeModel->validate();
        $this->assertFalse($fakeModel->isValid());
        $this->assertNotNull($errors);
        $this->assertNotNull($errors['name']);
        $this->assertNull($errors['email']);
    }
    
    public function testAllInvalidData() {
        $fakeModel = new FakeModel([
            'name' => 'Jonathan Nametoolong',
            'email' => 'sooperdooper@abc'
        ]);
        $this->assertFalse($fakeModel->isValid());
        $errors = $fakeModel->validate();
        $this->assertFalse($fakeModel->isValid());
        $this->assertNotNull($errors);
        $this->assertNotNull($errors['name']);
        $this->assertNotNull($errors['email']);
    }
    
    public function testDataWithSentinelValue() {
        $fakeModel = new FakeModel([
            'name' => 'John Doe',
            'email' => new Sentinel\NotFound()
        ]);
        $this->assertFalse($fakeModel->isValid());
        $errors = $fakeModel->validate();
        $this->assertFalse($fakeModel->isValid());
        $this->assertNotNull($errors);
        $this->assertNull($errors['name']);
        $this->assertNotNull($errors['email']);
    }
}
?>
