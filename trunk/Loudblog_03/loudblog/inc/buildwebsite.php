<?php header("Content-type: text/html; charset=utf-8");

// ----------------------------------------------------- //
// Loudblog                                              //
// easy-to-use audioblogging and podcasting              //
// Version 0.3 (2005-07-01)                              // 
// http://loudblog.com                                   //
//                                                       //
// Written by Gerrit van Aaken (gerrit@praegnanz.de)     //
//                                                       //
// Released under the Gnu General Public License         //
// http://www.gnu.org/copyleft/gpl.html                  //
//                                                       //
// Have Fun! Drop me a line if you like Loudblog!        //
// ----------------------------------------------------- //

//start timer
function microtime_float()
{
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}
$time_start = microtime_float();

//for developing we need some error messages
error_reporting(E_ALL);

//get database connection values
include "loudblog/custom/config.php";

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
include "loudblog/inc/functions.php";
$settings = getsettings();

//get data from database-tables and put it into arrays
dumpdata();


//Ready to rock'n'roll? Let's start building the website!

//template required by URL? Override template-setting
if (isset($_GET['template'])) {
    $settings['template'] = $_GET['template'];
}

//building the right path to required template
$templpath = $GLOBALS['templatepath'] . 
             $settings['template'] . "/index.html";

//copies template into variable
$connect = @fopen ($templpath, "rb") OR die("Unfortunately I could not find a valid template!");
$template = fread ($connect, 262144);
fclose($connect);

//includes official loudblog-tags
include "loudblog/inc/loudblogtags.php";

//includes plugins from plugins-folder
$folder = opendir('loudblog/custom/plugins'); 
while ($file = readdir($folder)) { 
    if (substr($file, -4, 4) == ".php") {
        include_once("loudblog/custom/plugins/" . $file);
    }
}

//this is special: at first we pretend that no "next page" is possible
global $nextpage;
$nextpage = false;

//start parsing!!!
echo fullparse(firstparse(hrefmagic($template)));



//--------------------------------------------------------


function firstparse ($string) {

//very first, we do the loop_postings, because we need some global data for other functions, aight?
$postparsed = parsepostings ($string);

//now we put the posting-parsing-results into the original string
if ((isset($postparsed[0])) AND ($postparsed[0] != false)) {
    foreach ($postparsed as $replace) {
        $string = str_replace($replace['origin'], $replace['parsed'], $string);
    }
}
return $string;
}


//--------------------------------------------------------


function fullparse ($string) {

//we have to look for container-tags and parse them.
$contparsed = parsecontainer ($string);

//now we put the container-parsing-results into the original string
if ((isset($contparsed[0])) AND ($contparsed[0] != false)) {
    foreach ($contparsed as $replace) {
        $string = str_replace($replace['origin'], $replace['parsed'], $string);
    }
}

//secondly, we have to look for single-tags and parse them, too.
$singleparsed = parsesingle ($string);

//now we put the single-parsing-results into the original template
if (isset($singleparsed[0])) {
    foreach ($singleparsed as $replace) {
        $string = str_replace($replace['origin'], $replace['parsed'], $string);
    }
}
return $string;
}


//--------------------------------------------------------------------
function parsepostings ($string) {
//search for postings-tags

$parsing = "";
$search = '|<(lb:loop_postings)[^>]*>.*?</\1>|s';
preg_match_all($search, $string, $matches);
$i = 0;
$parsing = false;
if (isset($matches[0])) {
    foreach ($matches[1] as $containertag) {
        $call = substr ($containertag, 3);
        $parsing[$i]['origin'] = $matches[0][$i];
        $parsing[$i]['parsed'] = call_user_func($call, $matches[0][$i]);
        $i +=1;
    } 
}
return $parsing;
}

//--------------------------------------------------------------------
function parsecontainer ($string) {
//search for container-tags

$parsing = "";
$search = '|<(lb:[_a-z][_a-z0-9]*)[^>]*>.*?</\1>|s';
preg_match_all($search, $string, $matches);
$i = 0;
$parsing = false;
if (isset($matches[0])) {
    foreach ($matches[1] as $containertag) {
        $call = substr ($containertag, 3);
        $parsing[$i]['origin'] = $matches[0][$i];
        $parsing[$i]['parsed'] = call_user_func($call, $matches[0][$i]);
        $i +=1;
    } 
}
return $parsing;
}

//--------------------------------------------------------------------
function stripcontainer ($string) {
//put those "<lb:something>content</lb:something>" tags to trash
if ($string != "") {

    $start = strpos ($string,">") + 1;
    $length= strrpos($string,"<") - strlen($string);
    $string = substr ($string, $start, $length);
}
return $string;
}

//--------------------------------------------------------------------
function parsesingle ($string) {
//search for single-tags

$parsing = "";
if ($string != "") {
    $search = '|<(lb:[_a-z][_a-z0-9]*)[^>]* />|s';
    preg_match_all($search, $string, $matches);
    $i = 0;
    $parsing = false;
    if (isset($matches[0])) {
        foreach ($matches[1] as $singletag) {
            $call = substr ($singletag, 3);
            $parsing[$i]['origin'] = $matches[0][$i];
            $parsing[$i]['parsed'] = call_user_func($call, $matches[0][$i]);
            $i +=1;
        }
    }
} 
return $parsing;
}

//--------------------------------------------------------------------
function getattributes ($string) {
//takes the whole loudblog-tag and returns the attributes as array

$att = array();
if ($string != "") {
    $string = substr($string, 0, strpos($string, ">"));
    $fragments = explode('"', strstr($string, " "));
    for ($i = 0; $i < count($fragments)-1; $i+=2) {
        $att[substr(trim($fragments[$i]), 0, -1)] = $fragments[$i+1];
    } 
}
return $att;
}

//--------------------------------------------------------------------
function hrefmagic ($string) {
//takes all relative href-links and src-links and forward to template-location

$return = false;
if ($string != "") 
{
    global $settings;
    
	$search = '#(href|src)=["\']([^/][^:"\']*)["\']#';
	$replace= '$1="loudblog/custom/templates/'.$settings['template'].'/$2"';

    $return = preg_replace ($search, $replace, $string);
}
return $return;
}



//show timer
$time_end = microtime_float();
$time = $time_end - $time_start;
echo "<!-- Loudblog built this page in $time seconds-->";

?>