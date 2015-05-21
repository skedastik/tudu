<?php
namespace Tudu\Data\Repository;

use \Tudu\Core;
use \Tudu\Data\Model;
use \Tudu\Core\Data\Repository\Error;

final class AccessToken extends Core\Data\Repository\Repository {
    
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
            return Error::Generic('Token ID not found.');
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
     * @return mixed Token ID on success, Error object otherwise.
     */
    public function createAccessToken($userId, $tokenString, $tokenType, $ttl, $autoRevoke, $ip) {
        $result = $this->db->queryValue(
            'select tudu.create_access_token($1, $2, $3, $4, $5, $6);',
            [$userId, $tokenString, $tokenType, $ttl, $autoRevoke === true ? 't' : 'f', $ip]
        );
        switch ($result) {
            case -1:
                return Error::Generic('User ID not found.');
            case -2:
                return Error::Generic('Token string is already in use.');
        }
        return $result;
    }
    
    /**
     * Revoke active access tokens of a given type.
     * 
     * @param int $userId ID of existing user.
     * @param string $tokenType Type of access token.
     * @param string $ip IP address
     * @return mixed Number of tokens revoked on success, Error otherwise.
     */
    public function revokeActiveAccessTokens($userId, $tokenType, $ip) {
        $result = $this->db->queryValue(
            'select tudu.revoke_active_access_tokens($1, $2, $3);',
            [$userId, $tokenType, $ip]
        );
        if ($result == -1) {
            return Error::Generic('No active tokens of given type exist for given user.');
        }
        return $result;
    }
    
    /**
     * Validate an access token.
     * 
     * @param int $userId ID of existing user.
     * @param string $tokenString Access token string.
     * @return mixed 0 on success, Error object otherwise.
     */
    public function validateAccessToken($userId, $tokenString) {
        $result = $this->db->queryValue(
            'select tudu.validate_access_token($1, $2);',
            [$userId, $tokenString]
        );
        switch ($result) {
            case -1:
                return Error::Generic('No such token found for given user.');
            case -2:
                return Error::Generic('Token has been revoked.');
            case -3:
                return Error::Generic('Token is expired.');
        }
        return $result;
    }
}
?>
