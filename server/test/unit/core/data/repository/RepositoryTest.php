<?php
namespace Tudu\Test\Unit\Core\Data\Repository;

use \Tudu\Test\Mock\MockRepository;
use \Tudu\Test\Mock\MockModel;

class UserTest extends \PHPUnit_Framework_TestCase {
    
    protected function setUp() {
        $this->db = $this->getMockBuilder('\Tudu\Core\Database\DbConnection')->disableOriginalConstructor()->getMock();
        $this->repo = new MockRepository($this->db);
    }
    
    public function testFetchShouldProduceNormalizedModel() {
        $model = $this->repo->fetch(new MockModel([]));
        $this->assertTrue($model->isNormalized());
    }
    
    public function testFetchShouldThrowValidationExceptionIfInputDoesNotNormalize() {
        $this->setExpectedException('\Tudu\Core\Exception\Client');
        $this->repo->fetch(new MockModel([
            'name' => 'Jonathan Mynameis Waytoolong Andwillberejected'
        ]));
    }
}
?>
