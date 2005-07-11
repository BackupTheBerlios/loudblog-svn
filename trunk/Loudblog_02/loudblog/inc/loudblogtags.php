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
// http://creativecommons.org/licenses/by-nc-sa/2.0/     //
//                                                       //
// Have Fun! Drop me a line if you like Loudblog!        //
// ----------------------------------------------------- //

//--------------------------------------------------------------------
//  GENERAL SITE TAGS
//-------------------------------------------------------------------- 

//-------------------------------------
function sitename () {
//returns the "name" of the website, taken from settings
global $settings;
return $settings['sitename'];
}

//-------------------------------------
function link_website ($content) {
//generates a href-link to the url of the website, taken from settings
global $settings;
$return = "<a href=\"index.php\" title=\"" . $settings['sitename'] . "\">";
$return .= fullparse (stripcontainer ($content));
$return .= "</a>\n";
return $return;
}

//-------------------------------------
function siteslogan () {
//returns the "slogan" of the website, taken from settings
global $settings;
return $settings['slogan'];
}

//-------------------------------------
function sitedescription () {
//returns the "short description" of the website, taken from settings
global $settings;
return $settings['description'];
}

//-------------------------------------
function link_login ($content) {
//generates a href-link to the admin-login
global $settings;
$return = "<a href=\"loudblog\" title=\"Login to Administration\">";
$return .= fullparse (stripcontainer ($content));
$return .= "</a>\n";
return $return;
}

//-------------------------------------
function link_podcast ($content) {
//generates a href-link to the podcast-feed
$return = "<a href=\"podcast.php\" title=\"Link to Podcast\">";
$return .= fullparse (stripcontainer ($content));
$return .= "</a>\n";
return $return;
}

//-------------------------------------
function rssfeedhead () {
//returns a full <link>-tag to the podcast-feed (for the html-head)
global $settings;
$return = "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"Podcast-Feed\" href=\"" . $settings['url'] . "/podcast.php\" />";
return $return;
}

//-------------------------------------
function link_prev ($content) {
//generates a href-link to the previous page

if (isset($_GET['page'])) { $here = $_GET['page']; }
else { $here = 1; }

//on page 1 we don't show a previous page link!
if (($here > 1) AND (!isset($_GET['id']))) {
    $return = "<a href=\"index.php".addToUrl("page",($here-1))."\" title=\"previous page\">";
    $return .= fullparse (stripcontainer ($content));
    $return .= "</a>\n";
} else { $return = ""; }

return $return;
}

//-------------------------------------
function link_next ($content) {
//generates a href-link to the next page

global $nextpage;

if (isset($_GET['page'])) { $here = $_GET['page']; }
else { $here = 1; }

if ((!isset($_GET['id'])) AND ($nextpage == true)) {
    $return = "<a href=\"index.php".addToUrl("page",($here+1))."\" title=\"previous page\">";
    $return .= fullparse (stripcontainer ($content));
    $return .= "</a>\n";
} else { $return = ""; }
return $return;

}

//-------------------------------------
function currentcategory ($content) {
//returns the name of the currently listed category, if possible
global $cats;
$att = getattributes($content);
$return = "";

//getting category from url
if (isset($_GET['cat'])) {
    //getting some data from categories-table
    $dosql = "SELECT * FROM ".$GLOBALS['prefix']."lb_categories;";
    $result = mysql_query($dosql) OR die (mysql_error());

    while ($temp = mysql_fetch_assoc($result)) { 
        if (killentities($temp['name']) == $_GET['cat']) {
            $return = $temp['name'];
        }
    }
} 

//getting first category from single postings
if (isset($_GET['id'])) { 
    $dosql = "SELECT category1_id FROM ".$GLOBALS['prefix']."lb_postings 
              WHERE id='" . $_GET['id'] . "';";
    $result = mysql_query($dosql) OR die (mysql_error());
    $row = mysql_fetch_assoc($result);

    $dosql = "SELECT name FROM ".$GLOBALS['prefix']."lb_categories 
              WHERE id='" . $row['category1_id'] . "';";
    $result = mysql_query($dosql) OR die (mysql_error());
    $row = mysql_fetch_assoc($result);

    $return = $row['name'];  
}

if (isset($att['short']) AND ($att['short'] == "true")) {
    $return = killentities($return);
}
return $return;
}


//--------------------------------------------------------------------
//  POSTING TAGS
//-------------------------------------------------------------------- 

