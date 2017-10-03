<?php 

define('HOST','localhost');
define('USER','user');
define('PASS','password');
define('DBNAME','server_webservice');

class DB
{

    protected $conexion;
    protected $db;

    public function conect()
    {
        $this->conexion = mysql_connect(HOST, USER, PASS);
        if ($this->conexion == 0) DIE("Sorry, cant connect whit MySQL: " . mysql_error());
        $this->db = mysql_select_db(DBNAME, $this->conexion);
        if ($this->db == 0) DIE("Sorry, cant connect whit database: " . DBNAME);

        return true;
    }

    public function close()
    {
        if ($this->conexion) {
            mysql_close($this->conexion);
        }
    }

	public function getDb(){
		return $this->db;
	}	

	public function getLink(){
		return $this->conexion;
	}

}
