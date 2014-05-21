<?php

class SessionModel implements \League\OAuth2\Server\Storage\SessionInterface {

    private $db;

    public function __construct()
    {
        require_once './db.php';
        $this->db = new DB();
    }

   
    // public function updateSession($sessionId, $authCode = null, $accessToken = null, $refreshToken = null, $accessTokenExpire = null, $stage = 'requested')
    // {
        // $this->db->query('
            // UPDATE oauth_sessions SET
                // auth_code = :authCode,
                // access_token = :accessToken,
                // refresh_token = :refreshToken,
                // access_token_expires = :accessTokenExpire,
                // stage = :stage,
                // last_updated = UNIX_TIMESTAMP(NOW())
            // WHERE id = :sessionId',
        // array(
            // ':authCode' =>  $authCode,
            // ':accessToken'  =>  $accessToken,
            // ':refreshToken' =>  $refreshToken,
            // ':accessTokenExpire'    =>  $accessTokenExpire,
            // ':stage'    =>  $stage,
            // ':sessionId'    =>  $sessionId
        // ));
    // }




	//// ===== interface
    public function createSession($clientId, $ownerType, $ownerId) {
    	// $this->db->query('
            // INSERT INTO oauth_sessions (
                // client_id,
                // owner_type,
                // owner_id,
            // )
            // VALUES (
                // :clientId,
                // :ownerType,
                // :ownerId,
            // )', array(
            // ':clientId' =>  $clientId,
            // ':ownerType' =>  $ownerType,
            // ':ownerId'   =>  $ownerId,
        // ));
        
          $query = '
            INSERT INTO oauth_sessions (
                client_id,
                owner_type,
                owner_id
            )
            VALUES (
                :clientId,
                :ownerType,
                :ownerId
            )';
            $params =  array(
            ':clientId' =>  $clientId,
            ':ownerType' =>  $ownerType,
            ':ownerId'   =>  $ownerId
        );
		$this->db->query($query, $params);

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
    	$query = 'INSERT INTO oauth_session_redirects (session_id, redirect_uri) VALUE (:sessionId, :redirectUri)';
		$params = array(':sessionId' => $sessionId, ':redirectUri' => $redirectUri);
		$this->db->query($query, $params);		
    }

   
    public function associateAccessToken($sessionId, $accessToken, $expireTime) {
    	$query = 'INSERT INTO oauth_session_access_tokens (session_id, access_token, access_token_expires)
        VALUE (:sessionId, :accessToken, :accessTokenExpire)';
		$params = array(':sessionId' => $sessionId, ':accessToken' => $accessToken, ':accessTokenExpire' => $expireTime);
		$this->db->query($query, $params);
		
		return $this->db->getInsertId();
    }

 
    public function associateRefreshToken($accessTokenId, $refreshToken, $expireTime, $clientId) {
    	$query = 'INSERT INTO oauth_session_refresh_tokens (session_access_token_id, refresh_token, refresh_token_expires,
        client_id) VALUE (:accessTokenId, :refreshToken, :expireTime, :clientId)';
		$params = array(':accessTokenId' => $accessTokenId, ':refreshToken' => $refreshToken, ':expireTime' => $expireTime, ':clientId' => $clientId);
		$this->db->query($query, $params);		
    }

   
    public function associateAuthCode($sessionId, $authCode, $expireTime) {
    	$query = 'INSERT INTO oauth_session_authcodes (session_id, auth_code, auth_code_expires)
        VALUE (:sessionId, :authCode, :authCodeExpires)';
		$params = array(':sessionId' => $sessionId, ':authCode' => $authCode, ':authCodeExpires' => $expireTime);
		// throw new Exception('Missing auth parameters');
		// return;
		$this->db->query($query, $params);
    }

   
    public function removeAuthCode($sessionId) {
    	$query = 'DELETE FROM oauth_session_authcodes WHERE session_id = :sessionId';
		$params = array(':sessionId' => $sessionId);
		$this->db->query($query,$params);
    }

  
  	   
    public function validateAuthCode($clientId, $redirectUri, $authCode) {
    	// $query = 'SELECT oauth_sessions.id AS session_id, oauth_session_authcodes.id AS authcode_id FROM oauth_sessions
	    	// JOIN oauth_session_authcodes ON oauth_session_authcodes.session_id = oauth_sessions.id
	    	// JOIN oauth_session_redirects ON oauth_session_redirects.session_id = oauth_sessions.id WHERE
	    	// oauth_sessions.client_id = :clientId AND 
	    	// oauth_session_authcodes.auth_code = :authCode AND 
	    	// oauth_session_authcodes.auth_code_expires >= :time AND
	    	// oauth_session_redirects.redirect_uri = :redirectUri';		
		// $params = array(':clientId' => $clientId, ':authCode' => $authCode, ':time' => UNIX_TIMESTAMP(NOW()), ':redirectUri', $redirectUri);
		// $result = $this->db->query($query, $params);
		
		$query = 'SELECT oauth_sessions.id AS session_id, oauth_session_authcodes.id AS authcode_id FROM oauth_sessions
	    	JOIN oauth_session_authcodes ON oauth_session_authcodes.session_id = oauth_sessions.id
	    	JOIN oauth_session_redirects ON oauth_session_redirects.session_id = oauth_sessions.id WHERE
	    	oauth_sessions.client_id = :clientId AND 
	    	oauth_session_authcodes.auth_code = :authCode AND 
	    	oauth_session_authcodes.auth_code_expires >= UNIX_TIMESTAMP(NOW()) AND
	    	oauth_session_redirects.redirect_uri = :redirectUri';		
		$params = array(':clientId' => $clientId, ':authCode' => $authCode, ':redirectUri' => $redirectUri);
		$result = $this->db->query($query, $params);
		
		
		
    	// $result = $this->db->query('
                // SELECT * FROM oauth_sessions WHERE
                    // client_id = :clientId AND
                    // redirect_uri = :redirectUri AND
                    // auth_code = :authCode',
            // array(
                // ':clientId' =>  $clientId,
                // ':redirectUri'  =>  $redirectUri,
                // ':authCode' =>  $authCode
            // ));

        while ($row = $result->fetch())
        {
            return (array) $row;
        }

        return false;
    }

   
    public function validateAccessToken($accessToken) {
    	//die(var_dump('validateAccessToken'));
   		$query = 'SELECT session_id, oauth_sessions.client_id, oauth_sessions.owner_id, oauth_sessions.owner_type
      		FROM oauth_session_access_tokens JOIN oauth_sessions ON oauth_sessions.id = session_id WHERE
     		access_token = :accessToken AND access_token_expires >= UNIX_TIMESTAMP(NOW())';
		$params = array(':accessToken' => $accessToken);
		$result = $this->db->query($query, $params);
		while ($row = $result->fetch()) {
			return (array) $row;
		}
		return false;
    }

 
    public function removeRefreshToken($refreshToken) {
    	$query = 'DELETE FROM oauth_session_refresh_tokens WHERE refresh_token = :refreshToken';
		$params = array(':refreshToken' => $refreshToken);
		$this->db->query($query, $params);	
    }

 
    public function validateRefreshToken($refreshToken, $clientId) {
    	$query = 'SELECT session_access_token_id FROM oauth_session_refresh_tokens WHERE 
    	refresh_token = :refreshToken AND refresh_token_expires >= UNIX_TIMESTAMP(NOW()) AND 
    	client_id = :clientId';
		$params = array(':refreshToken' => $refreshToken, ':clientId' => $clientId);
		
		$result = $this->db->query($query, $params);
		while ($row = $result->fetch()) {
			return $row['session_access_token_id'];	
		}
		return false;		
    }

  
    public function getAccessToken($accessTokenId) {
    	$query = 'SELECT * FROM oauth_session_access_tokens WHERE id = :accessTokenId';
		$params = array(':accessTokenId' => $accessTokenId);
		$result = $this->db->query($query, $params);
		
		while ($row = $result->fetch()) {
			return (array)$row;
		}
		return false;
    }

   
    public function associateAuthCodeScope($authCodeId, $scopeId) {
    	$query = 'INSERT INTO oauth_session_authcode_scopes (oauth_session_authcode_id, scope_id) VALUES
       	(:authCodeId, :scopeId)';
		$params = array(':authCodeId' => $authCodeId, '$scopeId' => $scopeId);
		$this->db->query($query, $parmas);
    }

  
  	// TODO: this method must be tested.
    public function getAuthCodeScopes($oauthSessionAuthCodeId) {
    	$query = 'SELECT scope_id FROM oauth_session_authcode_scopes WHERE oauth_session_authcode_id = :authCodeId';
		$params = array(':authCodeId' => $oauthSessionAuthCodeId);
		$result = $this->db->query($query, $params);
		while ($row = $result->fetch()) {
			return (array)$row;
		}
		return false;
    }

  
    public function associateScope($accessTokenId, $scopeId) {
    	$this->db->query('INSERT INTO oauth_session_scopes (session_id, scope_id) VALUE (:sessionId, :scopeId)', array(
            ':sessionId'    =>  $sessionId,
            ':scopeId'  =>  $scopeId
        ));
    }
   
    public function getScopes($accessToken) {
    	$query = 'SELECT oauth_scopes.* FROM oauth_session_token_scopes JOIN oauth_session_access_tokens ON oauth_session_access_tokens.id = oauth_session_token_scopes.session_access_token_id    	
    	JOIN oauth_scopes ON oauth_scopes.id = oauth_session_token_scopes.scope_id
    	WHERE access_token = :accessToken';
		$params = array(':accessToken' => $accessToken);
		$result = $this->db->query($query, $params);
		while ($row = $result->fetch()) {
			return (array) $row;
		}
		return false;
    }
}