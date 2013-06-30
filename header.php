<?php
include_once "parts/fbconnect.php";
?>
<!DOCTYPE html>
<html>
  <head>
    <title>DARS for Dummies</title>
	<link rel="icon" 
      type="image/ico" 
      href="images/favicon.ico">
    <!-- Bootstrap -->
    <!--link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="parts/css/kstyle.css" rel="stylesheet"-->
    <link href="metro/css/bootstrap.css" rel="stylesheet">
    <link href="metro/css/bootstrap-responsive.css" rel="stylesheet">
    <link href="metro/css/m-styles.min.css" rel="stylesheet">
    <link href="metro/css/m-forms.min.css" rel="stylesheet">
    <link href="metro/css/m-buttons.css" rel="stylesheet">
    <link href="metro/css/custom.css" rel="stylesheet">
    <link href="metro/css/jquery.fancybox-1.3.4.css" rel="stylesheet">
    <link href="metro/css/kstyles.css" rel="stylesheet"> 
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300' rel='stylesheet' type='text/css'>
  </head>
  <body>
    <nav class="navbar navbar-fixed-top navbar-inverse">
      <div class="navbar-inner" >
        <div class="container">
          <ul class="nav">
             <a class="brand" href="/"> <img src="images/header.png"></a>
            <li <?php echo ($_SERVER['PHP_SELF'] == '/index.php') ? 'class="active"' : '';?>><a href="/">Home</a></li>
            <li <?php echo ($_SERVER['PHP_SELF'] == '/class.php') ? 'class="active"' : '';?>><a href="class">Class</a></li>
			<li <?php echo ($_SERVER['PHP_SELF'] == '/report.php') ? 'class="active"' : '';?>><a href="report">Report</a></li>
			<li <?php echo ($_SERVER['PHP_SELF'] == '/requirements.php') ? 'class="active"' : '';?>><a href="requirements">Requirements</a></li>
			<li <?php echo ($_SERVER['PHP_SELF'] == '/preferences.php') ? 'class="active"' : '';?>><a href="preferences">Preferences</a></li>
            <li>
              <?php       
              if (isset($_SESSION['user'])) echo '<a href="logout">Logout</h5></a>';
              else echo '<a href="' . $loginUrl . '">Login</a>';
              ?>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <div class="container">
      <div class="row">
        <div class="span12">
			<?php
			if (empty($_SESSION['user']) && ($_SERVER['PHP_SELF'] != '/index.php'))
			{
				$loginUrl = $facebook->getLoginUrl(array(
					'scope' => 'email',
					'redirect_uri' => 'http://darsfordummies.web.engr.illinois.edu?red='.urlencode($_SERVER['PHP_SELF'])));
				echo '<a href="' . $loginUrl . '">Log in via Facebook</a>';
				include('parts/footer.php');
				die;
			}
