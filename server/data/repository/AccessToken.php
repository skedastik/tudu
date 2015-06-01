<?php
namespace Tudu\Data\Repository;

use \Tudu\Core\Data\Repository;
use \Tudu\Core\Exception;
use \Tudu\Data\Model\AccessToken as AccessTokenModel;
use \Tudu\Core\Data\Model;

final class AccessToken extends Repository {
    
    /**
     * Fetch a single access token with matching token ID.
     * 
     * @param \Tudu\Core\Data\Model $accessToken Access token model to match
     * against (token ID required).
     * @return \Tudu\Core\Data\Model A normalized model populated with data.
     */
    public function fetch(Model $accessToken) {
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
     * Create an access token.
     * 
     * @param \Tudu\Core\Data\Model $accessToken Access token model to export
     * (user ID, token string, token type, and TTL required).
     * @param string $tokenType Type of access token.
     * @param string $ttl Access token time to live.
     * @param bool $autoRevoke Auto-revoke active access tokens of same type.
     * @param string $ip IP address
     * @return int Token ID.
     */
    public function createAccessToken(Model $accessToken, $autoRevoke, $ip) {
        $result = $this->db->queryValue(
            'select tudu.create_access_token($1, $2, $3, $4, $5, $6);',
            [
                $accessToken->get(AccessTokenModel::USER_ID),
                $accessToken->get(AccessTokenModel::TOKEN_STRING),
                $accessToken->get(AccessTokenModel::TOKEN_TYPE),
                $accessToken->get(AccessTokenModel::TTL),
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
     * against (user ID required and token type required).
     * @param string $ip IP address
     * @return int Number of tokens revoked.
     */
    public function revokeActiveAccessTokens(Model $accessToken, $ip) {
        $result = $this->db->queryValue(
            'select tudu.revoke_active_access_tokens($1, $2, $3);',
            [
                $accessToken->get(AccessTokenModel::USER_ID),
                $accessToken->get(AccessTokenModel::TOKEN_TYPE),
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
     * (user ID, token string, and token type required).
     * @return true
     */
    public function validateAccessToken(Model $accessToken) {
        $result = $this->db->queryValue(
            'select tudu.validate_access_token($1, $2, $3);',
            [
                $accessToken->get(AccessTokenModel::USER_ID),
                $accessToken->get(AccessTokenModel::TOKEN_STRING),
                $accessToken->get(AccessTokenModel::TOKEN_TYPE)
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