//-------------------------------------
function link_permalink ($content) {
//generates the permalink for the posting (works within postings-loop)
global $settings;
global $currentid;
if ((!isset($_GET['id'])) OR ($_GET['id'] != $currentid)) {
    $return = "<a href=\"index.php?id=" .$currentid. "\" 
           title=\"Link to posting\">";
    $return .= fullparse (stripcontainer ($content));
    $return .= "</a>\n"; 
} else { $return = fullparse (stripcontainer ($content)); }
return $return;
}

//-------------------------------------
function link_comments ($content) {
//generates the comments invitation link (works within postings-loop)
global $settings;
global $postings;
global $currentid;
if ($postings[$currentid]['comment_on'] == "1") {
    
    if (!isset($_GET['id'])) {
        $return = "<a href=\"index.php?id=" .$currentid. "#comments\" 
                    title=\"Link to comments\">";
        $return .= fullparse (stripcontainer ($content));
        $return .= " (".countcomments($currentid).")</a>\n";
    } else { 
        $return = fullparse (stripcontainer ($content));
        $return .= " (".countcomments($currentid).")\n";
    }
} else { $return = ""; }
return $return;
}

//-------------------------------------
function title() {
//returns the title of a posting (works within postings-loop)
global $postings;
global $currentid;
return $postings[$currentid]['title'];
}

//-------------------------------------
function message() {
//returns the full message of a posting (works within postings-loop)
global $postings;
global $currentid;
return $postings[$currentid]['message_html'];
}

//-------------------------------------
function link_audio($content) {
//generates the link to the audiofile (works within postings-loop)
global $settings;
global $currentid;
global $postings;

if ($postings[$currentid]['audio_file'] != "") {

if ($postings[$currentid]['filelocal'] == 1) {
    $audio = "audio/" . $postings[$currentid]['audio_file']; }
else { $audio = $postings[$currentid]['audio_file']; }
$return = "<a href=\"". $audio . "\" 
           title=\"".$postings[$currentid]['title']."\">";
$return .= fullparse (stripcontainer ($content));
$return .= "</a>\n";
return trim($return);

} else { return ""; }
}

//-------------------------------------
function audiosize() {
//returns the filesize of an audio-file (works within postings-loop)
global $postings;
global $currentid;
return getmegabyte($postings[$currentid]['audio_size']);
}

//-------------------------------------
function audiolength() {
//returns the length of an audio-file (works within postings-loop)
global $postings;
global $currentid;
return getminutes($postings[$currentid]['audio_length']);
}

//-------------------------------------
function audiotype() {
//returns the type of an audio-file (works within postings-loop)
global $postings;
global $currentid;
return getmediatypename($postings[$currentid]['audio_type']);
}

//-------------------------------------
function flashplayer($content) {
//puts emff-flash-app on screen (works within postings-loop)
global $postings;
global $currentid;
global $settings;
$att = getattributes($content);

if ($postings[$currentid]['audio_file'] != "") {

//possible attributes and default-values
if (isset($att['width']))  { $width  = $att['width']; }  else { $width  = 200; }
if (isset($att['height'])) { $height = $att['height']; } else { $height = 62; }
if ($postings[$currentid]['filelocal'] == 1) {
    $audio = "audio/".$postings[$currentid]['audio_file']; }
else { $audio = $postings[$currentid]['audio_file']; }

//build html-code
$return  = "<object type=\"application/x-shockwave-flash\" ";
$return .= "data=\"loudblog/custom/templates/" . $settings['template'];
$return .= "/emff.swf?src=" . $audio . "\" ";
$return .= "width=\"$width\" height=\"$height\">\n";
$return .= "<param name=\"movie\" value=\"loudblog/custom/templates/";
$return .= $settings['template'] . "/emff.swf?src=" . $audio . "\" />\n";
$return .= "</object>\n";

return $return;

} else { return ""; }

}

//-------------------------------------
function commentlimit() {
//returns the maximal size of an audio comment (works within postings-loop)
global $postings;
global $currentid;
if ($postings[$currentid]['comment_size'] > 0) {
    $tech = uploadlimit();
    $user = $postings[$currentid]['comment_size'];
    if ($tech <= $user) { $show = $tech; } else { $show = $user; }
    return getmegabyte($show);
} else {
    return "";
}
}

//-------------------------------------
function author() {
//returns the author of a posting (works within postings-loop)
global $postings;
global $currentid;
return getnickname($postings[$currentid]['author_id']);
}

