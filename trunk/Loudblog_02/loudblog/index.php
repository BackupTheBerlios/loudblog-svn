<?php header("Content-type: text/html; charset=utf-8");

// ----------------------------------------------------- //
// Loudblog                                              //
// easy-to-use audioblogging and podcasting              //
// Version 0.1 (2005-04-11)                              // 
// http://loudblog.com                                   //
//                                                       //
// Written by Gerrit van Aaken (gerrit@praegnanz.de)     //
//                                                       //
// Published under a Creative Commons License            //
// http://creativecommons.org/licenses/by-nc-sa/2.0/     //
//                                                       //
// Have Fun! Drop me a line if you like Loudblog!        //
// ----------------------------------------------------- //



////////////////// SOME INITIAL STUFF


//for developing we need some error messages
error_reporting(E_ALL);

//cannot find a session? start one!
//found a session? fetch the variables!
session_start();

//we want to logout? delete this session!
if ((isset($_GET['do'])) AND ($_GET['do'] == "logout")) { 
    session_unset(); session_destroy(); 
}

//get database connection values
include "custom/config.php";

//create some important globals
if (!isset($lb_data)) { die("<br /><br />Cannot find a valid configuration file! <a href=\"install.php\">Install Loudblog now!</a>"); }
$GLOBALS['prefix'] = $lb_pref;
$GLOBALS['path'] = $lb_path;
$GLOBALS['audiopath'] = $lb_path . "/audio/";
$GLOBALS['uploadpath'] = $lb_path . "/upload/";

//connect to the database
mysql_connect($lb_host, $lb_user, $lb_pass) OR
die("Unfortunately I couldn't connect to the database. <br />".mysql_error());
mysql_select_db($lb_data) OR
die("Unfortunately I couldn't work with this database. <br />".mysql_error());

//make all those clever functions available
include "inc/functions.php";
$settings = getsettings ();

//get data from database-tables and put it into arrays
dumpdata();


////////////////// CHECK THE USER-LOGIN



$access = false;

//no login-information in session-vars or post-data? no access!!
if (!isset($_SESSION['nickname']) AND !isset($_POST['nickname'])) 
    $access = false;
    
else {

    //fetch user-logins and passwords from database
    $dosql = "SELECT nickname, password FROM ".$GLOBALS['prefix']."lb_authors";
    $result = mysql_query($dosql) OR die (mysql_error());

    //compare with the login-data from session and from post
    while ($row = mysql_fetch_assoc($result)) {

        if ((isset($_SESSION['nickname']))
        AND ($row['nickname'] == $_SESSION['nickname']) 
        AND ($row['password'] == $_SESSION['password'])
        AND ($_SERVER['REMOTE_ADDR'] == $_SESSION['ipnumber'])) 
        $access = true; 
   
        else { 
            if ((isset($_POST['nickname']))
            AND ($row['nickname'] == $_POST['nickname'])
            AND ($row['password'] == md5($_POST['password'])))
            $access = true; 
        }
    }
}




////////////////// WHICH CONTENT DO WE SHOW?


//show the html-head which is always needed
include "inc/head.php";

//show the login-screen if access is denied
if (!$access) include "inc/backend_login.php";

//do other things if access is granted
else {

    //write login-data into session-data (if needed)
    if (!isset($_SESSION['nickname'])) {
        session_register ('nickname');
        session_register ('password');
        session_register ('ipnumber');
        session_register ('authorid');
        $_SESSION['nickname'] = $_POST['nickname'];
        $_SESSION['authorid'] = getuserid($_POST['nickname']);
        $_SESSION['password'] = md5($_POST['password']);
        $_SESSION['ipnumber'] = $_SERVER['REMOTE_ADDR'];
    }

    //no url request? show postings as default
    if (!isset($_GET['page'])) { $loadme = "inc/backend_postings.php"; }

    //build an include-path from the url-request
    else {
    $loadme = "inc/backend_" . $_GET['page'] . ".php";
    }
    
    //yee-hah! finally we do show real content on our page!
    include ($loadme);
    
}


include "inc/footer.php";

?>