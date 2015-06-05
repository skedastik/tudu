<?php
namespace Tudu\Test\Unit\Data\Model;

use \Tudu\Data\Model\Task;

class TaskTest extends \PHPUnit_Framework_TestCase {
    
    public function testNormalizedTaskShouldHaveNormalizedData() {
        $input = "   Buy # noodles...#phoSure.\n#food&wine #foo#bar #123\n   ";
        $data = [
            Task::DESCRIPTION => $input,
            Task::TAGS => $input
        ];
        $task = new Task($data);
        $task->normalize();
        $this->assertTrue($task->isNormalized());
        $expected = [
            Task::DESCRIPTION => "Buy # noodles...#phoSure.\n#food&wine #foo#bar #123",
            Task::TAGS => '{"phoSure", "food&wine", "foo", "bar", "123"}'
        ];
        $this->assertSame($expected, $task->asArray());
    }
}
?>
