<?php include ('functions.php');?>
<?php
$name=mysql_real_escape_string($_POST['login']);
$mobile=mysql_real_escape_string($_POST['mobile']);
$date=mysql_real_escape_string($_POST['date']);
$years=mysql_real_escape_string($_POST['years']);
if($_POST){
insert($name,$mobile,$date,$years);
{
echo "<script type='text/javascript'>
<!--
window.location = 'admin.php?Added'
//-->
</script> ";
}

}

?>