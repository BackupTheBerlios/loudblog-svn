<?php

// ----------------------------------------------------- //
// Loudblog                                              //
// easy-to-use audioblogging and podcasting              //
// Version 0.2 (2005-05-13)                              // 
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

function dumpdata() {
//fetch all category data from database and create a global array
global $catsdump;
$dosql = "SELECT * FROM ".$GLOBALS['prefix']."lb_categories;";
$result = mysql_query($dosql) OR die (mysql_error());
$i = 0;
while ($catsdump[$i] = mysql_fetch_assoc($result)) { $i += 1; }

//fetch all author data from database and create a global array
global $authordump;
$dosql = "SELECT * FROM ".$GLOBALS['prefix']."lb_authors;";
$result = mysql_query($dosql) OR die (mysql_error());
$i = 0;
while ($authordump[$i] = mysql_fetch_assoc($result)) { $i += 1; }
}


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

function countcomments ($id) {
//count number of comment of a certain posting
$dosql = "SELECT COUNT(*) FROM ".$GLOBALS['prefix']."lb_comments ";
$dosql .= "WHERE posting_id = '".$id."';";
$result = mysql_query($dosql) OR die (mysql_error());
$row = mysql_fetch_assoc($result);
return $row['COUNT(*)'];
}

// ----------------------------------------------------------------

function addToUrl ($att, $value) {
//returns a string with the attributes of the current URL plus the given one 

$return = "";

foreach ($_GET as $oldatt => $oldvalue) {
    if ($oldatt != $att) {
        $return .= "&".$oldatt."=".$oldvalue;
    }
}
$return .= "&".$att."=".$value;
return "?".substr($return, 1);
}

// ----------------------------------------------------------------

function getminutes ($sec) {
//turns seconds into "3:33" scheme

$min = (int) ($sec / 60);
$min2 = $sec%60;
if ($min2 < 10) { $min2 = "0" . $min2; }
return $min.":".$min2;
}

// ----------------------------------------------------------------

function getseconds ($request) {
//turns a "3:33" string into pure seconds

$pieces = explode (":", $request);
$sec = $pieces[0] * 60;
$sec += $pieces[1]; 
return $sec;
}

// ----------------------------------------------------------------

function getmegabyte ($request) {
//turns byte into a nice megabyte-string
$mb = $request / 1024 / 1024;
$mb = round ($mb, 1);
if ($mb == 0) { $mb = 0.1; }
if ($request < 10) { $mb = 0; };
return $mb;
}

// ----------------------------------------------------------------

function uploadlimit() {
//calculates the upload-via-browser size limit
$load = ini_get('upload_max_filesize');
$post = ini_get('post_max_size');

$load = trim($load);
$last = strtolower($load{strlen($load)-1});
switch($last) {
    case 'g': $load *= 1024;
    case 'm': $load *= 1024;
    case 'k': $load *= 1024;
}

$post = trim($post);
$last = strtolower($post{strlen($post)-1});
switch($last) {
    case 'g': $post *= 1024;
    case 'm': $post *= 1024;
    case 'k': $post *= 1024;
}

if ($post <= $load) { return $post; } else { return $load; }
}


// ----------------------------------------------------------------

function tunefilename ($x) {
//makes an url browser- and sql-friendly

$x = str_replace(" ", "_", $x);
$x = str_replace("'", "", $x);
$x = str_replace('"', '', $x);
$x = str_replace("(", "", $x);
$x = str_replace(")", "", $x);
$x = str_replace("‚Äô", "", $x);
$x = str_replace(",", "", $x);
$x = str_replace("?", "", $x);
$x = str_replace("‚Äì", "-", $x);
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

function freshaudioname ($suffix, $prefix) {
//build a new filename for the audio file

if ((!isset($suffix)) OR (trim($suffix) == "")) { $suffix = ".mp3"; }
$daysec = 10000 + date("G")*3600 + date("i")*60 + date("s");
$filename = $prefix."-".date("Y-m-d-").$daysec.$suffix;
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
case ".MOV": return "7"; break;
case ".AVI": return "8"; break;
default: return "0"; break;
}
}

// ----------------------------------------------------------------

