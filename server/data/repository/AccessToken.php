<?php
namespace Tudu\Data\Repository;

use \Tudu\Core\Data\Repository;
use \Tudu\Core\Exception;
use \Tudu\Data\Model\AccessToken as AccessTokenModel;
use \Tudu\Core\Data\Model;

final class AccessToken extends Repository {
    
    /**
     * Fetch a single access token with matching ID.
     * 
     * @param \Tudu\Core\Data\Model $accessToken Access token model to match
     * against (token ID required).
     * @return \Tudu\Core\Data\Model A normalized model populated with data.
     */
    public function getByID(Model $accessToken) {
        $result = $this->db->query(
            'select token_id, user_id, token_string, token_type, kvs, status, edate, cdate from tudu_access_token where token_id = $1;',
            [$accessToken->get(AccessTokenModel::TOKEN_ID)]
        );
        if ($result === false) {
            throw new Exception\Client('Token not found.');
        }
        return new AccessTokenModel($result[0], true);
    }
    
    /**
     * Fetch a single access token with a given user ID and token string.
     * 
     * @param \Tudu\Core\Data\Model $accessToken Access token model to match
     * against (user ID and token string required).
     * @return mixed AccessToken model on success, otherwise an Error object.
     */
    public function getByUserIDAndTokenString(Model $accessToken) {
        $result = $this->db->query(
            'select token_id, user_id, token_string, token_type, kvs, status, edate, cdate from tudu_access_token where user_id = $1 and token_string = $2;',
            [
                $accessToken->get(AccessTokenModel::USER_ID),
                $accessToken->get(AccessTokenModel::TOKEN_STRING)
            ]
        );
        if ($result === false) {
            throw new Exception\Client('Token not found.');
        }
        return new AccessTokenModel($result[0], 0);
    }
    
    /**
     * Create an access token.
     * 
     * @param \Tudu\Core\Data\Model $accessToken Access token model to export
     * (user ID and token string required).
     * @param string $tokenType Type of access token.
     * @param string $ttl Access token time to live.
     * @param bool $autoRevoke Auto-revoke active access tokens of same type.
     * @param string $ip IP address
     * @return int Token ID.
     */
    public function createAccessToken(Model $accessToken, $tokenType, $ttl, $autoRevoke, $ip) {
        $result = $this->db->queryValue(
            'select tudu.create_access_token($1, $2, $3, $4, $5, $6);',
            [
                $accessToken->get(AccessTokenModel::USER_ID),
                $accessToken->get(AccessTokenModel::TOKEN_STRING),
                $tokenType,
                $ttl,
                $autoRevoke === true ? 't' : 'f',
                $ip
            ]
        );
        switch ($result) {
            // failure to create an access token is an internal error
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
     * @param \Tudu\Core\Data\Model $accessToken Access token model to match
     * against (user ID required).
     * @param string $tokenType Type of access token.
     * @param string $ip IP address
     * @return int Number of tokens revoked.
     */
    public function revokeActiveAccessTokens(Model $accessToken, $tokenType, $ip) {
        $result = $this->db->queryValue(
            'select tudu.revoke_active_access_tokens($1, $2, $3);',
            [
                $accessToken->get(AccessTokenModel::USER_ID),
                $tokenType,
                $ip
            ]
        );
        if ($result == -1) {
            throw new Exception\Client('No active tokens of given type exist for given user.');
        }
        return $result;
    }
    
    /**
     * Validate an access token of a given type.
     * 
     * @param \Tudu\Core\Data\Model $accessToken Access token model to validate
     * (user ID and token string required).
     * @return true
     */
    public function validateAccessToken(Model $accessToken) {
        // TODO: Match access token type.
        $result = $this->db->queryValue(
            'select tudu.validate_access_token($1, $2);',
            [
                $accessToken->get(AccessTokenModel::USER_ID),
                $accessToken->get(AccessTokenModel::TOKEN_STRING)
            ]
        );
        switch ($result) {
            case -1:
                throw new Exception\Client('No such token found for given user.');
            case -2:
                throw new Exception\Client('Token has been revoked.');
            case -3:
                throw new Exception\Client('Token is expired.');
        }
        return true;
    }
}
?>
