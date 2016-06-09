<?php

// This is where your database creds come from
require '../parameters.php';


class myTeams
{
     protected $conn;
     protected $db_host;
     protected $db_username;
     protected $db_password;
     protected $db_database;


     public function __construct($host,$username,$password,$database)
     {
          $this->db_host = $host;
          $this->db_username = $username;
          $this->db_password = $password;
          $this->db_database = $database;
     }

     /**
      * Connect up the database
      * @return boolean Returns true if we connect up
      */
     public function connectToDb()
     {

          $this->conn = new mysqli($this->db_host, $this->db_username, $this->db_password, $this->db_database);

          if ($this->conn->connect_error)
          {
               return false;
          }

          return true;
     }



}

$app = new myTeams($db_host,$db_username,$db_password,$db_database);

$db = $app->connectToDb();

if($db)
{
     // We got a connect to the db so get to work.
}
else
{
     // Didn't connect up to the db.
}

















?>
