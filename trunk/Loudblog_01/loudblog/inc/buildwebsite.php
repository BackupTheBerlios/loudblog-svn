<?php

// ----------------------------------------------------- //
// Loudblog                                              //
// easy-to-use audioblogging and podcasting              //
// Version 0.1 (2005-04-11)                              // 
// http://loudblog.com                                   //
//                                                       //
// Written by Gerrit van Aaken (gerrit@praegnanz.de)     //
//                                                       //
// Published under a Creative Commons License            //
// http://creativecommons.org/licenses/by-nc-sa/2.0      //
//                                                       //
// Have Fun! Drop me a line if you like Loudblog!        //
// ----------------------------------------------------- //

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


//Ready to rock'n'roll? Let's start building the website!


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
    if (substr($file, -4, 4) == ".php") include "loudblog/custom/plugins/".$file;
}

echo fullparse(hrefmagic($template));


//--------------------------------------------------------


function fullparse ($string) {

//first we have to look for container-tags and parse them.
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
//put those "<lb:something>content</lb_something>" tags to trash
if ($string != "") {
    $string = strstr($string,">");
    $string = substr ($string, 1, strrpos($string,"<") - 1);
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

$att = "";
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
if ($string != "") {
    global $settings;
    $newpath_h = 'href="loudblog/custom/templates/' . $settings['template'] . '/';  
    $newpath_s = 'src="loudblog/custom/templates/' . $settings['template'] . '/';  
    
    //avoiding slow RegEx-Engine. filtering absolute hyperlinks
    $string = str_replace('href="http://', '2764rtte24dh="http://', $string);
    $string = str_replace('href="ftp://', '2764rtte24dh="ftp://', $string);
    $string = str_replace('src="http://', '2764rtte24ds="http://', $string);
    $string = str_replace('src="ftp://', '2764rtte24ds="ftp://', $string);
    
    //forward relative hyperlinks
    $string = str_replace('href="', $newpath_h, $string); 
    $string = str_replace('src="', $newpath_s, $string); 
    
    //re-replace absolute hyperlinks
    $string = str_replace('2764rtte24dh', 'href', $string);
    $string = str_replace('2764rtte24ds', 'src', $string);
    $return = $string;
}
return $return;
}




?>