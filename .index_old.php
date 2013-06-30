<?php
if (!empty($_GET['red']))
{
	if ($_GET['red'] == 'prefs')
		header("Location: preferences.php");
	else
		header("Location: ".urldecode($_GET['red']));
}

include_once "parts/header.php";
?>
<center>
<img style="padding-top: 10px;" src="images/bookCover.png" title="DARS for Dummies"/>

<div class="container">  
<!-- Main hero unit for a primary marketing message or call to action -->  
<div class="leaderboard">  
<h1>An elegant way to find classes to take with others</h1>  
</center>
<p>DARS for Dummies is a semester project for CS411 built by a trio of very hardworking individuals, who go by the collective name "XtraECE". Run a DARS report with our Chrome extension installed to find out who else in your Facebook friends list needs to take the same class that you do or can take it as an elective.</p>
<center>
</div>  
<!-- Example row of columns -->  
<div class="row">  
<div class="span4">  
<h2>Step 1</h2>  
<p>Install our extension by saving the file in the link below. In Google Chrome, go to <i>tools->extensions</i> in browser settings, then drag the .CRX file into the window.</p>  
</div>  
<div class="span4">  
<h2>Step 2</h2>  
<p>Login to Facebook, authorize our app and run a DARS report. </p>  
</div>  
<div class="span4">  
<h2>Step 3</h2>  
<p>Go to our Reports page and enable/disable what you want to show to your friends. You can see who to take classes with on our Recommendations page. Now you can be anti-social while still taking classes with other people. Enjoy!</p>  
</div>  
</div>  
<hr>  
</div>
<a href="/DARSD.crx">Click here to download our Chrome extension</a>
</center>
<?php
include_once "parts/footer.php";
?>