//-------------------------------------
function authorfullname() {
//returns the full name of the author of a posting (works within postings-loop)
global $postings;
global $currentid;
return getfullname($postings[$currentid]['author_id']);
}

//-------------------------------------
function loop_postingcats($content) {
//returns all defines categories (works within postings-loop)
global $postings;
global $currentid;
global $currentcat;

$content = stripcontainer($content);
$return = "";

for ($i=1; $i < 5; $i++) { 
    $tempcat = "category".$i."_id";
    if ($postings[$currentid][$tempcat] != 0) {
        $currentcat = $postings[$currentid][$tempcat];
        $return .= fullparse ($content);    
    }
}
return trim($return);
}

//-------------------------------------
function postingcat($content) {
//returns a category belonging to a posting (works within postings-loop)
global $currentcat;

$att = getattributes($content);
$tempname = getcategory($currentcat);
$templinkname = killentities($tempname);

if ((isset($att['link'])) AND ($att['link'] == "true")) { 
    $return = "<a href=\"index.php?cat=$templinkname\" 
           title=\"All postings of category $tempname\">$tempname</a>";
} else {
    $return = $tempname;
}

return $return;
}




//-------------------------------------
function posted($content) {
//returns the publishing-date/time of a posting (works within postings-loop)
global $postings;
global $currentid;
global $settings;
$att = getattributes($content);
if (isset($att['format'])) { $format = $att['format']; } 
else { $format = $settings['dateformat']; }

return date($format, strtotime($postings[$currentid]['posted']));
}

//-------------------------------------
function loop_postings ($content) {
//returns a certain number of postings
global $currentid;
global $postings;
global $nextpage;
$att = getattributes($content);
$content = stripcontainer($content);

//possible attributes and default-values
if (isset($att['number'])) { $loops = $att['number']; } else { $loops = 5; }
if (isset($att['order'])) { $order = strtoupper($att['order']); } 
        else { $order = "DESC"; }
if (isset($att['forceloop'])) { $forceloop = $att['forceloop']; } 
        else { $forceloop = "false"; }
if (isset($att['paging'])) { $paging = $att['paging']; } 
        else { $paging = "true"; }
        
$return = "";

//offset / splitting into pages
if ((isset($_GET['page'])) AND ($paging == "true")) { 
    $start = $loops*($_GET['page']-1); 
} else { $start = 0; }

//no request from url? show us a loop of postings!
if ((!isset($_GET['id'])) OR ($forceloop == "true")) { 

    //getting data from postings-table
    $dosql  = "SELECT * FROM ".$GLOBALS['prefix']."lb_postings WHERE "; 
    
    //posting must be "live" to be displayed
    $dosql .= "status='3' ";
    
    //posting must not be published in the future
    $dosql .= "AND posted < NOW() ";
    
    //if category is set, filter postings which doesn't fit
    if (isset($_GET['cat'])) {
    
        //which category-id do we request via url?
        $tempcatid = getcategoryidshort($_GET['cat']);    
        $dosql .= "AND (category1_id = ". $tempcatid . " ";
        $dosql .= "OR category2_id = ". $tempcatid . " ";
        $dosql .= "OR category3_id = ". $tempcatid . " ";
        $dosql .= "OR category4_id = ". $tempcatid . ") ";
    }
    
    //order and paging
    //we try to fetch one more posting than requested in order to check the availability of a "next to"-button.
    $dosql .= "ORDER BY posted ".$order." LIMIT ".$start.", ".($loops+1).";";
    
    //get data from MySQL          
    $result = mysql_query($dosql) OR die (mysql_error());
    
    $i = 0;
    
    //use all results!
    while ($temp = mysql_fetch_assoc($result)) { 
        $i +=  1;
        if ($i <= $loops) {
            $currentid = $temp['id'];
            $postings[$currentid] = $temp;
            $return .= fullparse ($content);
        //if there is one more posting than requested, we can show a "next page"-button.
        } else { 
            if ($paging == "true") { $nextpage = true; }
        }
    }

    
} else {   //ah, we want to show a single posting with a given id? no problem!

    //getting data from postings-table
    $dosql = "SELECT * FROM ".$GLOBALS['prefix']."lb_postings 
              WHERE id='" . $_GET['id'] . "' 
              AND posted < NOW() AND status='3';";
    $result = mysql_query($dosql) OR die (mysql_error());
    $temp = mysql_fetch_assoc($result);

    $currentid = $temp['id'];
    $postings[$currentid] = $temp;
    $return .= fullparse ($content);
}
return trim($return);
}

