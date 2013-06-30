<?php
/*
duplicate of requirements.php, but with different variables. waste of code.
*/

include_once('parts/header.php');
require_once('parts/sqlCredentials.php');
if (!empty($_POST))
{
	$connection = mysqli_connect($sqlHost, $sqlUser, $sqlPass, $sqlDB);

	foreach($_POST as $key => $val)
		$_POST[$key] = mysqli_real_escape_string($connection, $val);

	mysqli_real_query($connection, sprintf("insert into class values('%s', '%s', '%s', %d)", $_POST['name'], $_POST['title'], $_POST['link'], $_POST['credit'])) or die(mysqli_error($connection));

	$connection->close();
}

$name = empty($_POST['name']) ? 'name' : $_POST['name'];
$title = empty($_POST['title']) ? 'title' : $_POST['title'];
$link = empty($_POST['link']) ? 'link' : $_POST['link'];
$credit = empty($_POST['credit']) ? 'credit' : $_POST['credit'];
?>

<script type="text/javascript">
var mem = ['<?php echo $name; ?>',
			'<?php echo $title; ?>',
			'<?php echo $link; ?>',
			'<?php echo $credit; ?>'
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
		name="name" value="<?php echo $name; ?>">
	<input type="text" onfocus="fieldClear(1)" onblur="fieldPopulate(1)" id="input1"
		name="title" value="<?php echo $title; ?>">
	<input type="text" onfocus="fieldClear(2)" onblur="fieldPopulate(2)" id="input2"
		name="link" value="<?php echo $link; ?>">
	<input type="text" onfocus="fieldClear(3)" onblur="fieldPopulate(3)" id="input3"
		name="credit" value="<?php echo $credit; ?>">

	<input type="submit" value="Insert into Class DB">
</form>
<?php
include_once('parts/footer.php');
?>
