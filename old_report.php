<?php
include_once('parts/sqlCredentials.php');
include_once('parts/header.php');

$connection=mysqli_connect($sqlHost,$sqlUser,$sqlPass,$sqlDB);
$query = sprintf("SELECT DISTINCT major FROM user NATURAL JOIN report WHERE fbid = '%d'",$_SESSION['id']);
$connection->real_query($query);
$result = $connection->use_result();
$report = $result->fetch_all(MYSQLI_BOTH);
$result->free();
if (is_array($report[0])){
	foreach ($report[0] as $key => $value){
		$major = $value;
	}
}
$query = sprintf("SELECT report.primaryGroup AS primaryGroup,
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
						ORDER BY primaryGroup",$_SESSION['id']);
$connection->real_query($query);
$result = $connection->use_result();
$report = $result->fetch_all(MYSQLI_BOTH);
$result->free();

$curl = curl_init('http://graph.facebook.com/' . $_SESSION['user_profile']['username']);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$name = curl_exec($curl);
curl_close($curl);
$name = json_decode($name);
$name = $name->name;
echo '<div id="profile_head">';
	echo '<br><a id="profile_picture" href="http://facebook.com/' . $_SESSION['user_profile']['username'] . '">
		<img src="http://graph.facebook.com/' . $_SESSION['user_profile']['username'] . '/picture?type=large"
		title="' . $name .'" /></a><br>'; // my picture
	echo '<div id="profile_info">';
		echo '<span id="profile_name">'.$_SESSION['user_profile']['name'].'</span>'.'<br>'; // my name
		echo ucwords(strtolower($major)).'<br>'; // Computer Science
		//print $_SESSION['id'].'<br>'; // facebook id
		if (is_array($report[0])) {
			foreach ($report as $rowIndex => $row) {
				if (!is_null($row['primaryHrs']) && $row['primaryGroup']=='MINIMUM OF 128 HOURS REQUIRED') {
					echo '<br>Needs '.$row['primaryHrs'].' hours to graduate.<br>';
				}
			}
		}

	echo '</div>';
echo '</div>';


?>
<link href="parts/css/table.css" rel="stylesheet">
<div id="course_options">
<?php
	
	$first = true;
	$rowNo = 0;
	$technicalElectivesPrinted = false;
	$rflag = false;
	$tflag = false;
	if (is_array($report[0]))
	{
		// display the results
		foreach ($report as $rowIndex => $row)
		{
			$first = false;
			if (!is_null($row['primaryHrs']) && $row['primaryGroup']=='ECE/CS ELECTIVES'){
				echo '<br>ECE/CS Electives needed - '.$row['primaryHrs'].' hours<br><a class="choose_from" href="#">Choose from:</a><br>';
				$query = "SELECT name, title, courseCatalogLink FROM class NATURAL JOIN (SELECT DISTINCT crossListedAs AS name FROM class NATURAL JOIN (SELECT courseList AS crossListedAs FROM `requirements` WHERE type='ECE/CS ELECTIVES' AND MAJOR='COMPUTER ENGINEERING') AS T) AS T2";
				if ($connection->real_query($query)){
					$result = $connection->use_result();
					$classRow = $result->fetch_all(MYSQLI_BOTH);
					$result->free();
					echo '<div class="choose_from_opts" style="display: none;">';
					for ($i = 0; $i<count($classRow); $i++){
						echo 'Choose from --> <a href="' . $classRow[$i]['courseCatalogLink'] . '">'.$classRow[$i]['name']. ': ' .$classRow[$i]['title']. '</a><br>';
					}
				}
			}
			if (!$technicalElectivesPrinted && !is_null($row['primaryHrs']) && $row['primaryGroup']=='TECHNICAL ELECTIVES'){
				echo '<br>Technical Electives needed - '.$row['primaryHrs'].' hours<br>';
				$technicalElectivesPrinted = true;
			}
			if ($row['primaryGroup']=='UNIVERSITY RESIDENCY REQUIREMENT'){
				echo '<br>University Residency Requirement - '.$row['secondaryHrs'].' hours<br><br>';
			}
			/*if (!is_null($row['primaryHrs']) && $row['primaryGroup']=='MINIMUM OF 128 HOURS REQUIRED'){
				echo '<br>Hours needed to graduate - '.$row['primaryHrs'].' hours<br><br>';
			}*/
			if (!is_null($row['primaryHrs']) && strpos($row['primaryGroup'],'MINOR')!==false){
				echo $row['primaryGroup'].' - '.$row['primaryHrs'].' hours<br>';
			}
			if ($row['primaryGroup']=='ADVANCED COMPOSITION REQUIREMENT'){
				echo '<br><div class="req_title">Advanced Composition Requirement Needed - '.$row['primaryCourse'].' Course Required.<input class="req_toggle" type="checkbox"></div><a class="advcom_choose_from" href="#">Choose from:</a><br>';
				$query = sprintf("SELECT name,title,courseCatalogLink FROM class NATURAL JOIN (SELECT courseList AS name FROM `requirements` WHERE major='%s' AND type='ADVANCED COMPOSITION REQUIREMENT') AS T",$major);
				if ($connection->real_query($query)){
					$result = $connection->use_result();
					$classRow = $result->fetch_all(MYSQLI_BOTH);
					$result->free();
					echo '<div class="advcom_choose_from_opts" style="display: none;">';
					for ($i = 0; $i<count($classRow); $i++){
						echo 'Choose from --> <a href="' . $classRow[$i]['courseCatalogLink'] . '">'.$classRow[$i]['name']. ': ' .$classRow[$i]['title']. '</a><br>';
					}
					echo '</div>';
				}
			}
			if ($row['primaryGroup']=='TECHNICAL TRACK'){
				echo '<br>Technical Track needed - '.$row['primaryHrs'].' hours<br>';
			}



			for ($i = 0; $i < (sizeof($row))/2; $i++)
			{
				
				if ($i == (sizeof($row))/2 - 1	// if this is the last column
					&& (preg_match_all('/(\w{2,5} \d{3})(.*?(\(.+?\))+?)?/', $row[$i], $matches) > 0))	// and this has course options
				{
					foreach ($matches[1] as $matchKey => $match)
					{
						if ($connection->real_query(sprintf("SELECT * FROM class WHERE name='%s'", $match)))
						$result = $connection->use_result();
						$classRow = mysqli_fetch_array($result);
						$result->free();
						if (is_array($classRow))
						{
							if ($matchKey > 0)
								echo " OR ";
						
							if ($row['primaryGroup']=='REQUIRED COURSES'){
								echo 'Required --> ';
								echo '<a href="' . $classRow['courseCatalogLink'] . '">'. $matches[0][$matchKey] . ': ' . $classRow['title'] . '</a><br>';
						
							}

							}
						else{
							//echo "<br>Shouldn't be here -->";
							//echo substr($row[$i], strpos($row[$i], $match), strlen($match)).'<br>';
							$choices = explode(' ',$row[$i]);
							$secondChoice = $choices[0].' '.$choices[3];
							if ($connection->real_query(sprintf("SELECT * FROM class WHERE name='%s'", $secondChoice)))
							$result = $connection->use_result();
							$classRow = mysqli_fetch_array($result);
							$result->free();
							if (is_array($classRow))
							{
								if ($row['primaryGroup']=='REQUIRED COURSES'){
									echo 'or --> ';
									echo '<a href="' . $classRow['courseCatalogLink'] . '">'. $secondChoice . ': ' . $classRow['title'] . '</a><br>';
							
								}
							}
						}
					}
				}
			}



			for ($i = 0; $i < (sizeof($row))/2; $i++)
			{
				
				if ($i == (sizeof($row))/2 - 1	// if this is the last column
					&& (preg_match_all('/(\w{2,5} \d{3})(.*?(\(.+?\))+?)?/', $row[$i], $matches) > 0))	// and this has course options
				{
					foreach ($matches[1] as $matchKey => $match)
					{
						if ($connection->real_query(sprintf("SELECT * FROM class WHERE name='%s'", $match)))
						$result = $connection->use_result();
						$classRow = mysqli_fetch_array($result);
						$result->free();
						if (is_array($classRow))
						{
							if ($matchKey > 0)
								echo " OR ";
						
							if ($row['primaryGroup']=='TECHNICAL ELECTIVES'){
								echo 'Required --> ';
								echo '<a href="' . $classRow['courseCatalogLink'] . '">'. $matches[0][$matchKey] . ': ' . $classRow['title'] . '</a><br>';
						
							}

							}
						else{
							//echo "<br>Shouldn't be here -->";
							//echo substr($row[$i], strpos($row[$i], $match), strlen($match)).'<br>';
							$choices = explode(' ',$row[$i]);
							$secondChoice = $choices[0].' '.$choices[3];
							if ($connection->real_query(sprintf("SELECT * FROM class WHERE name='%s'", $secondChoice)))
							$result = $connection->use_result();
							$classRow = mysqli_fetch_array($result);
							$result->free();
							if (is_array($classRow))
							{
								if ($row['primaryGroup']=='TECHNICAL ELECTIVES'){
									echo 'or --> ';
									echo '<a href="' . $classRow['courseCatalogLink'] . '">'. $secondChoice . ': ' . $classRow['title'] . '</a><br>';
							
								}
							}
						}
					}
				}
			}





			// adjust counter for row number
			$rowNo = (++$rowNo)%2;
		}
		
	}
	else { ?>
		We do not have a DARS Report on file for you.
		(Some nerve you've got for coming back here, if you deleted yourself from our database.)<br><br>
		<a href="https://darsweb.admin.uillinois.edu/darswebstu_uiuc/servlet/EASDarsServlet"
			target="_blank">Run a new DARS Report</a>
		with our extension installed to populate this page. (You'll have to refresh this page after you do that.)
	<?php }
	// free the connection
	$connection->close();
?>
<?php
include_once('parts/footer.php');
?>
</div>