//--------------------------------------------------------------------
//  HYPERLINK TAGS
//-------------------------------------------------------------------- 

//-------------------------------------
function link_hyperlink($content) {
//generates the url for an given hyperlink (works within hyperlinks-loop)
global $links;
global $currentlink;
$return = "<a href=\"". $links[$currentlink]['url'] . "\" title=\"" . 
      $links[$currentlink]['description'] . "\">";
$return .= fullparse (stripcontainer ($content));
$return .= "</a>\n";
return $return;
}

//-------------------------------------
function hyperlinkname() {
//returns the "name" of the a hyperlink (works within hyperlinks-loop)
global $links;
global $currentlink;
return $links[$currentlink]['title'];
}

//-------------------------------------
function hyperlinkdescription() {
//returns the "description" of the a hyperlink (works within hyperlinks-loop)
global $links;
global $currentlink;
return $links[$currentlink]['description'];
}

//-------------------------------------
function loop_hyperlinks($content) {
//returns all defined hyperlinks of a posting (works within postings-loop)
global $currentid;
global $postings;
global $links;
global $currentlink;

$att = getattributes($content);
$content = stripcontainer($content);

$return = "";

//getting some data from postings-table
$dosql = "SELECT * FROM ".$GLOBALS['prefix']."lb_links 
          WHERE posting_id='".$currentid."' ORDER BY linkorder ASC;";
$result = mysql_query($dosql) OR die (mysql_error());

while ($temp = mysql_fetch_assoc($result)) { 
    $currentlink = $temp['id'];
    $links[$currentlink] = $temp;
    $return .= fullparse ($content);
}
return trim($return);
}

//--------------------------------------------------------------------
//  COMMENTING TAGS
//-------------------------------------------------------------------- 

//-------------------------------------
function area_comments ($content) {
//only parse this area if comment stuff is to be shown
global $settings;
global $currentid;
global $postings;
global $tempfilename;

$tempfilename = "";
$return = "";
$freshfile = false;

//before we show stuff, we have to handle data from post or save things in database and so on...


//check if there is a new uploadad file and make a shorter meta data variable
if ((isset($_FILES['commentfile'])) 
    AND ($_FILES['commentfile']['error'] == "0")) {
    $freshfile = $_FILES['commentfile'];
}

//We are only previewing?
if (isset($_POST['commentpreview'])) {

    //add http:// to previewed urls
    if (substr($_POST['commentweb'],0,4) != "http") {
        $_POST['commentweb'] = "http://".$_POST['commentweb'];
    }

    //a new posted file has the highest priority
    if (($freshfile != false)
        AND (checksuffix($freshfile['name']))
        AND ($freshfile['size'] <= $postings[$currentid]['comment_size'])) {
  
        $tempfilename = freshaudioname(strrchr($freshfile['name'], "."), "temp"); 
        //put the uploaded file into the desired directory
        move_uploaded_file($freshfile['tmp_name'], 
                               $GLOBALS['audiopath'].$tempfilename) 
        OR die ("<p>Error!</p>");
    } else {
        //put previously uploaded file through to another preview
        if (isset($_POST['filethrough'])) {
            $tempfilename = $_POST['filethrough'];
        }
    }
}

//oh, we are submitting? It's getting serious!
if (isset($_POST['commentsubmit'])) { 

    //in dubio contra audio
    $audioexists = false;

    //do a lot of things, if we have got a new uploaded file
    if (($freshfile != false)
            AND (checksuffix($freshfile['file']))) {
        $filename = freshaudioname (strrchr($freshfile['name'], "."), "comment"); 
    
        //put the uploaded file into the desired directory
        move_uploaded_file($freshfile['tmp_name'], $GLOBALS['audiopath'].$filename) 
            OR die ("<p>Error!</p>");
        $audioexists = true;

    //but we can take the previewed audio file, too...
    } else {
        if (isset($_POST['filethrough'])) {
            //rename audio file and get audio meta data
            $tempfilename = $_POST['filethrough'];
            $filename = freshaudioname (strrchr($tempfilename, "."), "comment"); 
            rename($GLOBALS['audiopath'].$tempfilename, $GLOBALS['audiopath'].$filename) 
                OR die ("<p>Error!</p>");
            $audioexists = true;
        }
    }

    //there is an audio file?
    if ($audioexists) {
        //get metadata from getid3-class
        $id3 = getid3data($GLOBALS['audiopath'].$filename,"front");
    } else {
        //make empty values for audio data (cause we dont have audio data)
        $filename = "";
        $id3['duration'] = "0:00";
        $id3['size'] = 0; 
    }

    //prepare non-audio data
    if ($_POST['commentname'] == "") { $name = "Anonymous"; } 
        else { $name = htmlentities(strip_tags($_POST['commentname']), ENT_QUOTES, "UTF-8"); }    
    $mail = strip_tags($_POST['commentmail']);
    $web = strip_tags($_POST['commentweb']);    
    $ip = $_SERVER['REMOTE_ADDR'];
    $message_input = htmlentities($_POST['commentmessage'], ENT_QUOTES, "UTF-8");
    $message_html = makehtml(strip_tags($_POST['commentmessage']));

    //write data into database (doesn't matter, with or without audio)
    $dosql = "INSERT INTO {$GLOBALS['prefix']}lb_comments
             (posting_id, posted, name, mail, web, ip, message_input, message_html,
            audio_file, audio_type, audio_length, audio_size)
            VALUES
            (
            '".$currentid."',
            '".date('Y-m-d H:i:s')."',
            '".$name."', '".$mail."', '".$web."', '".$ip."', 
            '".$message_input."', '".$message_html."',
            '".$filename."',
            '".type_suffix($filename)."',
            '".getseconds($id3['duration'])."',
            '".$id3['size']."'
            );";
    $result = mysql_query($dosql) OR die (mysql_error());
}
//submitting actions are finished. thank you for your attention.

//do we show comments at all?
if ((isset($_GET['id'])) AND ($postings[$currentid]['comment_on'] == 1)) {
    $return .= "<div id=\"comments\">\n";
    $return .= fullparse (stripcontainer($content));
    $return .= "\n</div>";
} else { $return = ""; }

return $return;
}

