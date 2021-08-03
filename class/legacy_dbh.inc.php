<?php

class legacyDbh {

	protected $servername = '192.168.102.11';
	private $username;
	private $password;
	private $dbname;
	private $charset;

	public function connect() {
//		$this->servername = "localhost";
//                $this->servername = "192.168.102.11";
//                $this->servername = "10.10.1.2";
		$this->username = "root";
		$this->password = "5105458";
		$this->dbname = "phhsystem";
//		$this->dbname = "newphhsystem";
		$this->charset = "utf8";

		try {
			$dsn = "mysql:host=".$this->servername.";dbname=".$this->dbname.";charset=".$this->charset;
			$pdo = new PDO($dsn, $this->username, $this->password);
			// $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $pdo;
		} catch (PDOException $e) {
			echo "Connection failed: ".$e->getMessage();
		}
	}
        public function get_servername_dbh(){
            return $this->servername;
        }

}

