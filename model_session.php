<?php

class SessionModel implements \League\OAuth2\Server\Storage\SessionInterface {

    private $db;

    public function __construct()
    {
        require_once './db.php';
        $this->db = new DB();
    }

   
    public function updateSession($sessionId, $authCode = null, $accessToken = null, $refreshToken = null, $accessTokenExpire = null, $stage = 'requested')
    {
        $this->db->query('
            UPDATE oauth_sessions SET
                auth_code = :authCode,
                access_token = :accessToken,
                refresh_token = :refreshToken,
                access_token_expires = :accessTokenExpire,
                stage = :stage,
                last_updated = UNIX_TIMESTAMP(NOW())
            WHERE id = :sessionId',
        array(
            ':authCode' =>  $authCode,
            ':accessToken'  =>  $accessToken,
            ':refreshToken' =>  $refreshToken,
            ':accessTokenExpire'    =>  $accessTokenExpire,
            ':stage'    =>  $stage,
            ':sessionId'    =>  $sessionId
        ));
    }




	//// ===== interface
    public function createSession($clientId, $ownerType, $ownerId) {
    	$this->db->query('
            INSERT INTO oauth_sessions (
                client_id,
                owner_type,
                owner_id,
            )
            VALUES (
                :clientId,
                :ownerType,
                :ownerId,
            )', array(
            ':clientId' =>  $clientId,
            ':ownerType' =>  $ownerType,
            ':ownerId'   =>  $ownerId,
        ));

        return $this->db->getInsertId();
    }

    
    public function deleteSession($clientId, $ownerType, $ownerId) {
    	 $this->db->query('
                DELETE IF EXISTS FROM oauth_sessions WHERE
                client_id = :clientId AND
                owner_type = :type AND
                owner_id = :typeId',
            array(
                ':clientId' =>  $clientId,
                ':type'  =>  $ownerType,
                ':typeId' =>  $ownerId
            ));
    }

   
    public function associateRedirectUri($sessionId, $redirectUri) {
    	
    }

   
    public function associateAccessToken($sessionId, $accessToken, $expireTime) {
    	
    }

 
    public function associateRefreshToken($accessTokenId, $refreshToken, $expireTime, $clientId) {
    	
    }

   
    public function associateAuthCode($sessionId, $authCode, $expireTime) {
    	
    }

   
    public function removeAuthCode($sessionId) {
    	
    }

  
    public function validateAuthCode($clientId, $redirectUri, $authCode) {
    	$result = $this->db->query('
                SELECT * FROM oauth_sessions WHERE
                    client_id = :clientId AND
                    redirect_uri = :redirectUri AND
                    auth_code = :authCode',
            array(
                ':clientId' =>  $clientId,
                ':redirectUri'  =>  $redirectUri,
                ':authCode' =>  $authCode
            ));

        while ($row = $result->fetch())
        {
            return (array) $row;
        }

        return false;
    }

   
    public function validateAccessToken($accessToken) {
    	die(var_dump('validateAccessToken'));
    }

 
    public function removeRefreshToken($refreshToken) {
    	
    }

 
    public function validateRefreshToken($refreshToken, $clientId) {
    	
    }

  
    public function getAccessToken($accessTokenId) {
    	
    }

   
    public function associateAuthCodeScope($authCodeId, $scopeId) {
    	
    }

  
    public function getAuthCodeScopes($oauthSessionAuthCodeId) {
    	
    }

  
    public function associateScope($accessTokenId, $scopeId) {
    	$this->db->query('INSERT INTO oauth_session_scopes (session_id, scope_id) VALUE (:sessionId, :scopeId)', array(
            ':sessionId'    =>  $sessionId,
            ':scopeId'  =>  $scopeId
        ));
    }

   
    public function getScopes($accessToken) {
    	
    }
}