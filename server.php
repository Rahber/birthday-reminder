<?php

include('functions.php');
$today=date('Y-m-d');
$sql="select * from info";
$result=mysql_query($sql);

while($row = mysql_fetch_array($result))
{
$id=$row['id'];
$newdate= addyears($row['birth_date'],$row['years']);
$days=difference($today,$newdate);
if($days<-1)
{
$yy=addy($row['years']);
$sqlll="UPDATE info 
SET years='$yy'
 where id=$id";
$resultt=mysql_query($sqlll);
}
else{
$sqll="UPDATE info 
SET days_left='$days'
 where id=$id";
$resultt=mysql_query($sqll);
if ($days  < 2)
{
$email_body = "Hello <br />";
$email_body .="This is to inform you that following user have birthday in next 2 days  ";
$email_body .="<br /> The details of the user are as below";

$email_body .= "<br /><b>Name " . $row['login_name'] . " </b>.";
$email_body .= "<br /><b>Mobile Number " . $row['mobile'] . " </b>.";
$email_body .= "<br /><b>Days left " . $row['days_left'] . " </b>.";
$email_body .= "<br /><b>Date  " . $row['birth_date'] . " </b>.";

$email_body .= "<br />Thank You<br /> ";	

$contact_subject=date("F jS", strtotime($row["birth_date"])) .' - Birthday of '.  $row["login_name"];
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Additional headers
$headers .= 'To:'. $s2 . "\r\n";
$headers .= "From: Birthdays <birthday@rahber.net>" . "\r\n"; 
 mail( $s2, $contact_subject, $email_body, $headers ) ;
}
}}


echo "Fields Updated";
echo "<a href='index.php' >Click here to return to mainsite</a>";

?>