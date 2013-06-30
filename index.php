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
<table border="0" width="100%" cellpadding="10">
<tr valign="top">
<td width="30%">
<img style="padding-top: 10px;" src="images/bookCover.png" title="DARS for Dummies"/>
</td>
<td>
<h1>An elegant way to find classes to take with friends</h1>
<p>DARS for Dummies is a semester project for CS411 built by a trio of very hardworking individuals, who go by the collective name "XtraECE". Run a DARS report with our Chrome extension installed to find out who else in your Facebook friends list needs to take the same class that you do or can take it as an elective.</p>
<center>
<table border="0" width="99%" cellpadding="10">
<tr valign="top">
<td width="33%">
<h2>Step 1</h2>  
<p>Install our extension.</p>
<p>To do this, save the <a href="/download">.CRX file</a>. In Google Chrome, go to <i>tools->extensions</i> in browser settings, then drag the .CRX file into the window.</p>
<p>(You can then delete the .CRX file that you downloaded.)</p>
</td>
<td width="33%">
<h2>Step 2</h2>  
<p>Log in to our site via Facebook, authorize our app to use your Facebook information, and run a <a href="https://darsweb.admin.uillinois.edu/darswebstu_uiuc/servlet/EASDarsServlet">DARS report</a>. </p>  
</td>
<td width="33%">
<h2>Step 3</h2>  
<p>Go to our <a href="/report">Reports</a> page and enable/disable what you want to show to your friends.</p>
<p>You can see who to take classes with on our <a href="/recommendations">Recommendations</a> page.</p>
<p>Now you can be anti-social while still taking classes with other people. Enjoy!</p>
</td>
</tr>
</table>
<hr>  
</center>
</td>
</tr>
</table>
<?php
include_once "parts/footer.php";
?>
