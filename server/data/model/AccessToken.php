<?php
namespace Tudu\Data\Model;

use \Tudu\Core\Data\Model;
use \Tudu\Core\Data\Transform\Transform;
use \Tudu\Core\Data\Validate\Validate;

/**
 * Access token model.
 */
final class AccessToken extends Model {
    
    const TOKEN_ID     = 'token_id';
    const USER_ID      = 'user_id';
    const TOKEN_STRING = 'token_string';
    const TTL          = 'ttl';
    
    protected function getNormalizers() {
        return [
            self::TOKEN_ID => Transform::Convert()->to()->integer(),
            
            self::USER_ID => Transform::Convert()->to()->integer()
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
