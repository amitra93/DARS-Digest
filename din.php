<?php
/*
This script parses JSON data posted to this page and enters it into darsfordummies_dars.report

If $_POST['force'] is given a value, the script skips redundancy and
login checks for debugging purposes.
*/

if (empty($_POST['msg']))
	die;

$json = json_decode($_POST['msg']);
require_once('parts/sqlCredentials.php');
require_once('parts/fbconnect.php');

// if not logged in
if (!empty($loginUrl) && empty($_POST['force']))
{
	$loginUrl = $facebook->getLoginUrl(array(
		'scope' => 'email',
		'redirect_uri' => 'http://darsfordummies.web.engr.illinois.edu?red='.urlencode($_POST['loc']),));
	echo '<a href="' . $loginUrl . '">Log in via Facebook</a>';
}
else
{
?>

	Parsed data received<br>
	
	<?php
	$connection = mysqli_connect($sqlHost, $sqlUser, $sqlPass, $sqlDB) or die(mysqli_error($connection));
	$uin = mysqli_real_escape_string($connection, $json->UIN);
	
	$datetime = $json->date;
	$datetime = substr($datetime, -2) . '/' . substr($datetime, 0, 5);
	$datetime = $datetime . ' ' . $json->time;
	$datetimeComparison = substr($json->date, 3, 2) . '/' . substr($json->date, 0, 2) . '/' . substr($json->date, 6);
	$datetimeComparison = '20' . substr($json->date, 6) . '-' . str_replace('/', '-', substr($json->date, 0, 5));
	$datetimeComparison .= ' ' . $json->time . ':00';
	$datetimeComparison = strtotime($datetimeComparison);
	
	if (!mysqli_real_query($connection, "select * from report where uin=$uin"))
		die(mysqli_error($connection));
	$result = mysqli_use_result($connection);
	$time = mysqli_fetch_array($result);
	$time = strtotime($time['timestamp']);
	
	$result->free();
	
	// if this is a newer dars report, then insert the data
	if ($time < $datetimeComparison || !empty($_POST['force']))
	{
		// delete the previous requirements for this UIN
		mysqli_real_query($connection, "delete from report where uin=$uin;");
		mysqli_real_query($connection, "delete from courseopts where uin=$uin;");
		mysqli_real_query($connection, "delete from taken where uin=$uin;");
		
		$base_fields = "uin, timestamp, major, primaryGroup";
		$base_values = "'$uin', '"
				. mysqli_real_escape_string($connection, $datetime) . "', '"
				. mysqli_real_escape_string($connection, $json->major) . "', ";
		
		foreach($json->results as $result => $resultData)	// result is one index of the results
		{
			// reset tuple
			$fields = $base_fields;
			$values = $base_values . "'" . mysqli_real_escape_string($connection, $resultData->primary->Group) ."'";
		
			if (!empty($resultData->primary->Hrs))
			{
				$fields .= ", primaryHrs";
				$values .= ", " . mysqli_real_escape_string($connection, $resultData->primary->Hrs);
			}
	
			if (!empty($resultData->primary->Course))
			{
				$fields .= ", primaryCourse";
				$values .= ", " . mysqli_real_escape_string($connection, $resultData->primary->Course);
	
				// try to use the course options if this insert has a Course requirement
				if (!empty($resultData->courseOpts))
				{
					addCourseOpts($resultData->courseOpts,
									mysqli_real_escape_string($connection, $resultData->primary->Group),
									NULL);
				}
			}
	
			if (!empty($resultData->secondary))
			{
				foreach($resultData->secondary as $secondaryResult => $secondaryResultObject) // $secondaryResult is index within secondary
				{
					$fieldsExtended = $fields;
					$valuesExtended = $values;
		
					foreach($secondaryResultObject as $secondaryResultAttribute => $secondaryResultData)
					{
						$fieldsExtended .= ", secondary" . mysqli_real_escape_string($connection, $secondaryResultAttribute);
						$valuesExtended .= ", '" . mysqli_real_escape_string($connection, $secondaryResultData) . "'";
					}
	
					// try to use the course options if this insert has a Course requirement
					if (!empty($secondaryResultObject->Course) && !empty($resultData->courseOpts))
					{
						addCourseOpts($resultData->courseOpts,
										mysqli_real_escape_string($connection, $resultData->primary->Group),
										mysqli_real_escape_string($connection, $secondaryResultObject->Group));
					}
	
					$fieldsExtended = mysqli_real_escape_string($connection, $fieldsExtended);
		
					// insert into database
					//echo "<br>insert into report ($fieldsExtended) values ($valuesExtended);";
					if (!mysqli_real_query($connection, "insert into report ($fieldsExtended) values ($valuesExtended);"))
					{
						die(mysqli_error($connection));
					}
				}
			}
			else
			{
				// insert into database
				//echo "<br>insert into report ($fields) values ($values);";
				if (!mysqli_real_query($connection, "insert into report ($fields) values ($values);"))
				{
					die(mysqli_error($connection));
				}
			}
		}

		// add the Taken courses to the database
		foreach ($json->taken as $key => $value)
		{
			if (substr($value, 0, 2) == 'FA')
				$date = '20' . substr($value, 2) . '-9-1';		// 20__-September 1
			else if (substr($value, 0, 2) == 'SP')
				$date = '20' . substr($value, 2) . '-2-1';		// 20__-February 1
			else if (substr($value, 0, 2) == 'SU')
				$date = '20' . substr($value, 2) . '-6-1';		// 20__-June 1

			if (!$connection->real_query("insert into taken values($uin, '$key', '$date');"))
				die(mysqli_error($connection));
		}
	}
	
	// tie this UIN to an FBID
	mysqli_real_query($connection, "select uin from user where fbid='".$_SESSION['id']."';");
	$result = mysqli_use_result($connection);
	$result = mysqli_fetch_array($result);
	$result = $result[0];
	if ($result == 0)
	{
		$query = "update user set uin=".$uin." where fbid='".$_SESSION['id']."';";
		mysqli_real_query($connection, $query);
	}
	elseif ($result != $uin) { ?>
	
		<div id="confirmUIN">
			We have your UIN previously recorded as <b><?php echo $result; ?></b>. Is this correct?<br>
			<i>(No action results in keeping the previously recorded UIN.)</i><br>
	
			<form method="GET" action="http://darsfordummies.web.engr.illinois.edu/parts/updateFb.php">
				<input type="hidden" name="auditUrl" value="" id="return">
				<input type="hidden" name="uin" value="<?php echo $uin; ?>">
				<input type="button" value="Yes" onclick="document.getElementById('confirmUIN').innerHTML=''"> my UIN is <?php echo $result; ?>.<br>
				<input type="submit" value="No"  onclick="document.getElementById('return').value=window.location.href"> my UIN is <?php echo $uin; ?>.
			</form>
		</div>
	
	<?php
	}
	mysqli_close($connection);
}

