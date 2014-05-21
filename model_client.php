<?php

class ClientModel implements \League\OAuth2\Server\Storage\ClientInterface {


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