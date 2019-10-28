<?php

include('functions.php');

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
if(!$_POST){
$id=$_GET['id'];

$sql="Select * from info where id='$id'";
$result=mysql_query($sql);
while($row = mysql_fetch_array($result))
{
 $tp=$row['years'];
echo " <form id='form1' name='form1' method='post' action='update.php?mode=update&id=".$id."'>
  <table width='100%' border='0'>
    <tr>
      <td>Login Name</td>
      <td><span id='sprytextfield1'>
        <input name='login' type='text' id='login' value='".$row['login_name']."' />
      <span class='textfieldRequiredMsg'>A value is required.</span></span></td>
    </tr>
    <tr>
      <td>Machine Name</td>
      <td><span id='sprytextfield2'>
        <input type='text' name='mobile' id='mobile' value='".$row['mobile']."' />
      <span class='textfieldRequiredMsg'>A value is required.</span></span></td>
    </tr>
    <tr>
      <td>Birthday in Current Year</td>
      <td>
      <script>
	$(function() {
		$( '#date' ).datepicker({ autoSize: true , dateFormat: 'yy-mm-dd' });
		
	});
	</script>
    <span id='sprytextfield4'>
    <input name='date' type='text' id='date' value='".$row['birth_date']."' />
    <span class='textfieldRequiredMsg'>A value is required.</span><span class='textfieldInvalidFormatMsg'>Invalid format.</span></span>
      </td>
    </tr>
    <tr>
      <td>Years</td>

      <td><span id='spryselect1'>
        <select name='years' id='years'>
          <option >Select</option>
"; echo $tp; if($tp=='1'){echo " <option selected='selected' value='1'>1</ option> ";}else {
          echo "<option value='1'>1</option>";}
          if($tp=='2'){echo " <option selected='selected' value='2'>2</ option> ";}else {
          echo "<option value='2'>2</option>";}
          if($tp=='3'){echo " <option selected='selected' value='3'>3</ option>";}else {
          echo "<option value='3'>3</option>";}
if($tp=='4'){echo " <option selected='selected' value='4'>4</ option>";}else {
          echo "<option value='4'>4</option>";}
if($tp=='5'){echo " <option selected='selected' value='5'>5</ option>";}else {
          echo "<option value='5'>5</option>";}
if($tp=='6'){echo " <option selected='selected' value='6'>6</ option>";}else {
          echo "<option value='6'>6</option>";}
if($tp=='7'){echo " <option selected='selected' value='7'>7</ option>";}else {
          echo "<option value='7'>7</ option>";}
if($tp=='8'){echo " <option selected='selected' value='8'>8</ option>";}else {
          echo "<option value='8'>8</ option>";}
echo"
        </select>
      <span class='selectRequiredMsg'>Please select an item.</span></span></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td><input type='submit' name='Submit' id='Submit' value='Submit' /></td>
    </tr>
  </table>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
</form>";
}
}
if($_POST)
{

$id=$_GET['id'];
$name=mysql_real_escape_string($_POST['login']);
$mobile=mysql_real_escape_string($_POST['mobile']);
$date=mysql_real_escape_string($_POST['date']);
$years=mysql_real_escape_string($_POST['years']);
if($_POST){
update($name,$mobile,$date,$years,$id);
echo "<script type='text/javascript'>
<!--
window.location = 'admin.php?Updated'
//-->
</script> ";
}
}
}
?>
<?php include ('footer.php');
?>
<script type="text/javascript">
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {validateOn:["change"]});
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {validateOn:["change"]});
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "none", {validateOn:["change"]});
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1", {validateOn:["change"]});
var sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4", "date", {validateOn:["blur", "change"], format:"yyyy-mm-dd", hint:"yyyy-mm-dd"});
</script>