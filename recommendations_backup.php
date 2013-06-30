<?php
include_once('parts/sqlCredentials.php');
include_once('parts/header.php');


//ok here's how this works. i only check for 4 things since those are all the things that seem to popup. Required classes (for EE,CE,CS), ECE/CS electives (CE only), Technical electives (EE only) and Technical track (CS only)...that's it.

function rec($course,$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,&$friends_pics,&$friends_most_classes_taken){
	
	$connection=mysqli_connect($sqlHost,$sqlUser,$sqlPass,$sqlDB);
	
	//$query = sprintf("SELECT DISTINCT major FROM user NATURAL JOIN report WHERE fbid = '%d'",$_SESSION['id']);
	//$connection->real_query($query);
	//$result = $connection->use_result();
	//$report = $result->fetch_all(MYSQLI_BOTH);
	//$result->free();
	$query = "(SELECT fbid FROM (SELECT DISTINCT fbid 
			FROM user 
				NATURAL JOIN report 
				NATURAL JOIN 
			(SELECT DISTINCT major, type AS primaryGroup 
			FROM requirements 
			WHERE courseList LIKE '".$course."') AS T
				WHERE fbid != '".$_SESSION['id']."'
				AND hidePrimary=false) AS T2
                                WHERE
                                fbid NOT IN (SELECT fbid FROM user NATURAL JOIN taken WHERE courseName='".$course."'))
			UNION
			(SELECT fbid FROM (SELECT DISTINCT fbid 
			FROM user 
				NATURAL JOIN report 
				NATURAL JOIN 
			(SELECT DISTINCT major, type AS secondaryGroup 
			FROM requirements 
			WHERE courseList LIKE '".$course."') AS T3
				WHERE fbid != '".$_SESSION['id']."'
				AND hidePrimary=false) AS T4
                                WHERE
                                fbid NOT IN (SELECT fbid FROM user NATURAL JOIN taken WHERE courseName='".$course."'))";
	if ($connection->real_query($query) == false)
		echo $connection->error;
	$result = $connection->use_result();
	$users = $result->fetch_all();
	$result->free();
	//var_dump($users);
	// associate a name and picture to the facebook id, then display everything
	for ($i = 0; $i < count($users); $i++){
		if (in_array($users[$i][0],$friends)){
			if (!array_key_exists($users[$i][0],$friends_pics)){
				$curl = curl_init('http://graph.facebook.com/' . $users[$i][0]);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				$name = curl_exec($curl);
				curl_close($curl);
				$name = json_decode($name);
				$name = $name->name;
				$friends_pics[$users[$i][0]] = $name;
				$query = "SELECT fbid, mostClassesTaken 
				FROM user 
				NATURAL JOIN 
					(SELECT taken2.uin, COUNT(taken2.uin) AS mostClassesTaken 
					FROM 
						(SELECT uin,semester,crossListedAs AS courseName 
						FROM 
							(SELECT uin,semester,courseName AS name 
							FROM taken) 
						AS T NATURAL JOIN class) AS taken1, 
						(SELECT uin,semester,crossListedAs AS courseName 
						FROM 
							(SELECT uin,semester,courseName AS name 
							FROM taken) 
						AS T2 NATURAL JOIN class) AS taken2 
					WHERE taken1.uin != taken2.uin 
					AND taken1.semester = taken2.semester 
					AND taken1.courseName = taken2.courseName 
					AND taken1.uin = 
						(SELECT uin FROM user 
						WHERE fbid = '".$_SESSION['id']."') 
					GROUP BY taken2.uin) AS T3 ORDER BY mostClassesTaken DESC";
				if ($connection->real_query($query) == false)
					echo $connection->error;
				$result = $connection->use_result();
				$mostClassesTaken = $result->fetch_all();
				$result->free();
				for ($j = 0; $j < count($mostClassesTaken); $j++){
					$friends_most_classes_taken[$mostClassesTaken[$j][0]] = $mostClassesTaken[$j][1];
				}
			}
		}
	}
	$users_copy = array();
	for ($i = 0; $i < count($users); $i++){
		$users_copy[$i] = $users[$i][0];	
	}
	//for ($i = 0; $i < sizeof($users); $i++){
	//	if (in_array($users[$i][0],$friends)){
	//		echo '<a href="http://facebook.com/' . $users[$i][0] . '">
	//			<img src="http://graph.facebook.com/' . $users[$i][0] . '/picture?type=square"
	//			title="' . $friends_pics[$users[$i][0]] .'" /></a>' . "\n";
	//	}
	for ($i = 0; $i < count($friends_most_classes_taken); $i++){
		if (in_array($friends_most_classes_taken[$i],$users_copy)){
			echo '<a href="http://facebook.com/' .$friends_most_classes_taken[$i]. '">
				<img src="http://graph.facebook.com/' . $friends_most_classes_taken[$i]. '/picture?type=square"
				title="' . $friends_pics[$friends_most_classes_taken[$i]] .'" /></a>' . "\n";
		}
	}
	
}


