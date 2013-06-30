<?php
include_once('parts/header.php');
?>

<?php
	$connection = mysqli_connect($sqlHost, $sqlUser, $sqlPass, $sqlDB);
	mysqli_real_query($connection, "select hidePrimary from user where fbid='".$_SESSION['id']."';");
	$result = mysqli_use_result($connection);
	$priv = mysqli_fetch_array($result);
	$priv = intval($priv['hidePrimary']);

	$result->free();
	$connection->close();
?>

Hello, <?php echo $_SESSION['user_profile']['first_name']; ?>.<br><br>
These are your privacy settings for what DARS information will be 
made available to other users of this tool.<br><br>

<select id="pref" style="width:300px">
	<option value="0" <?php echo ($priv==0) ? 'selected' : ''; ?>>
		Make information available to our recommendations engine</option>
	<option value="1" <?php echo ($priv==1) ? 'selected' : ''; ?>>
		Hide information from our recommendations engine</option>
</select>
<span id="confirmDone" style="background-color:#AAFFAA;opacity:0;visibility:'hidden';padding:10px">
	Changes saved
</span>

<br><br>

You may also delete all data about you that we have saved.
Using this functionality will also log you out of this application.
<br>
<button value="Delete" onclick="if (confirm('This will delete you from our database until you log in and use our app again.')) {window.location='logout.php?DELETEALL=TRUE'}">Delete me from the database</button>

<script type="text/javascript">
document.getElementById('pref').addEventListener('change', updatePref);

function updatePref()
{
        obj = document.getElementById('pref');
        val = obj.options[obj.selectedIndex].value;
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.open("get","http://darsfordummies.web.engr.illinois.edu/parts/updatePreferences.php?val="+val, true);
        xmlhttp.send();
        xmlhttp.onreadystatechange = function()
        {
                // server response has been received
                if(xmlhttp.readyState==4)
                {
					trigger(document.getElementById('confirmDone'));
                }
        }
}

var tOuts = [];
function trigger(element)
{
	element.style.visibility='visible';
	element.style.opacity=1.0;

	for (j=0; j<tOuts.length; j++)
		clearTimeout(tOuts[j]);
	tOuts = [];

	tOuts.push(
	setTimeout(function() {
		for (i=0; i<19; i++)
		{
			tOuts.push(setTimeout(function() {	element.style.opacity-=0.05; }, (i+1)*100));
		}
		tOuts.push(setTimeout(function() { element.style.opacity=0; element.style.visibility='hidden'; }, 2000));

	}, 4000)
	);
}
</script>

<?php
include_once('parts/footer.php');
?>
