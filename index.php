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
          $sql = "SELECT id as legacyUserId, uid, tid, sid, lid as importKey, league_tid, league_name as name, league_provider as host, last_sync FROM ft_myleagues limit $limit_lower, $limit_upper";

          $val = $this->conn->query($sql);

          if($val)
          {
               $this->league_data_raw = $val;

               while ($row = $this->league_data_raw->fetch_assoc())
               {

                    // Create the space for the new fields.
                    $arrNew = array('_id'=>"",'userId'=>"",'nickname'=>"",'hostLeagueId'=>'');

                    // Merge both arrays
                    $mer = array_merge($row,$arrNew);

                    // Assign the top array to leagues
                    $arr = array('league'=>$mer);
                    // Assign it
                    $this->league_data[] = $arr;
               }

               // All went well so return true
               return true;
          }
          else
          {
               return false;
          }
     }

     public function getTeams()
     {
          // We need to loop through our leagues

          //  We need to use the address of the variable because we are going to alter it for the print out.
          foreach ($this->league_data as &$league)
          {
               // Grab relevant data for the SQL query

               $sql = "SELECT name FROM ft_myteams WHERE tid = ".$league['league']['tid']." AND sid = ".$league['league']['sid']." AND id = ".$league['league']['uid'];

               $raw_data_teams = $this->conn->query($sql);

               $sql = "SELECT qbstrt,rbstrt,wrstrt,testrt,kstrt,dstrt,dlstrt,lbstrt,dbstrt,qbflex,rbflex,wrflex,teflex,kflex,dflex,dlflex,lbflex,dbflex FROM ft_myteams WHERE tid = ".$league['league']['tid']." AND sid = ".$league['league']['sid']." AND id = ".$league['league']['uid'];

               $raw_data_positions = $this->conn->query($sql);

               $sql = "SELECT * FROM ft_scoring WHERE sid = ".$league['league']['sid'];

               $raw_data_scoringsystem = $this->conn->query($sql);

               //print_r($raw_data_scoringsystem);

               // Fetch all those players ids
               $sql = "SELECT pid from ft_myteamplayers WHERE tid = ".$league['league']['tid']." AND id = ".$league['league']['uid'];

               $raw_data_players = $this->conn->query($sql);

               $player_string = $this->resultToArray($raw_data_players);

               while($row = $raw_data_teams->fetch_assoc())
               {
                    $row['nickname'] = $row['name'];
                    $row['lastSync'] = $league['league']['last_sync'];
                    $row['userIsOwner'] = true;
                    $row['hostTeamId'] = $league['league']['tid'];
                    $row['matchups'] = "";
                    $row['players'] = "[".$player_string."]";
                    $league['league']['teams'] = $row;

                    // Add in the positions array
                    $row1 = $raw_data_positions->fetch_assoc();
                    $league['league']['teams']['positions'] = $row1;

                    // Add in the scoring system
                    $scoringSystem = $raw_data_scoringsystem->fetch_assoc();
                    $alteredScoringSystem = $this->__transformScoringSystem($scoringSystem);
                    $league['league']['teams']['scoringSystem'] = $alteredScoringSystem;

               }

          }
     }



     public function resultToArray($data)
     {
          $string = "";

          $num = $data->num_rows;

          for ($i=1; $i <= $num; $i++)
          {
               $playerid = $data->fetch_assoc();
               $string .= $playerid['pid'];
               if($i != $num)
               {
                    $string .= ",";
               }
          }

          return $string;
     }


     private function __transformScoringSystem($data)
     {
          $arr = array();

          $arr['_id'] = "";
          $arr['name'] = $data['Name'];
          $arr['isSystemPreset'] = "";
          $arr['sortPriority'] = "";
          $arr['pass_att'] = $data['Patt'];
          $arr['pass_cmp'] = $data['Pcmp'];
          $arr['pass_icmp'] = $data['Pinc'];
          $arr['pass_yrd'] = $data['PYrd'];
          $arr['pass_td'] = $data['PTD'];
          $arr['pass_int'] = $data['PINT'];
          $arr['rush_att'] = $data['RuAtt'];
          $arr['rush_yrd'] = $data['RuYrd'];
          $arr['rush_td'] = $data['RuTD'];
          $arr['rec_rb'] = array();
          $arr['rec_rb']['rec']= $data['Rec'];
          $arr['rec_rb']['rec_yrd']= $data['RecYrd'];
          $arr['rec_rb']['rec_td']= $data['RecTD'];
          $arr['rec_wr'] = array();
          $arr['rec_wr']['rec']= $data['Wrec'];
          //$arr['rec_wr']['rec_yrd']= $data['WRecYrd'];
          //$arr['rec_wr']['rec_td']= $data['WRecTD'];
          $arr['rec_te'] = array();
          $arr['rec_te']['rec']= $data['Trec'];
          //$arr['rec_te']['rec_yrd']= $data['TRecYrd'];
          //$arr['rec_te']['rec_td']= $data['TRecTD'];
          $arr['pr_yrd'] = $data['PrYrd'];
          $arr['pr_td'] = $data['PrTD'];
          $arr['kr_yrd'] = $data['KrYrd'];
          $arr['kr_td'] = $data['KrTD'];
          $arr['idp_dl'] = array();
          $arr['idp_dl']['tackle']= $data['dlTck'];
          $arr['idp_dl']['assist']= $data['dlAst'];
          $arr['idp_dl']['sack']= $data['dlSack'];
          $arr['idp_de'] = array();
          $arr['idp_de']['tackle']= $data['dlTck'];
          $arr['idp_de']['assist']= $data['dlAst'];
          $arr['idp_de']['sack']= $data['dlSack'];
          $arr['idp_dt'] = array();
          $arr['idp_dt']['tackle']= $data['dlTck'];
          $arr['idp_dt']['assist']= $data['dlAst'];
          $arr['idp_dt']['sack']= $data['dlSack'];

          $arr['idp_lb'] = array();
          $arr['idp_lb']['tackle']= $data['lbTck'];
          $arr['idp_lb']['assist']= $data['lbAst'];
          $arr['idp_lb']['sack']= $data['lbSack'];

          $arr['idp_olb'] = array();
          $arr['idp_olb']['tackle']= $data['lbTck'];
          $arr['idp_olb']['assist']= $data['lbAst'];
          $arr['idp_olb']['sack']= $data['lbSack'];

          $arr['idp_ilb'] = array();
          $arr['idp_ilb']['tackle']= $data['lbTck'];
          $arr['idp_ilb']['assist']= $data['lbAst'];
          $arr['idp_ilb']['sack']= $data['lbSack'];

          $arr['idp_db'] = array();
          $arr['idp_db']['tackle']= $data['dbTck'];
          $arr['idp_db']['assist']= $data['dbAst'];
          $arr['idp_db']['sack']= $data['dbSack'];

          $arr['idp_cb'] = array();
          $arr['idp_cb']['tackle']= $data['dbTck'];
          $arr['idp_cb']['assist']= $data['dbAst'];
          $arr['idp_cb']['sack']= $data['dbSack'];

          $arr['idp_s'] = array();
          $arr['idp_s']['tackle']= $data['dbTck'];
          $arr['idp_s']['assist']= $data['dbAst'];
          $arr['idp_s']['sack']= $data['dbSack'];

          $arr['idp_db'] = array();
          $arr['idp_db']['tackle']= $data['dbTck'];
          $arr['idp_db']['assist']= $data['dbAst'];
          $arr['idp_db']['sack']= $data['dbSack'];

          $arr['idp_fum_rec'] = $data['FR'];
          $arr['idp_fum_frc'] = $data['FF'];
          //$arr['idp_int'] = $data['INT'];
          $arr['idp_pd'] = $data['PD'];
          $arr['idp_td'] = $data['DTD'];

          return $arr;
     }

     public function jsonPrint()
     {
          $num = count($this->league_data);

          for ($i=0; $i < $num; $i++)
          {
               //print_r(json_encode($this->league_data[$i]));
               print_r($this->league_data[$i]);
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
          $app->getTeams();



          $app->jsonPrint();
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