//-------------------------------------
function loop_comments ($content) {
//show a loop of all comments of a certain posting
global $currentid;
global $currentcomment;
global $comments;
global $tempfilename;
global $allcomm;

$att = getattributes($content);
if (isset($att['global'])) { $allcomm = $att['global']; } 
else { $allcomm = "false"; }
if (isset($att['number'])) { $number = $att['number']; } 
else { $number = 5; }


$content = trim(stripcontainer($content));
$return = "";

//do we get the comments of the current posting?
if ($allcomm == "false") {

//getting some data from comments-table
$dosql = "SELECT * FROM ".$GLOBALS['prefix']."lb_comments 
          WHERE posting_id='".$currentid."' ORDER BY posted ASC;";
$result = mysql_query($dosql) OR die (mysql_error());
$i = 0;
$comments = "";
while ($temp = mysql_fetch_assoc($result)) { 
    $i += 1;
    $comments[$i] = $temp;
}

//only here for previewing?
if (isset($_POST['commentpreview'])) {

    if ($tempfilename != "") {
        $id3 = getid3data($GLOBALS['audiopath'].$tempfilename,"front");
        $tempfilesize = $id3['size']; 
        $tempfilelength = getseconds($id3['duration']);
    } else { 
        $tempfilesize = "0"; 
        $tempfilelength = "0"; 
    }

    $i += 1;
    $comments[$i]['id'] = 0;
    $comments[$i]['posting_id'] = $currentid;
    $comments[$i]['posted'] = date('Y-m-d H:i:s');
    if ($_POST['commentname'] == "") { $comments[$i]['name'] = "Anonymus"; } 
    else { $comments[$i]['name'] = 
        htmlentities($_POST['commentname'], ENT_QUOTES, "UTF-8"); }
    $comments[$i]['name'] = $comments[$i]['name'];
    $comments[$i]['mail'] = strip_tags($_POST['commentmail']);
    $comments[$i]['web'] = strip_tags($_POST['commentweb']);
    $comments[$i]['ip'] = $_SERVER['REMOTE_ADDR'];
    $comments[$i]['message_input'] = htmlentities(strip_tags($_POST['commentmessage']), ENT_QUOTES, "UTF-8");
    $comments[$i]['message_html'] = "<p>[PREVIEW]</p> ".makehtml(strip_tags($_POST['commentmessage']));
    $comments[$i]['audio_file'] = $tempfilename;
    $comments[$i]['audio_size'] = $tempfilesize;
    $comments[$i]['audio_length'] = $tempfilelength;
    $comments[$i]['audio_type'] = type_suffix($tempfilename);
}


//okay, we show a list af ALL recent comments
} else {

$dosql = "SELECT * FROM ".$GLOBALS['prefix']."lb_comments 
          ORDER BY posted DESC LIMIT 0,".$number.";";
$result = mysql_query($dosql) OR die (mysql_error());
$i = 0;
$comments = "";
while ($temp = mysql_fetch_assoc($result)) { 
    $i += 1;
    $comments[$i] = $temp; 
}
}

//is there one or more comments?
if ($i > 0) {
    $i = 1;
    
    //show every comment, one by one
    foreach ($comments as $thiscomment) {
        $currentcomment = $i;
        if ($allcomm == "false") {
            $return .= "<span id=\"com".$comments[$i]['id']."\"></span>";
        }
        $return .= fullparse($content);
        $i += 1;
    }
}

return $return;
}

