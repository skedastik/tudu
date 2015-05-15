<?php
namespace Tudu\Test\Core\Data\Model;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;
use \Tudu\Core\Data\Validate\Error as Error;
use \Tudu\Core\Chainable\Sentinel;

class FakeModel extends \Tudu\Core\Data\Model\Model {
    
    protected function getNormalizers() {
        return [
            'name'  => Validate::String()->length()->from(5)->upTo(10)
                    -> then(Transform::Description()->to('Name')),
                        
            'email' => Validate::Email()
                    -> then(Transform::Description()->to('Email address'))
        ];
    }
}

class ModelNormalizeTest extends \PHPUnit_Framework_TestCase {
    
    public function testAllValidData() {
        $fakeModel = new FakeModel([
            'name' => 'John Doe',
            'email' => 'sooperdooper@abc.xyz'
        ]);
        $this->assertFalse($fakeModel->isNormalized());
        $errors = $fakeModel->normalize();
        $this->assertTrue($fakeModel->isNormalized());
        $this->assertNull($errors);
    }
    
    public function testMixedData() {
        $fakeModel = new FakeModel([
            'name' => 'Jonathan Nametoolong',
            'email' => 'sooperdooper@abc.xyz'
        ]);
        $this->assertFalse($fakeModel->isNormalized());
        $errors = $fakeModel->normalize();
        $this->assertFalse($fakeModel->isNormalized());
        $this->assertNotNull($errors);
        $this->assertTrue(!isset($errors['email']));
        $this->assertEquals('Name must be 5 to 10 characters in length.', $errors['name']);
    }

    public function testAllInvalidData() {
        $fakeModel = new FakeModel([
            'name' => 'Jonathan Nametoolong',
            'email' => 'sooperdooper@abc'
        ]);
        $this->assertFalse($fakeModel->isNormalized());
        $errors = $fakeModel->normalize();
        $this->assertFalse($fakeModel->isNormalized());
        $this->assertNotNull($errors);
        $this->assertEquals('Name must be 5 to 10 characters in length.', $errors['name']);
        $this->assertEquals('Email address is invalid.', $errors['email']);
    }
    
    public function testDataWithSentinelValue() {
        $fakeModel = new FakeModel([
            'name' => 'John Doe',
            'email' => new Sentinel(Error::NOT_FOUND)
        ]);
        $this->assertFalse($fakeModel->isNormalized());
        $errors = $fakeModel->normalize();
        $this->assertFalse($fakeModel->isNormalized());
        $this->assertNotNull($errors);
        $this->assertTrue(!isset($errors['name']));
        $this->assertEquals('Email address not found.', $errors['email']);
    }
}
?>
