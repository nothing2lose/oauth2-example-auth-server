<?php
// League\OAuth2\Server\Storage;

class ClientModel implements \League\OAuth2\Server\Storage\ClientInterface {
// class ClientModel implements OAuth2\Storage\ClientInterface {

	//public function getClient($clientId = null, $clientSecret = null, $redirectUri = null)
	public function getClient($clientId, $clientSecret = NULL, $redirectUri = NULL, $grantType = NULL)
	{
		return array(
			'client_id' => '1234',
			'client secret' => '5678',
			'redirect_uri' => 'http://foo/redirect',
			'name' => 'Test Client'
		);
	}

}