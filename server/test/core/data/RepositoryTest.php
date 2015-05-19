<?php
namespace Tudu\Test\Core\Data\Model;

use \Tudu\Core;
use \Tudu\Core\Data\Repository;
use \Tudu\Core\Data\Model\Model;
use \Tudu\Test\Fixture\FakeModel;
use \Tudu\Test\Fixture\FakeRepository;

class RepositoryTest extends \PHPUnit_Framework_TestCase {
    
    public function testPrenormalize() {
        $repo = new FakeRepository();
        $model = new FakeModel([
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
        $repo = new FakeRepository();
        $model = new FakeModel([
            'name' => 'Jonathan Mynameis Waytoolong Andwillberejected',
            'email' => 'foo@bar.xyz'
        ]);
        $error = $repo->publicPrenormalize($model);
        $this->assertTrue($error instanceof \Tudu\Core\Error);
        
        $expected = Repository\Error::Validation($model->normalize());
        $this->assertSame($expected->asArray(), $error->asArray());
    }
}
