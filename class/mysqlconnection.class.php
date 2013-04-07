<?php
class MySqlConnection {
	private $server = 'localhost';
    private $user = 'root';
	private $password = '';
	private $database = 'aeolus';
	
	private $mysql_connection;
	
    public function __construct() {
        if ( ! ($this->mysql_connection = mysql_connect($this->server, $this->user, $this->password)) ) {
			die('Connection to database failed');
		}
		if ( ! mysql_select_db($this->database) ) {
			die('Connection to database failed');
		}
    }
	
	public function __destruct() {
		if ( is_resource($this->mysql_connection) ) {
            mysql_close($this->mysql_connection);
        }
	}
}
?>