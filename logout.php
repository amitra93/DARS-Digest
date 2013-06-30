<?php
if (!session_id())
	session_start();

if ($_GET['DELETEALL'] == 'TRUE')
{
	$uinTables = ['courseopts', 'taken', 'report'];
	require_once('parts/sqlCredentials.php');
	$connection = mysqli_connect($sqlHost, $sqlUser, $sqlPass, $sqlDB);
	foreach ($uinTables as $key => $value)
		mysqli_real_query($connection, sprintf("DELETE FROM $value WHERE uin = (SELECT uin FROM user WHERE fbid='%d')",$_SESSION['id']));
	mysqli_real_query($connection, "delete from user where fbid='".$_SESSION['id']."';");
	mysqli_close($connection);
}

session_unset();
header("Location:/");
exit;
?>
