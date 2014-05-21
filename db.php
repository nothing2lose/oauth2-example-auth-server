<?php

class DB {

	private $conn;
	private $statement;

	public function __construct()
	{
		$this->conn = new PDO('mysql:host=127.0.0.1;dbname=oauth', 'test', '1234');
		
	}

	function query($sql = '', $params = array())
	{
		$statement = $this->conn->prepare($sql);
		$statement->setFetchMode(PDO::FETCH_OBJ);
		$statement->execute($params);
		return $statement;
	}

	public function getInsertId()
	{
		return (int) $this->conn->lastInsertId();
	}

}
