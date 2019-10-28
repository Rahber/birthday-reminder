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
echo " <form id='form1' name='form1' method='post' action='insert.php'>
  <table width='100%' border='0'>
    <tr>
      <td>Name</td>
      <td><span id='sprytextfield1'>
        <input name='login' type='text' id='login' value='' />
      <span class='textfieldRequiredMsg'>A value is required.</span></span></td>
    </tr>
    <tr>
      <td>Mobile Number</td>
      <td><span id='sprytextfield2'>
        <input type='text' name='mobile' id='mobile' />
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
    <input name='date' type='text' id='date' value='Click me to set date' />
    <span class='textfieldRequiredMsg'>A value is required.</span><span class='textfieldInvalidFormatMsg'>Invalid format.</span></span>
      </td>
    </tr>
    <tr>
      <td>Years</td>
      <td><span id='spryselect1'>
        <select name='years' id='years'>
          <option selected='selected' value='1'>1</option>

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