function getmediatypename ($request) {
//converts media type from Loudblog-code to a beautiful string

switch ($request) {
case 1: $name = "MP3";
    break;
case 2: $name = "AAC/MPEG-4";
    break;
case 3: $name = "Ogg Vorbis";
    break;
case 4: $name = "WindowsMedia";
    break;
case 5: $name = "WAV";
    break;
case 6: $name = "AIFF";
    break;
case 7: $name = "QuickTime";
    break;
case 8: $name = "AVI";
    break;
    
default: $name = "N/A";
    break;
}
return $name;
}

// ----------------------------------------------------------------

function getdata ($table, $giverow, $givevalue, $getrow) {
//gets the author_id from the nickname

switch ($table) {

case "categories": 
    global $catsdump;
    foreach ($catsdump as $cat) { 
        if ($cat[$giverow] == $givevalue) {
            return $cat[$getrow];
        }
    }
    break;
    
case "authors": 
    global $authordump;
    foreach ($authordump as $author) { 
        if ($author[$giverow] == $givevalue) {
            return $author[$getrow];
        }
    }
    break;
}

}


// ----------------------------------------------------------------

function getuserid ($request) {
//gets the author_id from the nickname
global $authordump;

foreach ($authordump as $author) { 
    if ($author['nickname'] == $request) {
        return $author['id'];
    }
}
}

// ----------------------------------------------------------------

