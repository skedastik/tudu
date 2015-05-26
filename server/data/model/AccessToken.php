<?php
namespace Tudu\Data\Model;

use \Tudu\Core\Data\Model;
use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;

/**
 * Access token model.
 */
final class AccessToken extends Model {
    
    protected function getNormalizers() {
        return [
            'token_id' => Transform::Convert()->to()->integer(),
            
            'user_id' => Transform::Convert()->to()->integer()
        ];
    }
    
    protected function getSanitizers() {
        return [];
    }
    
    /**
     * Generate a random access token string.
     * 
     * @return string
     */
    public static function generateTokenString() {
        return sha1(openssl_random_pseudo_bytes(20));
    }
}
?>
