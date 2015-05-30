<?php
namespace Tudu\Data\Repository;

use \Tudu\Core\Data\Repository;
use \Tudu\Core\Exception;
use \Tudu\Data\Model;

final class AccessToken extends Repository {
    
    /**
     * Fetch a single access token with the given token ID.
     * 
     * @param int $id Token ID.
     * @return mixed AccessToken model on success, otherwise an Error object.
     */
    public function getByID($id) {
        $result = $this->db->query(
            'select token_id, user_id, token_string, token_type, kvs, status, edate, cdate from tudu_access_token where token_id = $1;',
            [$id]
        );
        if ($result === false) {
            throw new Exception\Client('Token not found.');
        }
        return $this->prenormalize(new Model\AccessToken($result[0]));
    }
    
    /**
     * Fetch a single access token with the given user ID and token string.
     * 
     * @param int $userId User ID.
     * @param string $tokenString Token string.
     * @return mixed AccessToken model on success, otherwise an Error object.
     */
    public function getByUserIDAndTokenString($userId, $tokenString) {
        $result = $this->db->query(
            'select token_id, user_id, token_string, token_type, kvs, status, edate, cdate from tudu_access_token where user_id = $1 and token_string = $2;',
            [$userId, $tokenString]
        );
        if ($result === false) {
            throw new Exception\Client('Token not found.');
        }
        return $this->prenormalize(new Model\AccessToken($result[0]));
    }
    
    /**
     * Create an access token.
     * 
     * @param int $userId ID of existing user.
     * @param string $tokenString Access token string.
     * @param string $tokenType Type of access token.
     * @param string $ttl Access token time to live.
     * @param bool $autoRevoke Auto-revoke active access tokens of same type.
     * @param string $ip IP address
     * @return int Token ID.
     */
    public function createAccessToken($userId, $tokenString, $tokenType, $ttl, $autoRevoke, $ip) {
        $result = $this->db->queryValue(
            'select tudu.create_access_token($1, $2, $3, $4, $5, $6);',
            [$userId, $tokenString, $tokenType, $ttl, $autoRevoke === true ? 't' : 'f', $ip]
        );
        switch ($result) {
            case -1:
                throw new Exception\Client('User ID not found.');
            case -2:
                throw new Exception\Client('Token string is already in use.');
        }
        return $result;
    }
    
    /**
     * Revoke active access tokens of a given type.
     * 
     * @param int $userId ID of existing user.
     * @param string $tokenType Type of access token.
     * @param string $ip IP address
     * @return int Number of tokens revoked.
     */
    public function revokeActiveAccessTokens($userId, $tokenType, $ip) {
        $result = $this->db->queryValue(
            'select tudu.revoke_active_access_tokens($1, $2, $3);',
            [$userId, $tokenType, $ip]
        );
        if ($result == -1) {
            throw new Exception\Client('No active tokens of given type exist for given user.');
        }
        return $result;
    }
    
    /**
     * Validate an access token.
     * 
     * @param int $userId ID of existing user.
     * @param string $tokenString Access token string.
     */
    public function validateAccessToken($userId, $tokenString) {
        $result = $this->db->queryValue(
            'select tudu.validate_access_token($1, $2);',
            [$userId, $tokenString]
        );
        switch ($result) {
            case -1:
                throw new Exception\Client('No such token found for given user.');
            case -2:
                throw new Exception\Client('Token has been revoked.');
            case -3:
                throw new Exception\Client('Token is expired.');
        }
    }
}
?>
