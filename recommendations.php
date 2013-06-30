<?php
include_once('parts/sqlCredentials.php');
include_once('parts/header.php');


//ok here's how this works. i only check for 4 things since those are all the things that seem to popup. Required classes (for EE,CE,CS), ECE/CS electives (CE only), Technical electives (EE only) and Technical track (CS only)...that's it.

function rec($course,$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,&$friends_pics){
	
	$connection=mysqli_connect($sqlHost,$sqlUser,$sqlPass,$sqlDB);
	$query = "SELECT fbid FROM user NATURAL JOIN (SELECT taken2.uin, COUNT(taken2.uin) AS mostClassesTaken FROM (SELECT uin,semester,crossListedAs AS courseName FROM (SELECT uin,semester,courseName AS name FROM taken) AS T NATURAL JOIN class) AS taken1, (SELECT uin,semester,crossListedAs AS courseName FROM (SELECT uin,semester,courseName AS name FROM taken) AS T2 NATURAL JOIN class) AS taken2 WHERE taken1.uin != taken2.uin and taken1.semester = taken2.semester AND taken1.courseName = taken2.courseName and taken1.uin = (SELECT uin FROM user WHERE fbid='".$_SESSION['id']."') and taken2.uin IN (
SELECT DISTINCT uin FROM ((SELECT uin FROM (SELECT uin, fbid
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
			(SELECT DISTINCT uin FROM (SELECT uin, fbid
			FROM user 
				NATURAL JOIN report 
				NATURAL JOIN 
			(SELECT DISTINCT major, type AS secondaryGroup 
			FROM requirements 
			WHERE courseList LIKE '".$course."') AS T3
				WHERE fbid != '".$_SESSION['id']."'
				AND hidePrimary=false) AS T4
                                WHERE
                                fbid NOT IN (SELECT fbid FROM user NATURAL JOIN taken WHERE courseName='".$course."'))) AS CO
) GROUP BY taken2.uin) AS FINAL ORDER BY mostClassesTaken DESC";
	if ($connection->real_query($query) == false)
		echo $connection->error;
	$result = $connection->use_result();
	$users = $result->fetch_all();
	$result->free();
	// associate a name and picture to the facebook id, then display everything
	for ($i = 0; $i < sizeof($users); $i++){
		if (in_array($users[$i][0],$friends)){
			if (!array_key_exists($users[$i][0],$friends_pics)){
				$curl = curl_init('http://graph.facebook.com/' . $users[$i][0]);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				$name = curl_exec($curl);
				curl_close($curl);
				$name = json_decode($name);
				$name = $name->name;
				$friends_pics[$users[$i][0]] = $name;
			}
			echo '<a href="http://facebook.com/' . $users[$i][0] . '">
					<img src="http://graph.facebook.com/' . $users[$i][0] . '/picture?type=square"
					title="' . $friends_pics[$users[$i][0]] .'" /></a>' . "\n";
		}
	}
}


//ECE/CS electives
function ececselec_rec($major,$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,&$friends_pics){
	if ($major=='COMPUTER ENGINEERING'){
		$connection=mysqli_connect($sqlHost,$sqlUser,$sqlPass,$sqlDB);
		$query = "SELECT name FROM (SELECT courseList AS name FROM requirements where type='ECE/CS ELECTIVES') AS T WHERE name NOT IN (SELECT DISTINCT crossListedAs AS name FROM class NATURAL JOIN (SELECT courseName AS name FROM taken NATURAL JOIN user WHERE fbid='".$_SESSION['id']."') AS T2)";
		if ($connection->real_query($query) == false)
		echo $connection->error;
		$report = $connection->use_result();
		$classes = $report->fetch_all();
		$report->free();
		$connection->close();
		echo '<div class="contain">';
		echo '<br><a id="rec_ececse_link"><span class="bold-this">ECE/CS electives:</span></a><br>';
		echo '<div id="rec_ececse" style="display:none;">';
		echo '<table border="0" width="750">';
		for ($i = 0; $i < sizeof($classes); $i++){
			if($i % 2 == 0)
				echo '<tr>';
			echo '<td>';
			echo $classes[$i][0].'<br>';
			rec($classes[$i][0],$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,$friends_pics);
			echo '<br><br>';
			echo '</td>';
			if($i % 2 == 1)
				echo '</tr>';
		}echo '</table></div></div>';
	}
}


