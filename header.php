<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>System Administration Reminder System</title>
<script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<script src="SpryAssets/SpryValidationSelect.js" type="text/javascript"></script>
<link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryValidationSelect.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/jquery-ui-1.8.15.custom.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="SpryAssets/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="SpryAssets/jquery-ui-1.8.15.custom.min.js"></script>
<script src="SpryAssets/SpryMenuBar.js" type="text/javascript"></script>
<link href="SpryAssets/SpryMenuBarHorizontal.css" rel="stylesheet" type="text/css" />
</head>


 
<body id="main" style="background-image: url(SpryAssets/images/curve_bg.jpg);background-repeat:no-repeat;background-position:top;
	background-color:#FFFFFF;">
<div>
  <p><img src="SpryAssets/images/logo.png" width="194" height="84" alt="Mobilink" /></p>
  
  
  <p>&nbsp; </p>
  
</div>
<div style="width:990px;margin: 50px auto;">

<script>
	// increase the default animation speed to exaggerate the effect
	$.fx.speeds._default = 1000;
	$(function() {
		$( "#dialog" ).dialog({
			autoOpen: false,
			show: "blind",
			hide: "explode"
		});

		$( ".opener" ).click(function() {
			$( "#dialog" ).dialog( "open" );
			return false;
		});
	});
	</script>



<div class="demo">

<div id="dialog" title="Search">
	<p><form action='search.php' method='get'><p>
  <input type='text' name='search' id='search'>
</p>
<p>
  <label>
    <input type='radio' checked='checked' name='SValues' value='login_name' id='SValues_0'>
    Login Name</label>
  
 
   <input type="submit"  value="Search!" />
</p></form></p>
</div>

<div style="width:640px;margin: 50px auto;text-align:center">
<ul id="MenuBar1" class="MenuBarHorizontal">
  <li><a href="admin.php">Home</a>  </li>
  <li><a href="add.php">Add Account</a></li>
  <li><a  class='opener'>Search</a>  </li>
  <li><a href="server.php">Run Cron</a></li>
  <?php if(isset($_SESSION['u'])){
  echo "<li><a href='index.php?logout=true'>Logout</a></li>"; }else {echo "<li><a href='index.php'>Login</a></li>";}?>
</ul>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});
</script>
</div>


 <table width="100%" align="center" cellpadding="0" cellspacing="0" >
    <tr class="table_hdr1">
        <td width="2%">
            <div align="left">
              <img src="SpryAssets/images/inner/tableCorner_1.png" width="12" height="33" /></div>
        </td>
        <td width="96%" class="table_hdr">
            <div align="center">
                <strong>System Administration Reminder System</strong></div>
        </td>
        <td width="2%">
            <div align="right">
              <img src="SpryAssets/images/inner/tableCorner_2.png" width="12" height="33" /></div>
        </td>
    </tr>
	<tr style="text-align:center">
	<td></td>
	<td>
	