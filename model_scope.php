<?php


//class ScopeModel implements \OAuth2\Storage\ScopeInterface {
class ScopeModel implements \League\OAuth2\Server\Storage\ScopeInterface {

	private $db;

    public function __construct()
    {
        require_once 'db.php';
        $this->db = new DB();
    }

	public function getScope($scope, $clientId = NULL, $grantType = NULL) 
	{

		$result = $this->db->query('SELECT * FROM oauth_scopes WHERE scope = :scope', array(':scope' => $scope));
		$row = $result->fetch();

		if ($row) {
			return array(
				'id'	=>	$row->id,
				'scope'	=>	$row->scope,
				'name'	=>	$row->name,
				'description'	=>	$row->description
			);
		} else {
			return false;
		}

	}

}