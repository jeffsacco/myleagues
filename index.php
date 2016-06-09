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

     protected $league_data;


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

     public function getLeagues($limit_lower = null, $limit_upper = null)
     {
          $sql = "SELECT id, uid, tid, sid, lid, league_tid, league_name, league_provider, last_sync FROM ft_myleagues limit $limit_lower, $limit_upper";

          $val = $this->conn->query($sql);

          if($val)
          {
               $this->league_data = $val;
               return true;
          }
          else
          {
               return false;
          }
     }



}

$app = new myTeams($db_host,$db_username,$db_password,$db_database);

$db = $app->connectToDb();

if($db)
{
     // We got a connect to the db so get to work.

     // Move on to getting the leagues
     $league_data = $app->getLeagues(0,10);

     if($league_data)
     {
          // We got some valid league data

     }
     else
     {
          echo "Something went wrong grabbing the league data.  Check it out.";
     }
}
else
{
     // Didn't connect up to the db.
     echo "Couldn't connect up the database";
}



















?>
