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

// ----------------------------------------------------------------

function readonly($posting) {
if (!allowed(1,$posting)) { return "readonly=\"readonly\""; }
}

// ----------------------------------------------------------------

function allowed($action,$posting) {
//checks if the author has the right to do a certain action
//action 1 = edit a posting
//action 2 = publish a posting
//action 3 = administration tasks


//admin may do anything
if (getuserrights("admin")) { return true; }

else {
    switch ($action) {    
    
    case "1": 
        $tempreturn = false;
        if (getuserrights("edit_all")) { $tempreturn = true; }
        else { 
            if (getuserrights("edit_own") AND owner($posting))
                { $tempreturn = true; } 
        }
        return $tempreturn;
        break;
        
    case "2": 
        $tempreturn = false;
        if (getuserrights("publish_all")) { $tempreturn = true; }
        else { 
            if (getuserrights("publish_own") AND owner($posting))
                { $tempreturn = true; } 
        }
        return $tempreturn;
        break; 
           
    default:    
        return false;
        break;
    }
}

}

// ----------------------------------------------------------------

function owner($article) {
//checks if the logged author is the owner of a certain posting

$dosql = "SELECT author_id FROM ".$GLOBALS['prefix']."lb_postings 
          WHERE id='" . $article . "';";
$result = mysql_query($dosql) OR die (mysql_error());
$row = mysql_fetch_assoc($result);
if ($row['author_id'] == getuserid($_SESSION['nickname'])) { return true; }
else { return false; }
}

// ----------------------------------------------------------------

function getuserrights ($request) {
//checks if the logged user has got a certain right

$dosql = "SELECT ".$request." FROM ".$GLOBALS['prefix']."lb_authors 
          WHERE nickname='" . $_SESSION['nickname'] . "';";
$result = mysql_query($dosql) OR die (mysql_error());
$row = mysql_fetch_assoc($result);
if ($row[$request] == 1) { return true; } else { return false; }
}

// ----------------------------------------------------------------

function checker ($request) {
$x = (int) $request;
if ($x == 1) { return "checked=\"checked\" "; } else { return ""; }
}

// ----------------------------------------------------------------

function countrows ($request) {
//count rows of a certain table in database
$dosql = "SELECT COUNT(*) FROM ".$GLOBALS['prefix'] . $request . ";";
$result = mysql_query($dosql) OR die (mysql_error());
$row = mysql_fetch_assoc($result);
return $row['COUNT(*)'];
}

function max_id ($request) {
//get the highest id-number of a certain table
$dosql = "SELECT * FROM ".$GLOBALS['prefix'] . $request . ";";
$result = mysql_query($dosql) OR die (mysql_error());
$max = 0;
while ($row = mysql_fetch_assoc($result)) {
    if ($row['id'] > $max) $max = $row['id'];
}
return $max;
}


// ----------------------------------------------------------------

function getmegabyte ($request) {
//turns byte into a nice megabyte-string
$kb = $request / 1024 / 1024;
$kb = round ($kb, 1);
return $kb;
}

// ----------------------------------------------------------------

function tunefilename ($x) {
//makes an url browser- and sql-friendly

$x = str_replace(" ", "_", $x);
$x = str_replace("'", "", $x);
$x = str_replace('"', '', $x);
$x = str_replace("(", "", $x);
$x = str_replace(")", "", $x);
$x = str_replace("Õ", "", $x);
$x = str_replace(",", "", $x);
$x = str_replace("?", "", $x);
$x = str_replace("Ð", "-", $x);
$x = str_replace("#", "", $x);
$x = str_replace("+", "", $x);
$x = str_replace("&", "", $x);
$x = str_replace("\\", "", $x);
return $x;
}

// ----------------------------------------------------------------

function extractfilename ($request) {
//extracts the pure filename from a whole url

$url = parse_url($request);
$path = $url['path'];
$fragments = explode ("/", $path);
$i = 0;
while (isset($fragments[$i])) {
    $filename = $fragments[$i];
    $i += 1;
}
return $filename;
}

// ----------------------------------------------------------------

function stripsuffix($request) {
//delete the file-suffix

$request = str_replace(".mp3", "", $request);
$request = str_replace(".aac", "", $request);
$request = str_replace(".mp4", "", $request);
$request = str_replace(".m4a", "", $request);
$request = str_replace(".MP3", "", $request);
$request = str_replace(".AAC", "", $request);
$request = str_replace(".MP4", "", $request);
$request = str_replace(".wav", "", $request);
$request = str_replace(".WAV", "", $request);
$request = str_replace(".aif", "", $request);
$request = str_replace(".AIF", "", $request);
$request = str_replace(".AIFF", "", $request);
$request = str_replace(".aiff", "", $request);
return $request;
}

// ----------------------------------------------------------------

