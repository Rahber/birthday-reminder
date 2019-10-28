<?php
//General Variables
session_start();
$table='info';
$table1='admin';
$today= date('Y-m-d');

$s2="someemail"; //email to send reminders to
//Generals Functions
function connect()
{

$server ='';
$user='root';
$password='root';
$database='root';
$con = mysql_connect($server,$user,$password);
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
 mysql_select_db($database, $con);

}


function disconnect()
{



}

function insert($name,$mobile,$date,$years)
{
$today = date('Y-m-d');
$newdate= addyears($date,$years);
$days=difference($today,$newdate);
$today = date('Y-m-d');

$sql="INSERT INTO info (login_name, mobile, birth_date, years,days_left )
VALUES
('$name','$mobile','$date','$years','$days')";
$result=mysql_query($sql);
}


function update($name,$mobile,$date,$years,$id)
{
$today = date('Y-m-d');
$newdate= addyears($date,$years);
$days=difference($today,$newdate);


$sql="UPDATE info SET login_name='$name', mobile='$mobile', birth_date='$date', years='$years',days_left='$days'  WHERE id='$id'";
$result=mysql_query($sql);




}
function login($username,$password)
{


}

function display($txt)
{
echo "<span> " . $txt. "</span>";

}


function difference($start, $end) {
  $start_ts = strtotime($start);
  $end_ts = strtotime($end);
  $diff = $end_ts - $start_ts;
  return round($diff / 86400);
} 

function addyears($date,$years)
{

$newdate = strtotime ( $years .'years', strtotime ( $date ) ) ;
$newdate = gmdate ( 'Y-m-d' , $newdate );
return $newdate;

}

function addy($years)
{

 return $years+1;

}
error_reporting(0);
connect();

include ('header.php');

?>