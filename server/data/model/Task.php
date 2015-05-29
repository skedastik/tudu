<?php
namespace Tudu\Data\Model;

use \Tudu\Core\Data\Model;
use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;

/**
 * Task model.
 */
final class Task extends Model {
    
    const TASK_ID = 'task_id';
    
    protected function getNormalizers() {
        return [];
    }
    
    protected function getSanitizers() {
        return [];
    }
}
?>
