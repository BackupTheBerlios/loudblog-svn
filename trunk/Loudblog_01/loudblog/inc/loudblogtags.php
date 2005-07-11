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
return trim($return);
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
return trim($return);
}

//-------------------------------------
function link_podcast ($content) {
//generates a href-link to the podcast-feed
$return = "<a href=\"podcast.php\" title=\"Link to Podcast\">";
$return .= fullparse (stripcontainer ($content));
$return .= "</a>\n";
return trim($return);
}

//-------------------------------------
function rssfeedhead () {
//returns a full <link>-tag to the podcast-feed (for the html-head)
global $settings;
$return = "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"Podcast-Feed\" href=\"" . $settings['url'] . "/podcast.php\" />";
return trim($return);
}

//-------------------------------------
function link_prev ($content) {
//generates a href-link to the previous page

if (isset($_GET['page'])) { $here = $_GET['page']; }
else { $here = 1; }

//on page 1 we don't show a previous page link!
if (($here > 1) AND (!isset($_GET['id']))) {

    if (isset($_GET['cat'])) { $cat = "cat=".$_GET['cat']."&"; }
    else { $cat = ""; }

    $return = "<a href=\"index.php?".$cat."page=". ($here-1) . "\" title=\"previous page\">";
    $return .= fullparse (stripcontainer ($content));
    $return .= "</a>\n";

} else { $return = ""; }

return trim($return);
}

//-------------------------------------
function link_next ($content) {
//generates a href-link to the next page

if (isset($_GET['page'])) { $here = $_GET['page']; }
else { $here = 1; }

if (!isset($_GET['id'])) {

    if (isset($_GET['cat'])) { $cat = "cat=".$_GET['cat']."&"; }
    else { $cat = ""; }

    $return = "<a href=\"index.php?".$cat."page=". ($here+1) . "\" title=\"previous page\">";
    $return .= fullparse (stripcontainer ($content));
    $return .= "</a>\n";
} else { $return = ""; }
return trim($return);
}

//-------------------------------------
function currentcategory () {
//returns the name of the currently listed category, if possible
if (isset($_GET['cat'])) {
    return htmlentities(urldecode($_GET['cat']), ENT_QUOTES, "UTF-8");
} else { return ""; }
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
    $return = "<a href=\"". $settings['url'] . "/index.php?id=" .$currentid. "\" 
           title=\"Link to posting\">";
    $return .= fullparse (stripcontainer ($content));
    $return .= "</a>\n"; 
} else { $return = fullparse (stripcontainer ($content)); }
return trim($return);
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
$return = "<a href=\"". $settings['url'] . "/audio/" . 
           $postings[$currentid]['audio_file'] . "\" 
           title=\"".$postings[$currentid]['title']."\">";
$return .= fullparse (stripcontainer ($content));
$return .= "</a>\n";
return trim($return);
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
return $postings[$currentid]['audio_length'];
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

//possible attributes and default-values
if (isset($att['width']))  { $width  = $att['width']; }  else { $width  = 200; }
if (isset($att['height'])) { $height = $att['height']; } else { $height = 62; }
if ($postings[$currentid]['filelocal'] == 1) {
    $audio = $settings['url'] . "/audio/".$postings[$currentid]['audio_file']; }
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
}

//-------------------------------------
function author() {
//returns the author of a posting (works within postings-loop)
global $postings;
global $currentid;
return getnickname($postings[$currentid]['author_id']);
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
$tempname2 = htmlentities(urldecode($tempname), ENT_QUOTES, "UTF-8");

if ((isset($att['link'])) AND ($att['link'])) { 
    $return = "<a href=\"index.php?cat=$tempname\" 
           title=\"All postings of category $tempname2\">$tempname2</a>";
} else {
    $return = $tempname2;
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
$att = getattributes($content);
$content = stripcontainer($content);

//possible attributes and default-values
if (isset($att['number'])) { $loops = $att['number']; } else { $loops = 5; }
if (isset($att['order'])) { $order = strtoupper($att['order']); } 
        else { $order = "DESC"; }
if (isset($att['forceloop'])) { $forceloop = $att['forceloop']; } 
        else { $forceloop = false; }
if (isset($att['paging'])) { $paging = $att['paging']; } 
        else { $paging = true; }
        
$return = "";

//offset / splitting into pages
if ((isset($_GET['page'])) AND ($paging == "true")) { 
    $start = $loops*($_GET['page']-1); 
} else { $start = 0; }

//no request from url? show us a loop of postings!
if ((!isset($_GET['id'])) OR ($forceloop)) { 

    //getting data from postings-table
    $dosql  = "SELECT * FROM ".$GLOBALS['prefix']."lb_postings WHERE "; 
    
    //posting must be "live" to be displayed
    $dosql .= "status='3' ";
    
    //posting must not be published in the future
    $dosql .= "AND posted < NOW() ";
    
    //if category is set, filter postings which doesn't fit
    if (isset($_GET['cat'])) {
        $dosql .= "AND (category1_id = ". getcategoryid($_GET['cat']) . " ";
        $dosql .= "OR category2_id = ". getcategoryid($_GET['cat']) . " ";
        $dosql .= "OR category3_id = ". getcategoryid($_GET['cat']) . " ";
        $dosql .= "OR category4_id = ". getcategoryid($_GET['cat']) . ") ";
    }
    
    //order and paging
    $dosql .= "ORDER BY posted ".$order." LIMIT ".$start.", ".$loops.";";
    
    //get data from MySQL          
    $result = mysql_query($dosql) OR die (mysql_error());
    
    $i = 0;
    
    //use all results!
    while ($temp = mysql_fetch_assoc($result)) { 
        $i +=  1;
        $currentid = $temp['id'];
        $postings[$currentid] = $temp;
        $return .= fullparse ($content);
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
return trim($return);
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
//  CATEGORY TAGS
//-------------------------------------------------------------------- 

//-------------------------------------
function link_category($content) {
//creates the link to the currently parsed category (works within category-loop)
global $currentcat;
global $cats;
global $settings;
$return = "<a href=\"index.php?cat=" . 
           $cats[$currentcat]['name'] . "\" 
           title=\"" . $cats[$currentcat]['description']."\">";
$return .= fullparse (stripcontainer($content));
$return .= "</a>\n";
return trim($return);
}

//-------------------------------------
function categoryname() {
//returns the name of a listed category (works within category-loop)
global $cats;
global $currentcat;
return htmlentities(urldecode($cats[$currentcat]['name']), ENT_QUOTES, "UTF-8");
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

//getting some data from postings-table
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
//parse content only if a (certain) category is being shown
$att = getattributes($content);
$return = "";
if (isset($_GET['cat'])) {
    if (!isset($att['category'])) {
        $return = fullparse (stripcontainer($content));
    } 
    else { 
        if ($_GET['cat'] == $att['category']) {
            $return = fullparse ($content);
        }
    }
}             
return trim($return);
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