<?php
namespace Tudu\Data\Model;

use \Tudu\Core\Data\Model;
use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;
use \Tudu\Core\Chainable\ForEvery;

/**
 * Task model.
 */
final class Task extends Model {
    
    // column/field names
    const TASK_ID       = 'task_id';
    const USER_ID       = 'user_id';
    const DESCRIPTION   = 'description';
    const TAGS          = 'tags';
    const FINISHED_DATE = 'finished_date';
    
    protected function getNormalizers() {
        return [
            self::TASK_ID => Transform::Convert()->to()->integer(),
            
            self::DESCRIPTION => Transform::Convert()->to()->string()
                              -> then(Transform::String()->trim())
                              -> then(Validate::String()->length()->upTo(256))
                              -> then(Transform::Description()->to('Task description')),
            
            // extract tags from input string as PostgreSQL array
            self::TAGS => Transform::Convert()->to()->string()
                       -> then(Transform::Extract()->hashtags()->asArray())
                       -> then(ForEvery::Element(
                               Validate::String()->length()->upTo(64)))
                       -> then(Transform::Convert()->to()->pgSqlArray())
                       -> then(Transform::Description()->to('Tags'))
        ];
    }
    
    protected function getSanitizers() {
        return [];
    }
    
    protected static $propertyAliases = [
        self::USER_ID => self::TASK_ID
    ];
}
?>
