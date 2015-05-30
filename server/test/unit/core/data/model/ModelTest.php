<?php
namespace Tudu\Test\Unit\Core\Data\Model;

use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;
use \Tudu\Core\Chainable\Sentinel;
use \Tudu\Test\Mock\MockModel;

class ModelTest extends \PHPUnit_Framework_TestCase {
    
    public function testModelToArrayConversionShouldWorkBothWays() {
        $data = [
            'name' => 'John Doe',
            'email' => 'sooperdooper@abc.xyz'
        ];
        $model = new MockModel($data);
        $this->assertSame($data, $model->asArray());
    }
    
    public function testGettersAndSettersShouldWork() {
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
    
    public function testHasPropertiesShouldReturnTrueGivenExistingProperties() {
        $model = new MockModel([
            'name' => 'John Doe',
            'email' => 'sooperdooper@abc.xyz'
        ]);
        $this->assertTrue($model->hasProperties(['name']));
        $this->assertTrue($model->hasProperties(['email']));
        $this->assertTrue($model->hasProperties(['name', 'email']));
    }
    
    public function testHasPropertiesShouldReturnFalseGivenNonexistentProperties() {
        $model = new MockModel([
            'name' => 'John Doe',
            'email' => 'sooperdooper@abc.xyz'
        ]);
        $this->assertFalse($model->hasProperties(['foo']));
        $this->assertFalse($model->hasProperties(['name', 'foo']));
    }
    
    public function testMutationShouldInvalidateModel() {
        $model = new MockModel([
            'name' => 'John Doe',
            'email' => 'sooperdooper@abc.xyz'
        ]);
        $model->normalize();
        $model = $model->getSanitizedCopy('name-only');
        $this->assertTrue($model->isNormalized());
        $this->assertTrue($model->isSanitized());
        
        $model->fromArray([
            'name' => 'John Doe',
            'email' => 'sooperdooper@abc.xyz'
        ]);
        $this->assertFalse($model->isNormalized());
        $this->assertFalse($model->isSanitized());
        $model->normalize();
        $model = $model->getSanitizedCopy('name-only');
        $this->assertTrue($model->isNormalized());
        $this->assertTrue($model->isSanitized());
        
        $model->set('name', 'Jane Doe');
        $this->assertFalse($model->isNormalized());
        $this->assertFalse($model->isSanitized());
        $model->normalize();
        $model = $model->getSanitizedCopy('name-only');
        $this->assertTrue($model->isNormalized());
        $this->assertTrue($model->isSanitized());
    }
    
    public function testNormalizingValidDataShouldProduceNoErrors() {
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
    
    public function testValidDataShouldBeNormalizedAfterNormalization() {
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
    
    public function testNormalizingMixedValidAndInvalidDataShouldProduceSomeErrors() {
        $mockModel = new MockModel([
            'name' => 'Jonathan Mynameis Waytoolong Andwillberejected',
            'email' => 'sooperdooper@abc.xyz'
        ]);
        $this->assertFalse($mockModel->isNormalized());
        $errors = $mockModel->normalize();
        $this->assertFalse($mockModel->isNormalized());
        $this->assertNotNull($errors);
        $this->assertTrue(!isset($errors['email']));
        $this->assertTrue(isset($errors['name']));
    }

    public function testNormalizingInvalidDataShouldProduceOnlyErrors() {
        $mockModel = new MockModel([
            'name' => 'Jonathan Mynameis Waytoolong Andwillberejected',
            'email' => 'sooperdooper@abc'
        ]);
        $this->assertFalse($mockModel->isNormalized());
        $errors = $mockModel->normalize();
        $this->assertFalse($mockModel->isNormalized());
        $this->assertNotNull($errors);
        $this->assertTrue(isset($errors['name']));
        $this->assertTrue(isset($errors['email']));
    }
    
    public function testNormalizingSentinelsShouldProduceAnError() {
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
    
    public function testNormalizingWithFewerPropertiesThanNormalizersShouldWork() {
        $mockModel = new MockModel([
            'name' => 'John Doe'
        ]);
        $this->assertFalse($mockModel->isNormalized());
        $errors = $mockModel->normalize();
        $this->assertTrue($mockModel->isNormalized());
        $this->assertNull($errors);
    }
    
    public function testNormalizersShouldBeCached() {
        $mockModel = new MockModel([]);
        $mockModel->normalize();
        $mockModel = new MockModel([]);
        $mockModel->normalize();
        $this->assertEquals(1, MockModel::getNormalizersMethodCallCount());
    }
    
    public function testSanitizersShouldBeCached() {
        $mockModel = new MockModel([]);
        $mockModel->normalize();
        $mockModel = $mockModel->getSanitizedCopy('name-only');
        $mockModel = new MockModel([]);
        $mockModel->normalize();
        $mockModel = $mockModel->getSanitizedCopy('email-only');
        $this->assertEquals(1, MockModel::getSanitizersMethodCallCount());
    }
    
    public function testNormalizationShouldBeIdempotent() {
        $mockModel = new MockModel([]);
        $this->assertNull($mockModel->normalize());
        $this->assertNull($mockModel->normalize());
    }
    
    public function testIsSanitizedShouldReturnTrueIfSanitizedFalseOtherwise() {
        $data = [
            'name' => '<a href="#" >John</a> Doe<br />',
            'email' => 'sooper&dooper@abc.xyz'
        ];
        $model = new MockModel($data);
        $this->assertFalse($model->isSanitized());
        $model->normalize();
        $copy = $model->getSanitizedCopy('name-only');
        $this->assertFalse($model->isSanitized());
        $this->assertTrue($copy->isSanitized());
    }
    
    public function testSanitizedCopyShouldNotMutateOriginalModel() {
        $data = [
            'name' => '<a href="#" >John</a> Doe<br />',
            'email' => 'sooper&dooper@abc.xyz'
        ];
        $model = new MockModel($data);
        $model->normalize();
        $model->getSanitizedCopy('name-only');
        $this->assertSame($data, $model->asArray());
    }
    
    public function testSanitizedCopyShouldUseChosenSanitizationScheme() {
        $data = [
            'name' => '<a href="#" >John</a> Doe<br />',
            'email' => 'sooper&dooper@abc.xyz'
        ];
        $model = new MockModel($data);
        $model->normalize();
        $copy = $model->getSanitizedCopy('name-only');
        $expected = [
            'name' => 'John Doe',
            'email' => 'sooper&dooper@abc.xyz'
        ];
        $this->assertSame($expected, $copy->asArray());
        
        $model = new MockModel($data);
        $model->normalize();
        $copy = $model->getSanitizedCopy('email-only');
        $expected = [
            'name' => '<a href="#" >John</a> Doe<br />',
            'email' => 'sooper&amp;dooper@abc.xyz'
        ];
        $this->assertSame($expected, $copy->asArray());
    }
    
    public function testSanitizingAModelThatHasNotBeenNormalizedShouldThrowAnException() {
        $model = new MockModel([]);
        $this->setExpectedException('\Tudu\Core\TuduException');
        $model->getSanitizedCopy('name-only');
    }
    
    public function testSanitizingAModelUsingANonexistentSchemeShouldThrowAnException() {
        $model = new MockModel([]);
        $model->normalize();
        $this->setExpectedException('\Tudu\Core\TuduException');
        $model->getSanitizedCopy('nonexistent-scheme');
    }
    
    public function testUsingANonexistentSanitizationSchemeShouldThrowAnException() {
        $model = new MockModel([]);
        $this->setExpectedException('\Tudu\Core\TuduException');
        $model->getSanitizedCopy('nonexistent-scheme');
    }
    
    public function testPropertyAliasesShouldAlsoBeNormalized() {
        $data = [
            'first_name' => "   Johnny   \t",
            'middle_name' => "\n   Comme   ",
            'last_name' => "   Lately    ",
        ];
        $mockModel = new MockModel($data);
        $mockModel->normalize();
        $expected = [
            'first_name' => 'Johnny',
            'middle_name' => 'Comme',
            'last_name' => 'Lately',
        ];
        $this->assertSame($expected, $mockModel->asArray());
    }
    
    public function testPropertyAliasesShouldAlsoBeSanitized() {
        $data = [
            'first_name' => '<a href="#" >Johnny</a><br />',
            'middle_name' => 'Comme<a href="#" ></a><br />',
            'last_name' => '<a href="#" ></a>Lately<br />',
        ];
        $mockModel = new MockModel($data);
        $mockModel->normalize();
        $sanitizedCopy = $mockModel->getSanitizedCopy('name-only');
        $expected = [
            'first_name' => 'Johnny',
            'middle_name' => 'Comme',
            'last_name' => 'Lately',
        ];
        $this->assertSame($expected, $sanitizedCopy->asArray());
    }
}
?>