//-------------------------------------
function commentname () {
//returns the commentator's name on the comments list
global $currentcomment;
global $comments;
global $allcomm;

$link = "";
$name = trim(stripslashes($comments[$currentcomment]['name']));
$mail = trim(stripslashes($comments[$currentcomment]['mail']));
$web  = trim(stripslashes($comments[$currentcomment]['web']));

if ($allcomm == "false") {
if ($mail != "") { $link = "mailto:".$mail; }
if (($web != "") AND ($web != "http://")) { $link = $web; }
if ($link != "") {
    $return = "<a href=\"".$link."\">".$name."</a>";
} else $return = $name;
} else $return = $name;

return $return;
}

//-------------------------------------
function link_permacomment ($content) {
//generates the permalink for the current comment
global $settings;
global $comments;
global $currentcomment;

$return =  "<a href=\"index.php?id=" .$comments[$currentcomment]['posting_id'];
$return .= "#com".$comments[$currentcomment]['id']. "\" 
           title=\"Permanent link to this comment\">";
$return .= fullparse (stripcontainer ($content));
$return .= "</a>\n"; 
return $return;
}

//-------------------------------------
function commentparent () {
//returns the title of the related posting
global $currentcomment;
global $comments;
global $settings;

$dosql = "SELECT title FROM ".$GLOBALS['prefix']."lb_postings 
          WHERE id = '".$comments[$currentcomment]['posting_id']."';";
$result = mysql_query($dosql) OR die (mysql_error());
$row = mysql_fetch_assoc($result);

return $row['title'];
}

//-------------------------------------
function commentposted ($content) {
//returns the date on the comments list
global $currentcomment;
global $comments;
global $settings;
$att = getattributes($content);
if (isset($att['format'])) { $format = $att['format']; } 
else { $format = $settings['dateformat']; }

return date($format, @strtotime($comments[$currentcomment]['posted']));
}

//-------------------------------------
function commentmessage ($content) {
//returns the message on the comments list
global $currentcomment;
global $comments;
$att = getattributes($content);

if (isset($att['length'])) {
    return substr(strip_tags($comments[$currentcomment]['message_html']),0,$att['length'])."...";
} else { return $comments[$currentcomment]['message_html']; }
}

//-------------------------------------
function link_commentfile ($content) {
//shows a link to the file on the comments list
global $currentcomment;
global $comments;

if ($comments[$currentcomment]['audio_file'] != "") {
    $return  = "<a href=\"audio/".$comments[$currentcomment]['audio_file'];
    $return .= "\" title=\"Link to audio file\">";
    $return .= fullparse (stripcontainer ($content));
    $return .= "</a>\n";
} else { $return = ""; }
return $return;
}

//-------------------------------------
function commentsize () {
//returns the audio size on the comments list
global $currentcomment;
global $comments;
return getmegabyte($comments[$currentcomment]['audio_size']);
}

//-------------------------------------
function commentlength () {
//returns the audio length on the comments list
global $currentcomment;
global $comments;
return getminutes($comments[$currentcomment]['audio_length']);
}

//-------------------------------------
function commentflashplayer($content) {
//puts emff-flash-app on screen
global $currentcomment;
global $comments;
global $settings;
$att = getattributes($content);

if ($comments[$currentcomment]['audio_type'] == 1) {

//possible attributes and default-values
if (isset($att['width']))  { $width  = $att['width']; }  else { $width  = 200; }
if (isset($att['height'])) { $height = $att['height']; } else { $height = 62; }
$audio = "audio/" . $comments[$currentcomment]['audio_file']; 

//build html-code
$return  = "<object type=\"application/x-shockwave-flash\" ";
$return .= "data=\"loudblog/custom/templates/" . $settings['template'];
$return .= "/emff_comments.swf?src=" . $audio . "\" ";
$return .= "width=\"$width\" height=\"$height\">\n";
$return .= "<param name=\"movie\" value=\"loudblog/custom/templates/";
$return .= $settings['template'] . "/emff_comments.swf?src=" . $audio . "\" />\n";
$return .= "</object>\n";

return $return;

} else { return ""; }
}