function getnickname ($request) {
//gets the nickname from the author_id
global $authordump;

foreach ($authordump as $author) { 
    if ($author['id'] == $request) {
        return $author['nickname'];
    }
}
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

function gettitlefromid ($id) {
//gets the title of a posting from the id

$dosql = "SELECT title FROM ".$GLOBALS['prefix']."lb_postings 
          WHERE id='" . $id . "';";
$result = mysql_query($dosql) OR die (mysql_error());
$row = mysql_fetch_assoc($result);
return $row['title'];
}

// ----------------------------------------------------------------

function killentities($text) {
$trans = get_html_translation_table(HTML_ENTITIES);
$trans["x"] = '&rsquo;';
$trans["'"] = '&#039;';
$trans["y"] = '&euro;';
$trans[" "] = ' ';

foreach($trans as $k => $v) { 
    $ttr[$v] = "";
}
return strtr($text, $ttr);
}

// ----------------------------------------------------------------

function getcategory ($request) {
//gets the category from the category_id
global $catsdump;

foreach ($catsdump as $cat) { 
    if ($cat['id'] == $request) {
        return $cat['name'];
    }
}
}

// ----------------------------------------------------------------

function getcategoryid ($request) {
//gets the category_id from a category name
global $catsdump;

foreach ($catsdump as $cat) { 
    if (trim($cat['name']) == $request) {
        return $cat['id'];
    }
}
}

// ----------------------------------------------------------------

function getcategoryidshort ($request) {
//gets the category_id from a shortened category name
global $catsdump;

foreach ($catsdump as $cat) { 
    if (killentities($cat['name']) == $request) {
        return $cat['id'];
    }
}
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


// ----------------------------------------------------------------

function makehtml ($text) {
//transform a given text with the preferred html-helper
global $settings;

    //include a markup-helper and make use of it
    switch ($settings['markuphelp']) {
    case "1":
        include_once ($GLOBALS['path'].'/loudblog/inc/markuphelp/textile.php');
        $textile = new Textile;
        $temphtml = $textile->TextileThis($text);
        break;
    case "2":
        include_once ($GLOBALS['path'].'/loudblog/inc/markuphelp/markdown.php');
        $temphtml = Markdown($text);
        $temphtml = htmlentities($temphtml, ENT_QUOTES, "UTF-8");
        break;
    case "3":
        include_once ($GLOBALS['path'].'/loudblog/inc/markuphelp/stringparser_bbcode.class.php');
        include_once ($GLOBALS['path'].'/loudblog/inc/markuphelp/bbcode.php');
        $temphtml = $bbcode->parse($text);
        $temphtml = htmlentities($temphtml, ENT_QUOTES, "UTF-8");
        break;
    case "0":
        $temphtml = htmlentities($text, ENT_QUOTES, "UTF-8");
        break;
    }
return $temphtml;
}

// ----------------------------------------------------------------

function checksuffix ($file) {
//getting the current id3-tags!

$suffix = strtoupper(strrchr($file, "."));
if (
    ($suffix == ".MP3") OR
    ($suffix == ".AAC") OR
    ($suffix == ".MP4") OR
    ($suffix == ".M4A") OR
    ($suffix == ".OGG") OR
    ($suffix == ".WMA") OR
    ($suffix == ".WMF") OR
    ($suffix == ".WAV") OR
    ($suffix == ".AIF") OR
    ($suffix == ".AIFF") OR
    ($suffix == ".MOV") OR
    ($suffix == ".AVI")
    ) { 
    return true; 
} else { return false; }
}



// ----------------------------------------------------------------

function getid3data ($filename, $where) {
//getting the current id3-tags!

if ($where == "back") { require_once('inc/id3/getid3.php'); }
else { require_once('loudblog/inc/id3/getid3.php'); }
$getID3 = new getID3;
$getID3->encoding = 'UTF-8';
$fileinfo = $getID3->analyze($filename);



//TITLE---------------------------
$title = "";
if (isset($fileinfo['id3v2']['comments']['title'][0])) {
        $title = $fileinfo['id3v2']['comments']['title'][0]; 
} else {
    if (isset($fileinfo['id3v1']['title'])) {
        $title = $fileinfo['id3v1']['title'];
    }
}

//ARTIST---------------------------
$artist = "";
if (isset($fileinfo['id3v2']['comments']['artist'][0])) {
        $artist = $fileinfo['id3v2']['comments']['artist'][0]; 
} else {
    if (isset($fileinfo['id3v1']['artist'])) {
        $artist = $fileinfo['id3v1']['artist'];
    }
}

//ALBUM---------------------------
$album = "";
if (isset($fileinfo['id3v2']['comments']['album'][0])) {
        $album = $fileinfo['id3v2']['comments']['album'][0]; 
} else {
    if (isset($fileinfo['id3v1']['album'])) {
        $album = $fileinfo['id3v1']['album'];
    }
}

//YEAR---------------------------
$year = "";
if (isset($fileinfo['id3v2']['comments']['year'][0])) {
        $year = $fileinfo['id3v2']['comments']['year'][0]; 
} else {
    if (isset($fileinfo['id3v1']['year'])) {
        $year = $fileinfo['id3v1']['year'];
    }
}

//TRACK---------------------------
$track = "";
if (isset($fileinfo['id3v2']['comments']['track'][0])) {
        $track = $fileinfo['id3v2']['comments']['track'][0]; 
} else {
    if (isset($fileinfo['id3v1']['track'])) {
        $track = $fileinfo['id3v1']['track'];
    }
}

//GENRE---------------------------
$genre = "";
if (isset($fileinfo['id3v2']['comments']['genre'][0])) {
        $genre = $fileinfo['id3v2']['comments']['genre'][0]; 
} else {
    if (isset($fileinfo['id3v1']['genre'])) {
        $genre = $fileinfo['id3v1']['genre'];
    }
}

//COMMENT---------------------------
$comment = "";
if (isset($fileinfo['id3v2']['comments']['comment'][0])) {
        $comment = $fileinfo['id3v2']['comments']['comment'][0]; 
} else {
    if (isset($fileinfo['id3v1']['comment'])) {
        $comment = $fileinfo['id3v1']['comment'];
    }
}

//NON ID3-INFO---------------------------
if (!isset($fileinfo['audio'])) { $fileinfo['audio'] = ""; }
if (!isset($fileinfo['filesize'])) { $fileinfo['filesize'] = 0; }
if (!isset($fileinfo['playtime_string'])) { $fileinfo['playtime_string'] = "0:00"; }
if (!isset($fileinfo['fileformat'])) { $fileinfo['fileformat'] = ""; }


return array('title'=>$title, 'artist'=>$artist, 'genre'=>$genre, 
    'track'=>$track, 'comment'=>$comment, 'year'=>$year, 'album'=>$album, 
    'audio'=>$fileinfo['audio'], 'size'=>$fileinfo['filesize'], 
    'duration'=>$fileinfo['playtime_string'], 'type'=>$fileinfo['fileformat']);
}


?>