function addCourseOpts($fullString, $primaryGroup, $secondaryGroup=NULL)
{
	global $connection;
	global $uin;

	$preg = '(\w{2,5} )?\d{3}(.*?\(.+?\))?';

	$courseOpts = substr($fullString, 13);
	$courseOpts = preg_split('/,/i', $courseOpts);

	foreach ($courseOpts as $key => $value)
	{
		$courseOpts[$key] = trim($value);

		// make sure that every entry has a department, not just a number
		if (preg_match('/^(\w){2,5} (\d){3}/', $courseOpts[$key], $matches) == 0)	// if this doesn't have a department,
			$courseOpts[$key] = $dept . $courseOpts[$key];					// prepend it
		else
		{
			preg_match('/\w{2,5} /', $matches[0], $dept);			// else, set the last department to this department
			$dept = $dept[0];
		}

		// if this array entry has multiple choices for the same credit...
		if (preg_match_all("/$preg( OR $preg)+/i", $courseOpts[$key], $matches) != 0)
		{
			$temp = $courseOpts[$key];
			unset($courseOpts[$key]);
			foreach ($matches[0] as $matchKey => $matchVal)
			{
				$courseOpts[] = $matches[0][$matchKey];

				// if these OR'd choices are not the only choices in this array entry...
				if (strlen($matches[0][$matchKey]) < strlen($value))
				{
					$temp = substr($temp, strlen($matches[0][$matchKey]) + 1);// + strpos($temp, $matches[0][$matchKey]));
				}
			}
			if ($temp !== $value && preg_match_all("/$preg/i", $temp, $temp2) > 0)
			{
				foreach ($temp2[0] as $key2 => $value2)
					$courseOpts[] = $value2;
			}
		}
	}

	// new for loop; array indices dynamically added via the previous foreach loop are not iterated through by the loop
	foreach ($courseOpts as $key => $value)
	{
		$query = "insert into courseopts(uin, primaryGroup, secondaryGroup, courseOpts)
					values('$uin', '$primaryGroup', "
					. (is_null($secondaryGroup) ? 'NULL' : "'$secondaryGroup'")
					. ", '" .$connection->escape_string($courseOpts[$key]). "')";

		mysqli_real_query($connection, $query);
	}
}

?>
