<?php include ('functions.php');


if($_GET['logout']=='true')
{

unset($_SESSION['u']);
}


if(isset($_SESSION['u']))
{
echo "<script type='text/javascript'>
<!--
window.location = 'admin.php?SessionExist'
//-->
</script> ";

}
?>
<form method="post" action="admin.php">
<table width="100%" border="0" >
  <tr>
    <td>Username</td>
    <td><input type="text" name="username" id="username" /></td>
  </tr>
  <tr>
    <td>Password</td>
    <td><input type="password" name="password" id="password" /></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><input type="submit" name="submit" id="submit" value="Login!" /></td>
  </tr>
</table>
</form>


<?php include ('footer.php');
?>