//-------------------------------------
function area_makecomment ($content) {
//returns the form-tag with some hidden fields
global $settings;
global $currentid;
global $postings;
global $tempfilename;

if (!isset($_POST['commentsubmit'])) {
    $return = "<form method=\"post\" 
               action=\"index.php?id=".$currentid."#comments\" 
               enctype=\"multipart/form-data\">\n";
    $return .= fullparse (stripcontainer($content));
    
    //if a temporary audio comment file has been uploaded: send path!
    if ((isset($_POST['filethrough'])) OR 
            ((isset($_POST['commentpreview'])) AND 
            (isset($_FILES['commentfile'])) AND
            ($_FILES['commentfile']['error'] == "0"))) {
        $return .= "<input type=\"hidden\" ";
        $return .= "name=\"filethrough\" value=\"".$tempfilename."\" />\n\n";
    }
    $return .= "</form>";
} else { $return = ""; }

return $return;
}

//-------------------------------------
function inputname () {
//returns the input field for commentor's name
if (isset($_POST['commentname'])) { 
    $value = trim(stripslashes($_POST['commentname']));
} else { $value = ""; }
return "<input type=\"text\" name=\"commentname\" class=\"commentname\" value=\"".$value."\" />";
}

//-------------------------------------
function inputmail () {
//returns the input field for commentor's mail address
if (isset($_POST['commentmail'])) { 
    $value = trim(stripslashes($_POST['commentmail'])); 
} else { $value = ""; }
return "<input type=\"text\" name=\"commentmail\" class=\"commentmail\" value=\"".$value."\" />";
}

//-------------------------------------
function inputweb () {
//returns the input field for commentor's website
if (isset($_POST['commentweb'])) { 
    $value = trim(stripslashes($_POST['commentweb'])); 
} else { $value = ""; }
return "<input type=\"text\" name=\"commentweb\" class=\"commentweb\" value=\"".$value."\" />";
}

//-------------------------------------
function inputmessage () {
//returns the textarea for commentor's message
if (isset($_POST['commentmessage'])) { 
    $value = trim(stripslashes($_POST['commentmessage'])); 
} else { $value = ""; }
return "<textarea name=\"commentmessage\" class=\"commentmessage\">".$value."</textarea>";
}

//-------------------------------------
function inputfile ($content) {
//returns the input field for commentor's audio file
global $currentid;
global $postings;
if ($postings[$currentid]['comment_size'] > 0) {
    return "<input type=\"file\" name=\"commentfile\" accept=\"audio/*\" class=\"commentfile\" />";
} else {
    return "<p>No audio file allowed</p>";
}
}

//-------------------------------------
function buttonpreview () {
//returns the preview button
return "<input type=\"submit\" name=\"commentpreview\" class=\"commentpreview\" value=\"preview\" />";
}

//-------------------------------------
function buttonsend () {
//returns the send button, if all requirements are matched
if (
   (  (isset($_POST['commentmessage'])) AND (trim($_POST['commentmessage']) != "")   )
   OR  
   (  (isset($_FILES['commentfile'])) AND ($_FILES['commentfile']['error'] == "0")  )
   OR
   (  (isset($_POST['filethrough'])) AND ($_POST['filethrough'] != "")   )
   ) { 
    return "<input type=\"submit\" name=\"commentsubmit\" 
            class=\"commentsubmit\" value=\"send comment\" />";
} else { return ""; }
}




//--------------------------------------------------------------------
//  CATEGORY TAGS
//-------------------------------------------------------------------- 

//-------------------------------------
function link_category($content) {
//creates the link to the currently parsed category (works within category-loop)
global $currentcat;
global $cats;
global $settings;
$return = "<a href=\"index.php?cat=" . 
           killentities($cats[$currentcat]['name']) . "\" 
           title=\"" . $cats[$currentcat]['description']."\">";
$return .= fullparse (stripcontainer($content));
$return .= "</a>\n";
return $return;
}