function freshaudioname () {
//build a new filename for the audio file

global $settings;

$daysec = 10000 + date("G")*3600 + date("i")*60 + date("s");
$filename = $settings['filename']."-".date("Y-m-d-").$daysec.".mp3";
return $filename;
}

// ----------------------------------------------------------------

function mime_type ($request) {
//converts the media type from Loudblog-codes to MIME

switch ($request) {
case "1": $type = "audio/mpeg";
    break;
case "2": $type = "application/octet-stream";
    break;
    
default: $type = "0";
    break;
}
return $type;
}

// ----------------------------------------------------------------

function type_mime ($request) {
//converts the media type from MIME to Loudblog-codes

switch ($request) {
case "audio/mpeg": $type = "1";
    break;
case "application/octet-stream": $type = "2";
    break;
    
default: $type = "0";
    break;
}
return $type;
}

// ----------------------------------------------------------------

function type_suffix($request) {
//gets the Loudblog-Code from a filename

$type = strtoupper(strrchr($request, "."));
switch ($type) {
case ".MP3": return "1"; break;
case ".AAC": return "2"; break;
case ".MP4": return "2"; break;
case ".M4A": return "2"; break;
case ".OGG": return "3"; break;
case ".WMA": return "4"; break;
case ".WMF": return "4"; break;
case ".WAV": return "5"; break;
case ".AIF": return "6"; break;
case ".AIFF":return "6"; break;
default: return "0"; break;
}
}

// ----------------------------------------------------------------

function getmediatypename ($request) {
//converts media type from Loudblog-code to a beautiful string

switch ($request) {
case 1: $name = "MP3";
    break;
case 2: $name = "AAC";
    break;
case 3: $name = "OGG";
    break;
case 4: $name = "WindowsMedia";
    break;
case 5: $name = "WAV";
    break;
case 6: $name = "AIFF";
    break;
    
default: $name = "N/A";
    break;
}
return $name;
}

// ----------------------------------------------------------------

function getuserid ($request) {
//gets the author_id from the nickname

$dosql = "SELECT id FROM ".$GLOBALS['prefix']."lb_authors 
          WHERE nickname='" . $request . "';";
$result = mysql_query($dosql) OR die (mysql_error());
$row = mysql_fetch_assoc($result);
return $row['id'];
}

// ----------------------------------------------------------------

function getnickname ($request) {
//gets the nickname from the author_id

$dosql = "SELECT nickname FROM ".$GLOBALS['prefix']."lb_authors 
          WHERE id='" . $request . "';";
$result = mysql_query($dosql) OR die (mysql_error());
$row = mysql_fetch_assoc($result);
return $row['nickname'];
}

// ----------------------------------------------------------------

function getfullname ($request) {
//gets the realname from the author_id

$dosql = "SELECT realname FROM ".$GLOBALS['prefix']."lb_authors 
          WHERE id='" . $request . "';";
$result = mysql_query($dosql) OR die (mysql_error());
$row = mysql_fetch_assoc($result);
return $row['realname'];
}

// ----------------------------------------------------------------

function getcategory ($request) {
//gets the category from the category_id

$dosql = "SELECT name FROM ".$GLOBALS['prefix']."lb_categories 
          WHERE id='" . $request . "';";
$result = mysql_query($dosql) OR die (mysql_error());
$row = mysql_fetch_assoc($result);
return $row['name'];
}

// ----------------------------------------------------------------

function getcategoryid ($request) {
//gets the category_id from a category name

$dosql = "SELECT id FROM ".$GLOBALS['prefix']."lb_categories WHERE 
          name='" . urlencode($request) . "' OR 
          name='" . $request . "';";
$result = mysql_query($dosql) OR die (mysql_error());
if ($row = mysql_fetch_assoc($result)) { return $row['id']; }
else { return -1; }
}

// ----------------------------------------------------------------

function getsettings () {
//putting settings into a handy array

$dosql = "SELECT * FROM ".$GLOBALS['prefix']."lb_settings;";
$result = mysql_query($dosql) OR die (mysql_error());
while ($row = mysql_fetch_assoc($result)) {
    $settings[$row['name']] = $row['value'];
}
return $settings;
}

// ----------------------------------------------------------------

function pagetitle () {
//gets current page-title from url and make it more beautiful

//does the url contain a page-information?
if (isset($_GET['page'])) {

switch ($_GET['page']) {

case "record1": $title = "Record Step 1";
    break;
case "record2": $title = "Record Step 2";
    break;
case "postings": $title = "Postings";
    break;
case "settings": $title = "Settings";
    break;
case "organisation": $title = "Organisation";
    break;

default: $title = "Postings";
    break;
}

//build even more beautiful page-title
$title = "Loudblog: " . $title;

//no page-info from url? has to be the login
} else { $title = "Loudblog: Login"; }

return $title;
}



?>