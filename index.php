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
          $arr['rec_wr']['rec_yrd']= $data['RecYrd'];
          $arr['rec_wr']['rec_td']= $data['RecTD'];
          $arr['rec_te'] = array();
          $arr['rec_te']['rec']= $data['Trec'];
          $arr['rec_te']['rec_yrd']= $data['RecYrd'];
          $arr['rec_te']['rec_td']= $data['RecTD'];
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

          $arr['fg_made'] = array();

          // Need to do a logic check here
          if($data['bpfg29'] > 0 || $data['bpfg39'] > 0 || $data['bpfg49'] || $data['bpfg50'])
          {


                    $var2 = array('name'=>'0-29','pts'=>$data['bpfg29'],'lower'=>0,'upper'=>29);

               $arr['fg_made'][] = $var2;

                    $var3 = array('name'=>'30-39','pts'=>$data['bpfg39'],'lower'=>30,'upper'=>39);

               $arr['fg_made'][] = $var3;

                    $var4 = array('name'=>'40-49','pts'=>$data['bpfg49'],'lower'=>40,'upper'=>49);

               $arr['fg_made'][] = $var4;

                    $var5 = array('name'=>'50+','pts'=>$data['bpfg50'],'lower'=>50,'upper'=>99);

               $arr['fg_made'][] = $var5;
          }
          else
          {
                    $var1 = array('name'=>'0-99','pts'=>$data['FG'],'lower'=>0,'upper'=>99);

               $arr['fg_made'][] = $var1;
          }


          $arr['bpts_td'] = array();

                    $var1 = array('name'=>'0-9','pts'=>$data['bpy9'],'lower'=>0,'upper'=>9);

               $arr['bpts_td'][] = $var1;

                    $var2 = array('name'=>'10-19','pts'=>$data['bpy19'],'lower'=>10,'upper'=>19);

               $arr['bpts_td'][] = $var2;

                    $var3 = array('name'=>'20-29','pts'=>$data['bpy29'],'lower'=>20,'upper'=>29);

               $arr['bpts_td'][] = $var3;

                    $var4 = array('name'=>'30-39','pts'=>$data['bpy39'],'lower'=>30,'upper'=>39);

               $arr['bpts_td'][] = $var4;

                   $var5 = array('name'=>'40+','pts'=>$data['bpy40'],'lower'=>40,'upper'=>100);

               $arr['bpts_td'][] = $var5;

          $arr['fg_miss'] = $data['FGM'];

          $arr['xp_made'] = array();
          $arr['xp_made']['name']= "0-99";
          $arr['xp_made']['pts']= $data['XP'];
          $arr['xp_made']['lower']= "0";
          $arr['xp_made']['upper']= "99";

          $arr['xp_miss'] = $data['XPM'];
          $arr['fum_lost'] = $data['FL'];
          $arr['two_pt'] = "";
          $arr['def_sack'] = $data['td_sack'];
          $arr['def_fum_rec'] = $data['td_fr'];
          $arr['def_int'] = $data['td_int'];
          $arr['def_blk'] = $data['td_blk'];
          $arr['def_saftey'] = $data['td_saf'];
          $arr['def_td'] = $data['td_dtd'];
          $arr['def_std'] = $data['td_std'];
          $arr['def_sack'] = $data['td_sack'];

          $arr['def_pts_allowed'] = array();

                    $var1 = array('name'=>'0','pts'=>$data['pts_all_0'],'lower'=>0,'upper'=>0);

               $arr['def_pts_allowed'][] = $var1;

                    $var2 = array('name'=>'1-6','pts'=>$data['pts_all_6'],'lower'=>1,'upper'=>6);

               $arr['def_pts_allowed'][] = $var2;

                    $var3 = array('name'=>'7-13','pts'=>$data['pts_all_13'],'lower'=>7,'upper'=>13);

               $arr['def_pts_allowed'][] = $var3;

                    $var4 = array('name'=>'14-20','pts'=>$data['pts_all_20'],'lower'=>14,'upper'=>20);

               $arr['def_pts_allowed'][] = $var4;

                   $var5 = array('name'=>'21-27','pts'=>$data['pts_all_27'],'lower'=>21,'upper'=>27);

               $arr['def_pts_allowed'][] = $var5;

                   $var6 = array('name'=>'28-34','pts'=>$data['pts_all_34'],'lower'=>28,'upper'=>34);

               $arr['def_pts_allowed'][] = $var6;

                   $var7 = array('name'=>'35+','pts'=>$data['pts_all_35'],'lower'=>35,'upper'=>99);

               $arr['def_pts_allowed'][] = $var7;


          $arr['def_yrd_allowed'] = array();

                    $var1 = array('name'=>'0-199','pts'=>$data['yrds_0_199'],'lower'=>0,'upper'=>199);

               $arr['def_yrd_allowed'][] = $var1;

                    //$var2 = array('name'=>'200-249','pts'=>$data['yrds_200_249'],'lower'=>'200','upper'=>'249');

               //$arr['def_yrd_allowed'][] = $var2;

                    $var3 = array('name'=>'200-299','pts'=>$data['yrds_200_299'],'lower'=>200,'upper'=>299);

               $arr['def_yrd_allowed'][] = $var3;

                    $var4 = array('name'=>'300-349','pts'=>$data['yrds_300_349'],'lower'=>300,'upper'=>349);

               $arr['def_yrd_allowed'][] = $var4;

                   $var5 = array('name'=>'350-399','pts'=>$data['yrds_350_399'],'lower'=>350,'upper'=>399);

               $arr['def_yrd_allowed'][] = $var5;

                   $var6 = array('name'=>'400-449','pts'=>$data['yrds_400_449'],'lower'=>400,'upper'=>449);

               $arr['def_yrd_allowed'][] = $var6;

                   $var7 = array('name'=>'450+','pts'=>$data['yrds_450'],'lower'=>450,'upper'=>999);

               $arr['def_yrd_allowed'][] = $var7;


          $arr['bpts_pass_yrd'] = array();

                    $var1 = array('pts'=>$data['bpp1'],'rec'=>'0.00','yrd'=>$data['bppy1']);

               $arr['bpts_pass_yrd'][] = $var1;

                    $var2 = array('pts'=>$data['bpp2'],'rec'=>'0.00','yrd'=>$data['bppy2']);

               $arr['bpts_pass_yrd'][] = $var2;


          $arr['bpts_rush_yrd'] = array();

                    $var1 = array('pts'=>$data['bpru1'],'rec'=>'0.00','yrd'=>$data['bpruy1']);

               $arr['bpts_rush_yrd'][] = $var1;

                    $var2 = array('pts'=>$data['bpru2'],'rec'=>'0.00','yrd'=>$data['bpruy2']);

               $arr['bpts_rush_yrd'][] = $var2;


          $arr['bpts_rec_yrd_rb'] = array();

                    $var1 = array('pts'=>$data['bprc1'],'rec'=>'0.00','yrd'=>$data['bprcy1']);

               $arr['bpts_rec_yrd_rb'][] = $var1;

                    $var2 = array('pts'=>$data['bprc2'],'rec'=>'0.00','yrd'=>$data['bprcy2']);

               $arr['bpts_rec_yrd_rb'][] = $var2;


          $arr['bpts_rec_yrd_wr'] = array();

                    $var1 = array('pts'=>$data['bprc1'],'rec'=>'0.00','yrd'=>$data['bprcy1']);

               $arr['bpts_rec_yrd_wr'][] = $var1;

                    $var2 = array('pts'=>$data['bprc2'],'rec'=>'0.00','yrd'=>$data['bprcy2']);

               $arr['bpts_rec_yrd_wr'][] = $var2;

         $arr['bpts_rec_yrd_te'] = array();

                    $var1 = array('pts'=>$data['bprc1'],'rec'=>'0.00','yrd'=>$data['bprcy1']);

               $arr['bpts_rec_yrd_te'][] = $var1;

                    $var2 = array('pts'=>$data['bprc2'],'rec'=>'0.00','yrd'=>$data['bprcy2']);

               $arr['bpts_rec_yrd_te'][] = $var2;


          $arr['bpts_rec_rec_rb'] = array();
          $arr['bpts_rec_rec_rb']['pts']= "0.00";
          $arr['bpts_rec_rec_rb']['rec']= "0.00";
          $arr['bpts_rec_rec_rb']['yrd']= "0.00";

          $arr['bpts_rec_rec_wr'] = array();
          $arr['bpts_rec_rec_wr']['pts']= "0.00";
          $arr['bpts_rec_rec_wr']['rec']= "0.00";
          $arr['bpts_rec_rec_wr']['yrd']= "0.00";

          $arr['bpts_rec_rec_te'] = array();
          $arr['bpts_rec_rec_te']['pts']= "0.00";
          $arr['bpts_rec_rec_te']['rec']= "0.00";
          $arr['bpts_rec_rec_te']['yrd']= "0.00";

          $arr['bpts_yrd_100'] = $data['tot100'];
          $arr['bpts_yrd_300'] = $data['tot300'];
          $arr['bpts_pass_oop_td'] = $data['bptd'];
          $arr['bpts_rush_oop_td'] = $data['brutd'];
          $arr['bpts_rec_oop_td'] = $data['bretd'];


          return $arr;
     }

     public function jsonPrint()
     {
          $num = count($this->league_data);

          for ($i=0; $i < $num; $i++)
          {
               //print_r(json_encode($this->league_data[$i]));
               //echo ",";
               print('<pre>');
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
