<?php
namespace Tudu\Test\Unit\Core\Data\Model;

use \Tudu\Core;
use \Tudu\Core\Data\Repository;
use \Tudu\Core\Data\Model\Model;
use \Tudu\Test\Mock\MockModel;
use \Tudu\Test\Mock\MockRepository;

class RepositoryTest extends \PHPUnit_Framework_TestCase {
    
    public function testPrenormalize() {
        $repo = new MockRepository();
        $model = new MockModel([
            'name' => "   John Doe   \t",
            'email' => 'foo@bar.xyz'
        ]);
        $model = $repo->publicPrenormalize($model);
        $this->assertTrue($model instanceof Model);
        $this->assertTrue($model->isNormalized());
        $expected = [
            'name' => 'John Doe',
            'email' => 'foo@bar.xyz'
        ];
        $this->assertSame($expected, $model->asArray());
    }
    
    public function testPrenormalizeWithInvalidModel() {
        $repo = new MockRepository();
        $model = new MockModel([
            'name' => 'Jonathan Mynameis Waytoolong Andwillberejected',
            'email' => 'foo@bar.xyz'
        ]);
        $error = $repo->publicPrenormalize($model);
        $this->assertTrue($error instanceof \Tudu\Core\Error);
        
        $expected = Repository\Error::Validation($model->normalize());
        $this->assertSame($expected->asArray(), $error->asArray());
    }
}
