<?php include ('functions.php');?>
<style>
.paginate {
font-family:Arial, Helvetica, sans-serif;
	padding: 3px;
	margin: 3px;
}

.paginate a {
	padding:2px 5px 2px 5px;
	margin:2px;
	border:1px solid #999;
	text-decoration:none;
	color: #666;
}
.paginate a:hover, .paginate a:active {
	border: 1px solid #999;
	color: #000;
}
.paginate span.current {
    margin: 2px;
	padding: 2px 5px 2px 5px;
		border: 1px solid #999;
		
		font-weight: bold;
		background-color: #999;
		color: #FFF;
	}
	.paginate span.disabled {
		padding:2px 5px 2px 5px;
		margin:2px;
		border:1px solid #eee;
		color:#DDD;
	}
	
	li{
		padding:4px;
		margin-bottom:3px;
		background-color:#FCC;
		list-style:none;}
		
	ul{margin:6px;
	padding:0px;}	
	
</style>

<?php

$u=mysql_real_escape_string($_POST['username']);
$p=mysql_real_escape_string($_POST['password']);
if(!isset($_SESSION['u']))
{
if(!$u || !$p)
{

echo "<script type='text/javascript'>
<!--
window.location = 'index.php?Blank'
//-->
</script> ";

}
else
{

echo $u;
$sqlp="SELECT * FROM admin WHERE username= '$u' AND password='$p'";
$rep=mysql_query($sqlp);
$count=mysql_num_rows($rep);
if($count==1)
{
$_SESSION['u']=$u;
echo "<script type='text/javascript'>
<!--
window.location = 'admin.php?Loggedin'
//-->
</script> ";

}
else
{

echo "<script type='text/javascript'>
<!--
window.location = 'index.php?Incorrect'
//-->
</script> ";

}

}

}
else
{


echo "Welcome to Server Administration Panel";

//$sql="Select * from info ";
//$result=mysql_query($sql);
echo "<table width= '936 ' border='1'>";
echo "<tr style='font-weight:bold;'>
<td  width= '241 '>Name</td>
<td width= '266 '>Mobile</td>
<td width= '200 '>Days Left</td>
<td width= '106 '>Details</td>
<td width= '99 '>Delete</td>
</tr></table>";




	$tableName="info";		
	$targetpage = "admin.php"; 	
	$limit = 10; 
	
	$query = "SELECT COUNT(*) as num FROM $tableName ORDER BY days_left ASC";
	$total_pages = mysql_fetch_array(mysql_query($query));
	$total_pages = $total_pages[num];
	
	$stages = 3;
	$page = mysql_escape_string($_GET['page']);
	if($page){
		$start = ($page - 1) * $limit; 
	}else{
		$start = 0;	
		}	
	
    // Get page data
	$query1 = "SELECT * FROM $tableName ORDER BY days_left ASC LIMIT $start, $limit";
	$result = mysql_query($query1);
	
	// Initial page num setup
	if ($page == 0){$page = 1;}
	$prev = $page - 1;	
	$next = $page + 1;							
	$lastpage = ceil($total_pages/$limit);		
	$LastPagem1 = $lastpage - 1;					
	
	
	$paginate = '';
	if($lastpage > 1)
	{	
	

	
	
		$paginate .= "<div class='paginate'>";
		// Previous
		if ($page > 1){
			$paginate.= "<a href='$targetpage?page=$prev'>previous</a>";
		}else{
			$paginate.= "<span class='disabled'>previous</span>";	}
			

		
		// Pages	
		if ($lastpage < 7 + ($stages * 2))	// Not enough pages to breaking it up
		{	
			for ($counter = 1; $counter <= $lastpage; $counter++)
			{
				if ($counter == $page){
					$paginate.= "<span class='current'>$counter</span>";
				}else{
					$paginate.= "<a href='$targetpage?page=$counter'>$counter</a>";}					
			}
		}
		elseif($lastpage > 5 + ($stages * 2))	// Enough pages to hide a few?
		{
			// Beginning only hide later pages
			if($page < 1 + ($stages * 2))		
			{
				for ($counter = 1; $counter < 4 + ($stages * 2); $counter++)
				{
					if ($counter == $page){
						$paginate.= "<span class='current'>$counter</span>";
					}else{
						$paginate.= "<a href='$targetpage?page=$counter'>$counter</a>";}					
				}
				$paginate.= "...";
				$paginate.= "<a href='$targetpage?page=$LastPagem1'>$LastPagem1</a>";
				$paginate.= "<a href='$targetpage?page=$lastpage'>$lastpage</a>";		
			}
			// Middle hide some front and some back
			elseif($lastpage - ($stages * 2) > $page && $page > ($stages * 2))
			{
				$paginate.= "<a href='$targetpage?page=1'>1</a>";
				$paginate.= "<a href='$targetpage?page=2'>2</a>";
				$paginate.= "...";
				for ($counter = $page - $stages; $counter <= $page + $stages; $counter++)
				{
					if ($counter == $page){
						$paginate.= "<span class='current'>$counter</span>";
					}else{
						$paginate.= "<a href='$targetpage?page=$counter'>$counter</a>";}					
				}
				$paginate.= "...";
				$paginate.= "<a href='$targetpage?page=$LastPagem1'>$LastPagem1</a>";
				$paginate.= "<a href='$targetpage?page=$lastpage'>$lastpage</a>";		
			}
			// End only hide early pages
			else
			{
				$paginate.= "<a href='$targetpage?page=1'>1</a>";
				$paginate.= "<a href='$targetpage?page=2'>2</a>";
				$paginate.= "...";
				for ($counter = $lastpage - (2 + ($stages * 2)); $counter <= $lastpage; $counter++)
				{
					if ($counter == $page){
						$paginate.= "<span class='current'>$counter</span>";
					}else{
						$paginate.= "<a href='$targetpage?page=$counter'>$counter</a>";}					
				}
			}
		}
					
				// Next
		if ($page < $counter - 1){ 
			$paginate.= "<a href='$targetpage?page=$next'>next</a>";
		}else{
			$paginate.= "<span class='disabled'>next</span>";
			}
			
		$paginate.= "</div>";		
	
	
}
 //echo $total_pages.' Results';
 // pagination
 

while($row = mysql_fetch_array($result))
{
echo "<table width= '936 ' style='border-bottom:1px solid black;'>
<tr>
<td width= '241 '> " .$row['login_name'] . "</td>
<td width= '266 '> " .$row['mobile'] .  "</td>
<td width= '200 '> " .$row['days_left'] . "</td>
<td width= '106 '><a href='update.php?id=". $row['id']. "'>Details</a></td>
<td width= '99 '><a href='delete.php?id=". $row['id']. "'>Delete</a></td>
</tr>
</table>";

}
echo "</table>";

echo $paginate;


}


?><?php include ('footer.php');
?>