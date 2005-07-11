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

//get database connection values
include "loudblog/custom/config.php";

//create some important globals
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
include "loudblog/inc/functions.php";
$settings = getsettings ();


//getting data from postings-table
$dosql  = "SELECT * FROM {$GLOBALS['prefix']}lb_postings WHERE "; 
    
//posting must be "live" to be displayed
$dosql .= "status='3' ";
    
//posting must not be published in the future
$dosql .= "AND posted < NOW() ";
    
//if category is set, filter postings which doesn't fit
if (isset($_GET['cat'])) {
    $getcat = getcategoryid(urlencode($_GET['cat']));
    $dosql .= "AND (category1_id = $getcat  ";
    $dosql .= "OR category2_id = $getcat ";
    $dosql .= "OR category3_id = $getcat ";
    $dosql .= "OR category4_id = $getcat) ";
}
    
//limiting number
$dosql .= "ORDER BY posted DESC LIMIT 0, {$settings['rss_postings']};";

$result = mysql_query($dosql) OR die (mysql_error());

$i = 0;
while ($rows[$i] = mysql_fetch_assoc($result)) { $i += 1; }
$lasttime = strtotime($rows[0]['posted']);
unset($rows[$i]);


//Ready to rock'n'roll? Let's start building the feed!
header("Content-Type: application/rss+xml"); 
echo "<?xml version=\"1.0\"?>\n";
echo "<rss version=\"2.0\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\">\n\n";

echo "<channel>\n\n";
echo "<title>{$settings['sitename']}</title>\n";
echo "<link>{$settings['url']}</link>\n";
echo "<description>{$settings['description']}</description>\n";
echo "<language>en-us</language>\n";
echo "<pubDate>" . date("r") . "</pubDate>\n";
echo "<lastBuildDate>" . date("r", $lasttime) . "</lastBuildDate>\n";
echo "<generator>Loudblog</generator>\n\n";


//start the loop for postings listing
foreach ($rows as $fields) { 

if ($fields['filelocal'] == 0) { $tempurl = $fields['audio_file']; }
else { $tempurl = $settings['url'] . "/audio/" . $fields['audio_file']; }

//start building the item
echo "<item>\n";

//title
echo "    <title>{$fields['title']}</title>\n";

//link
echo "    <link>{$settings['url']}/index.php?id={$fields['id']}</link>\n";

//categories
$tempcats = "";     
if ($fields['category1_id'] != "0") { 
    $tempcats .= urldecode(getcategory($fields['category1_id'])) . ", "; }
if ($fields['category2_id'] != "0") { 
    $tempcats .= urldecode(getcategory($fields['category2_id'])) . ", "; }
if ($fields['category3_id'] != "0") { 
    $tempcats .= urldecode(getcategory($fields['category3_id'])) . ", "; }
if ($fields['category4_id'] != "0") { 
    $tempcats .= urldecode(getcategory($fields['category4_id'])) . ", "; }

//trim the string
$tempcats = trim(htmlentities(substr($tempcats, 0, strrpos($tempcats,",")),ENT_QUOTES,"UTF-8"));

if ($tempcats != "") {
    echo "    <dc:subject>$tempcats</dc:subject>\n";
}
    
//author
echo "    <dc:creator>\n".getfullname($fields['author_id'])."\n</dc:creator>\n";

//description
echo "    <description>\n";
echo html_entity_decode(trim(strip_tags($fields['message_html'])),ENT_QUOTES); 
echo "\n    </description>\n";

//bodytext
echo "    <content:encoded>\n" . 
          trim(htmlspecialchars($fields['message_html'],ENT_QUOTES));
          
//hyperlinks

    //getting data from links-table
    $dosql  = "SELECT * FROM {$GLOBALS['prefix']}lb_links "; 
    $dosql .= "WHERE posting_id='{$fields['id']}' ";
    $dosql .= "ORDER BY linkorder ASC;";
    $result = mysql_query($dosql) OR die (mysql_error());
    $j = 0;
    while ($linkrows[$j] = mysql_fetch_assoc($result)) { $j += 1; }
    unset($linkrows[$j]);
    $tmp = "";
    
    if ($j > 0) {
        $tmp = "\n\n    <ul>\n\n";
        //start loop for showing links
        foreach ($linkrows as $link) {
            $tmp .= "    <li>\n<a href=\"{$link['url']}\" ";
            $tmp .= "title=\"{$link['description']}\">";
            $tmp .= "{$link['title']}</a>&nbsp;&ndash; ";
            $tmp .= "{$link['description']}</a>\n    </li>\n\n";
        }
        $tmp .= "    </ul>\n\n";
    }

echo trim(htmlspecialchars($tmp, ENT_QUOTES));
echo "    </content:encoded>\n";
    
//date of publication
echo "    <pubDate>".date("r", strtotime($fields['posted']))."</pubDate>\n\n";


//do we add an enclosure?
if ($fields['audio_file'] != "") {
    echo "    <enclosure url=\"$tempurl\" ";
    echo "length=\"{$fields['audio_size']}\" ";
    echo "type=\"".mime_type($fields['audio_type'])."\" />\n";
}

echo "</item>\n\n";


}


echo "\n\n</channel>\n\n</rss>";



?>