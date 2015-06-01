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
    const TOKEN_TYPE   = 'token_type';
    const TTL          = 'ttl';
    
    // access token types
    const TYPE_LOGIN = 'login';
    
    protected function getNormalizers() {
        return [
            self::TOKEN_ID => Transform::Convert()->to()->integer(),
            
            self::USER_ID => Transform::Convert()->to()->integer(),
            
            self::TOKEN_STRING => Transform::Convert()->to()->string()
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