//ECE/CS electives
function ececselec_rec($major,$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,&$friends_pics,&$friends_most_classes_taken){
	if ($major=='COMPUTER ENGINEERING'){
		echo '<br><br><br>ECE/CS electives - ';
		$connection=mysqli_connect($sqlHost,$sqlUser,$sqlPass,$sqlDB);
		$query = "SELECT name FROM (SELECT courseList AS name FROM requirements where type='ECE/CS ELECTIVES') AS T WHERE name NOT IN (SELECT DISTINCT crossListedAs AS name FROM class NATURAL JOIN (SELECT courseName AS name FROM taken NATURAL JOIN user WHERE fbid='".$_SESSION['id']."') AS T2)";
		if ($connection->real_query($query) == false)
		echo $connection->error;
		$report = $connection->use_result();
		$classes = $report->fetch_all();
		$report->free();
		for ($i = 0; $i < sizeof($classes); $i++){
			echo '<br><br>'.$classes[$i][0].'<br>';
			rec($classes[$i][0],$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,$friends_pics,$friends_most_classes_taken);
		}
		
	}
}


//technical electives
function techelec_rec($major,$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,&$friends_pics,&$friends_most_classes_taken){
	
}


$connection=mysqli_connect($sqlHost,$sqlUser,$sqlPass,$sqlDB);
$query = "SELECT DISTINCT major FROM report NATURAL JOIN user WHERE fbid='".$_SESSION['id']."'";
if ($connection->real_query($query) == false)
	echo $connection->error;
$report = $connection->use_result();
$classes = $report->fetch_all();
$report->free();
$major = $classes[0][0];

$query = sprintf("SELECT courseOpts as courseList FROM (SELECT report.primaryGroup AS primaryGroup,
						primaryHrs,
						primaryCourse,
						report.secondaryGroup AS secondaryGroup,
						secondaryHrs,
						secondaryCourse,
						courseOpts
						FROM user,report LEFT OUTER JOIN courseopts
									ON report.uin=courseopts.uin
									AND report.primaryGroup=courseopts.primaryGroup
									AND report.secondaryGroup=courseopts.secondaryGroup
						WHERE user.uin=report.uin
							AND fbid='%d'
						ORDER BY primaryGroup) AS T WHERE primaryGroup='REQUIRED COURSES'",$_SESSION['id']);
if ($connection->real_query($query) == false)
	echo $connection->error;
$report = $connection->use_result();
$classes = $report->fetch_all();
$report->free();
echo '<br><br>';
$friends = array();
$friends_pics = array();
$friends_most_classes_taken = array();
for ($a = 0; $a < sizeof($_SESSION['friends']['data']); $a++){
		$friends[$a] = intval($_SESSION['friends']['data'][$a]['id']);
}
echo 'Required classes -';
for ($i = 0; $i < sizeof($classes); $i++){
	if (explode(" ", $classes[$i][0])[0]!='OR'){
		echo '<br><br>'.$classes[$i][0].'<br>';
		rec($classes[$i][0],$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,$friends_pics,$friends_most_classes_taken);
	}
}
ececselec_rec($major,$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,$friends_pics,$friends_most_classes_taken);
techelec_rec($major,$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,$friends_pics,$friends_most_classes_taken);
var_dump($friends_most_classes_taken);
var_dump($friends_pics);
include_once('parts/footer.php');
?>
