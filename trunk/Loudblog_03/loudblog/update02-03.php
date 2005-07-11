<?php 

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="content-language" content="en" />
    <title>Loudblog Install</title>
    <meta name="robots" content="noindex, nofollow" />
    <meta name="description" content="Loudblog" />
    <meta name="author" content="Gerrit van Aaken" />
    
    <link rel="stylesheet" type="text/css" href="loudblog/backend/screen.css" />

</head>

<body>

<div id="wrapper">

<?php

//for developing we need some error messages
error_reporting(E_ALL);

//get database connection values
include "custom/config.php";

//create some important globals
if (!isset($lb_data)) { die("<br /><br />Cannot find a valid configuration file! <a href=\"install.php\">Install Loudblog now!</a>"); }
$GLOBALS['prefix'] = $lb_pref;
$GLOBALS['path'] = $lb_path;
$GLOBALS['audiopath'] = $lb_path . "/audio/";
$GLOBALS['uploadpath'] = $lb_path . "/upload/";
$GLOBALS['templatepath'] = $lb_path . "/loudblog/custom/templates/";

//connect to the database
mysql_connect($lb_host, $lb_user, $lb_pass) OR
die("Unfortunately I couldn't connect to the database. <br />".mysql_error());
mysql_select_db($lb_data) OR
die("Unfortunately I couldn't work with this database. <br />".mysql_error());

//make all those clever functions and settings available
include "inc/functions.php";
$settings = getsettings();

//headline
echo "<h1>Updating Loudblog</h1>\n";

//check if update is necessary
if ((isset($settings['cgi'])) and (!isset($settings['feedimage']))) {

//write new data into database
$dosql = array( 

"INSERT INTO `".$lb_pref."lb_settings` (`name`,`value`) VALUES (\"itunes_author\",\"\");",
"INSERT INTO `".$lb_pref."lb_settings` (`name`,`value`) VALUES (\"itunes_email\",\"\");",
"INSERT INTO `".$lb_pref."lb_settings` (`name`,`value`) VALUES (\"itunes_explicit\",\"0\");",
"INSERT INTO `".$lb_pref."lb_settings` (`name`,`value`) VALUES (\"copyright\",\"\");",
"INSERT INTO `".$lb_pref."lb_settings` (`name`,`value`) VALUES (\"languagecode\",\"0\");",
"INSERT INTO `".$lb_pref."lb_settings` (`name`,`value`) VALUES (\"feedcat1\",\"00-00\");",
"INSERT INTO `".$lb_pref."lb_settings` (`name`,`value`) VALUES (\"feedcat2\",\"00-00\");",
"INSERT INTO `".$lb_pref."lb_settings` (`name`,`value`) VALUES (\"feedcat3\",\"00-00\");",
"INSERT INTO `".$lb_pref."lb_settings` (`name`,`value`) VALUES (\"feedcat4\",\"00-00\");"
);

foreach ($dosql as $sql) {
$result = mysql_query($sql) OR die (mysql_error());
}

echo "<p>Updating was successful! Now please delete install.php and updateXX-XX.php!</p>";

} else { echo "<p>You don't need to update! Everything is fine. Now delete <code>install.php</code> and <code>update01-02.php</code>!</p>"; }

?>