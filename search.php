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
echo "<h1> Search Results </h1>" ;
if($_GET)
{

$field=$_GET['search'];
$value=$_GET['SValues'];


$sql="Select * FROM info WHERE $value LIKE '%$field%'";
$result=mysql_query($sql);
echo "<table width= '936 ' border='1'>";
echo "<tr style='font-weight:bold;'>
<td  width= '241 '> Name</td>
<td width= '266 '>Mobile</td>
<td width= '200 '>Days Left</td>
<td width= '106 '>Details</td>
<td width= '99 '>Delete</td>
</tr></table>";
while($row = mysql_fetch_array($result))
{
echo "<table width= '936 ' style='border-bottom:1px solid black;'>
<tr>
<td width= '241 '> " .$row['login_name'] . "</td>
<td width= '266 '> " .$row['mobile'] .  "</td>
<td width= '200 '> " .$row['days_left'] . "</td>
<td width= '106 '><a href='update.php?id=". $row['id']. "'>Edit</a></td>
<td width= '99 '><a href='delete.php?id=". $row['id']. "'>Delete</a></td>
</tr>
</table>";

}
echo "</table>";

}
}
?>