//-------------------------------------
function categoryname() {
//returns the name of a listed category (works within category-loop)
global $cats;
global $currentcat;
return $cats[$currentcat]['name'];
}

//-------------------------------------
function categorydescription() {
//returns the description of a listed category (works within category-loop)
global $cats;
global $currentcat;
return $cats[$currentcat]['description'];
}

//-------------------------------------
function loop_categories($content) {
//returns a loop-routine for all existing categories
global $currentcat;
global $cats;

$att = getattributes($content);
$content = stripcontainer($content);

//possible attributes and default-values
if (isset($att['sortby'])) { $sortby = $att['sortby']; } 
        else { $sortby = "name"; }
if (isset($att['order'])) { $order = strtoupper($att['order']); } 
        else { $order = "ASC"; }

$return = "";

//getting some data from categories-table
$dosql = "SELECT * FROM ".$GLOBALS['prefix']."lb_categories 
          ORDER BY ".$sortby." ".$order.";";
$result = mysql_query($dosql) OR die (mysql_error());

while ($temp = mysql_fetch_assoc($result)) { 

    $currentcat = $temp['id'];
    $cats[$currentcat] = $temp;
    $return .= fullparse ($content);
}
return trim($return);
}

//--------------------------------------------------------------------
//  CONDITIONAL TAGS (THOSE WITH "IF")
//-------------------------------------------------------------------- 

//-------------------------------------
function if_single ($content) {
//parse content only if a single posting is being shown
if (isset($_GET['id'])) {
    return fullparse (stripcontainer($content));
} else { return ""; }
}

//-------------------------------------
function if_list ($content) {
//parse content only if a list of posting is being shown
if (!isset($_GET['id'])) {
    return fullparse (stripcontainer($content));
} else { return ""; }
}

//-------------------------------------
function if_category ($content) {
//parse content only if a (certain) category or no category is being shown

$att = getattributes($content);
$return = "";
$nocat = true;

//checking the url for a category list request
if (isset($_GET['cat'])) {
    if (!isset($att['category'])) {
        $return = fullparse (stripcontainer($content));
    } else {
        $att['category'] = htmlentities($att['category'], ENT_QUOTES, "UTF-8");
        if (getcategoryidshort($_GET['cat']) == getcategoryid($att['category'])) {
            $return = fullparse (stripcontainer($content));
        }
    }
    $nocat = false;
} 

//checking url for single posting  
if (isset($_GET['id'])) {
    //checking if category is available
    $dosql = "SELECT category1_id, category2_id, category3_id, category4_id 
              FROM ".$GLOBALS['prefix']."lb_postings 
              WHERE id='" . $_GET['id'] . "';";
    $result = mysql_query($dosql) OR die (mysql_error());
    $row = mysql_fetch_assoc($result);
    $show = false;
    foreach ($row as $c => $id) {
        if (!isset($att['category'])) {
            if ($id != 0) {
                $return = fullparse (stripcontainer($content));
                $nocat = false;
                break;
            }
        } else { 
            $att['category'] = htmlentities($att['category'], ENT_QUOTES, "UTF-8");
            if ($id == getcategoryid($att['category'])) {
                $return = fullparse (stripcontainer($content));
                $nocat = false;
                break;
            }
        }
    }  
}

//the "no category" option at the end
if (($nocat) AND (isset($att['category'])) AND ($att['category'] == "false")) {
    $return = fullparse (stripcontainer($content));
}

return trim($return);
}

//-------------------------------------
function if_audio ($content) {
//parse content only if audio file is attached
global $postings;
global $currentid;

if ($postings[$currentid]['audio_file'] != "") {
    return trim(fullparse (stripcontainer($content)));
} else { return ""; }
}

//-------------------------------------
function if_commentaudio ($content) {
//parse content only if audio file is attached to a comment
global $currentcomment;
global $comments;

if ($comments[$currentcomment]['audio_file'] != "") {
    return trim(fullparse (stripcontainer($content)));
} else { return ""; }
}

//-------------------------------------
function if_comments ($content) {
//parse content only if comments are availabe for this posting
global $postings;
global $currentid;

if ($postings[$currentid]['comment_on'] == "1") {
    return trim(fullparse (stripcontainer($content)));
} else { return ""; }
}

//-------------------------------------
function if_mp3 ($content) {
//parse content only if attached file is a real mp3
global $postings;
global $currentid;

if ($postings[$currentid]['audio_type'] == 1) {
    return trim(fullparse (stripcontainer($content)));
} else { return ""; }
}

?>