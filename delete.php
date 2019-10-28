<?php include ('functions.php');?>
<?php

if(!isset($_SESSION['u']))
{
echo "<script type='text/javascript'>
<!--
window.location = 'index.php?SessionMissing'
//-->
</script> ";
}
else
{
$id=$_GET['id'];

$sql="DELETE FROM info WHERE id=$id";
$result=mysql_query($sql);
echo "<script type='text/javascript'>
<!--
window.location = 'admin.php?Deleted'
//-->
</script> ";
}
?>