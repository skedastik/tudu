<?php
namespace Tudu\Core\Data\Model;

use \Tudu\Core\Data\Transform;
use \Tudu\Core\Data\Validate;

/**
 * User model.
 */
final class User extends Model\Model {
    
    protected function getNormalizationMatrix() {
        // TODO user_id, email, password_salt, password_hash, kvs, status, edate, cdate
        return [
            'email' = (new Validate\Email())
                ->then((new Validate\String())->length()->from(5)->upTo(64)),
            
            'password_salt' = (new Validate\String())->length()->from(8)->upTo(64),
            
            'password_hash' = (new Validate\String())->length()->from(8)->upTo(256)
            
            /* TODO */
        ];
    }
}
?>
