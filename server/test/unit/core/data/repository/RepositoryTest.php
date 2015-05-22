<?php
namespace Tudu\Test\Unit\Core\Data\Model;

use \Tudu\Core\Error;
use \Tudu\Test\Mock\MockModel;
use \Tudu\Test\Mock\MockRepository;

class RepositoryTest extends \PHPUnit_Framework_TestCase {
    
    public function testPrenormalizeShouldNormalizeModelData() {
        $repo = new MockRepository();
        $model = new MockModel([
            'name' => "   John Doe   \t",
            'email' => 'foo@bar.xyz'
        ]);
        $model = $repo->publicPrenormalize($model);
        $this->assertTrue($model instanceof MockModel);
        $this->assertTrue($model->isNormalized());
        $expected = [
            'name' => 'John Doe',
            'email' => 'foo@bar.xyz'
        ];
        $this->assertSame($expected, $model->asArray());
    }
    
    public function testPrenormalizingAnInvalidModelShouldThrowAnException() {
        $repo = new MockRepository();
        $model = new MockModel([
            'name' => 'Jonathan Mynameis Waytoolong Andwillberejected',
            'email' => 'foo@bar.xyz'
        ]);
        $this->setExpectedException('\Tudu\Core\TuduException');
        $error = $repo->publicPrenormalize($model);
    }
}
