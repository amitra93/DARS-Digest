<?php
/*
duplicate of class.php, but with different variables. waste of code.
*/

include_once('parts/header.php');
require_once('parts/sqlCredentials.php');
if (!empty($_POST))
{
	$connection = mysqli_connect($sqlHost, $sqlUser, $sqlPass, $sqlDB);

	foreach($_POST as $key => $val)
		$_POST[$key] = mysqli_real_escape_string($connection, $val);

	mysqli_real_query($connection, sprintf("insert into requirements values('%s', '%s', '%s')", $_POST['major'], $_POST['type'], $_POST['courses'])) or die(mysqli_error($connection));

	$connection->close();
}

$major = empty($_POST['major']) ? 'major' : $_POST['major'];
$type = empty($_POST['type']) ? 'type' : $_POST['type'];
$courses = empty($_POST['courses']) ? 'courses' : $_POST['courses'];
?>

<script type="text/javascript">
var mem = ['<?php echo $major; ?>',
			'<?php echo $type; ?>',
			'<?php echo $courses; ?>'
			];

function fieldClear(id) {
	if (document.getElementById('input'+id).value == mem[id])
		document.getElementById('input'+id).value='';
}

function fieldPopulate(id)
{
	if (document.getElementById('input'+id).value == '')
		document.getElementById('input'+id).value = mem[id];
}
</script>

<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
	<input type="text" onfocus="fieldClear(0)" onblur="fieldPopulate(0)" id="input0"
		name="major" value="<?php echo $major; ?>">
	<input type="text" onfocus="fieldClear(1)" onblur="fieldPopulate(1)" id="input1"
		name="type" value="<?php echo $type; ?>">
	<input type="text" onfocus="fieldClear(2)" onblur="fieldPopulate(2)" id="input2"
		name="courses" value="<?php echo $courses; ?>">

	<input type="submit" value="Insert into Requirement DB">
</form>
<?php
include_once('parts/footer.php');
?>