//technical electives
function techelec_rec($major,$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,&$friends_pics){
	if ($major=='ELECTRICAL ENGINEERING'){
		$connection=mysqli_connect($sqlHost,$sqlUser,$sqlPass,$sqlDB);
		$query = "SELECT courseOpts AS name from courseopts NATURAL JOIN user WHERE fbid='517821541' AND primaryGroup='TECHNICAL ELECTIVES'";
		if ($connection->real_query($query) == false)
		echo $connection->error;
		$report = $connection->use_result();
		$classes = $report->fetch_all();
		$report->free();
		echo '<div class="contain">';
		echo '<br><a id="rec_tecele_link"><span class="bold-this">Technical electives:</span></a><br>';
		echo '<div id="rec_tecele">';
		echo '<table border="0" width="750">';
		for ($i = 0; $i < sizeof($classes); $i++){
			if($i % 2 == 0)
				echo '<tr>';
			echo '<td>';
			echo $classes[$i][0].'<br>';
			rec($classes[$i][0],$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,$friends_pics);
			echo '<br><br>';
			echo '</td>';
			if($i % 2 == 1)
				echo '</tr>';
		}echo '</table></div></div>';
	}
}

function techtrack_rec($major,$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,&$friends_pics){
	if ($major=='COMPUTER SCIENCE - COLLEGE OF ENGINEERING'){
		$connection=mysqli_connect($sqlHost,$sqlUser,$sqlPass,$sqlDB);
		$query="SELECT courseList FROM requirements NATURAL JOIN user WHERE fbid='".$_SESSION['id']."' AND type='TECHNICAL TRACK' AND courseList NOT IN (SELECT courseName AS courseList FROM taken NATURAL JOIN user WHERE fbid='".$_SESSION['id']."')";
		if ($connection->real_query($query) == false)
		echo $connection->error;
		$report = $connection->use_result();
		$classes = $report->fetch_all();
		$report->free();
		echo '<div class="contain">';
		echo '<br><a id="rec_tectra_link"><span class="bold-this">Technical track:</span></a><br>';
		echo '<div id="rec_tectra">';
		echo '<table border="0" width="750">';
		for ($i = 0; $i < sizeof($classes); $i++){
			if($i % 2 == 0)
				echo '<tr>';
			echo '<td>';
			echo $classes[$i][0].'<br>';
			rec($classes[$i][0],$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,$friends_pics);
			echo '<br><br>';
			echo '</td>';
			if($i % 2 == 1)
				echo '</tr>';
		}echo '</table></div></div>';
	}
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
$connection->close();
echo '<br><br>';
$friends = array();
$friends_pics = array();
for ($a = 0; $a < sizeof($_SESSION['friends']['data']); $a++){
		$friends[$a] = intval($_SESSION['friends']['data'][$a]['id']);
}
echo '<div class="contain">';
echo '<a id="rec_reqcla_link"><span class="bold-this">Required classes:</span></a><br>';
echo '<div id="rec_reqcla">';
for ($i = 0; $i < sizeof($classes); $i++){
	if (explode(" ", $classes[$i][0])[0]!='OR'){
		$special = true;
		echo $classes[$i][0].'<br>';
		rec($classes[$i][0],$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,$friends_pics,$friends_most_classes_taken);
		echo '<br><br>';
	}
}echo '</div></div>';
ececselec_rec($major,$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,$friends_pics);
techelec_rec($major,$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,$friends_pics);
techtrack_rec($major,$sqlHost,$sqlUser,$sqlPass,$sqlDB,$friends,$friends_pics);

include_once('parts/footer.php');
?>
