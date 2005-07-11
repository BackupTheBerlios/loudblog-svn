<?php header("Content-Type: text/xml; charset=iso-8859-1");
echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";

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

//get data from database-tables and put it into arrays
dumpdata();

//getting data from postings-table
$dosql  = "SELECT * FROM {$GLOBALS['prefix']}lb_postings WHERE "; 

//do we have only a single posting to show?
if (isset($_GET['id'])) 
{
    $dosql .= "id='".$_GET['id']."' AND ";
}
    
//posting must be "live" to be displayed
$dosql .= "status='3' ";
    
//posting must not be published in the future
$dosql .= "AND posted < NOW() ";
    
//if category is set, filter postings which doesn't fit
if (isset($_GET['cat'])) 
{    
    //which category-id do we request via url?
    $tempcatid = getcategoryidshort($_GET['cat']);    
    if ($tempcatid!="") {
        $dosql .= "AND (category1_id = ". $tempcatid . " ";
        $dosql .= "OR category2_id = ". $tempcatid . " ";
        $dosql .= "OR category3_id = ". $tempcatid . " ";
        $dosql .= "OR category4_id = ". $tempcatid . ") ";
    }
}
    
//limiting number
$dosql .= "ORDER BY posted DESC LIMIT 0, {$settings['rss_postings']};";

$result = mysql_query($dosql) OR die (mysql_error());

$i = 0;
while ($rows[$i] = mysql_fetch_assoc($result)) { $i += 1; }
$lasttime = strtotime($rows[0]['posted']);
unset($rows[$i]);


//build category string
if (isset($_GET['cat'])) {	$catname = ": ".getcategory(getcategoryidshort($_GET['cat'])); } 
else { $catname = ""; }

//build single posting string
if (isset($_GET['id'])) { $postname = ": ".$rows[0]['title']; }
else { $urlpost = ""; $postname = ""; }

//Ready to rock'n'roll? Let's start building the feed!
echo "<rss version=\"2.0\" xmlns:itunes=\"http://www.itunes.com/DTDs/Podcast-1.0.dtd\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\">\n\n";

echo "<channel>\n\n";

//building the right title for our little feed
echo "<title>".chars($settings['sitename']);
echo $catname.$postname."</title>\n";

//author of the feed
echo "<itunes:author>".chars($settings['itunes_author'])."</itunes:author>\n";

//build current url
$atts = "";
if (isset($_GET['id'])) { $atts = "?id=".$_GET['id']; }
if (isset($_GET['cat'])) { $atts = "?cat=".$_GET['cat']; }
echo "<link>".$settings['url'].$atts."</link>\n";

//more information about the podcast
$desc = chars($settings['description']);
$slogan = chars($settings['slogan']);

echo "<itunes:subtitle>".$slogan."</itunes:subtitle>\n";
echo "<itunes:summary>".$desc."</itunes:summary>\n";
echo "<description>".$desc."</description>\n";
echo "<language>".$settings['languagecode']."</language>\n";

echo "<copyright>".$settings['copyright']."</copyright>\n";
echo "<itunes:owner>\n";
echo "   <itunes:name>".chars($settings['itunes_author'])."</itunes:name>\n";
echo "   <itunes:email>".$settings['itunes_email']."</itunes:email>\n";
echo "</itunes:owner>\n";

//regular RSS-image
echo "<image>\n";
echo "   <url>".$settings['url']."/audio/rssimage.jpg</url>\n";
echo "   <title>".chars($settings['sitename'])."</title>\n";
echo "   <link>".$settings['url']."</link>\n";
echo "</image>\n";

//huge iTunes-image
echo "<itunes:link rel=\"image\" type=\"video/jpeg\" href=\"".$settings['url']."/audio/itunescover.jpg\">".str_replace("&", "&amp;", $settings['sitename'])."</itunes:link>\n\n";

echo "<pubDate>" . date("r") . "</pubDate>\n";
echo "<lastBuildDate>" . date("r", $lasttime) . "</lastBuildDate>\n";
echo "<generator>Loudblog</generator>\n\n";

//explicit or not?
if ($settings['itunes_explicit'] == "1") { $setexpl = "yes"; }
if ($settings['itunes_explicit'] == "0") { $setexpl = "no"; }

echo "<itunes:explicit>".$setexpl."</itunes:explicit>\n\n";


//iTunes categories!
$cat_table = itunescats();
$allcats = array($settings['feedcat1'],$settings['feedcat2'],$settings['feedcat3'],$settings['feedcat4']);

foreach ($allcats as $thiscat) {
    $main = false;
    if ($thiscat != "00-00") {
        if (substr($thiscat,3) != "00") {
            $maincat = substr($thiscat,0,2) . "-00";
            echo "<itunes:category text=\"".$cat_table[$maincat]."\">\n";
            $main = true;
        } 
        echo "<itunes:category text=\"".$cat_table[$thiscat]."\" />\n";
        if ($main) { echo "</itunes:category>\n"; }
    }
}


//start the loop for postings listing
foreach ($rows as $fields) 
{ 
    if ($fields['filelocal'] == 0) { $tempurl = $fields['audio_file']; }
    else { $tempurl = $settings['url'] . "/audio/" . $fields['audio_file']; }

    if ((!isset($_GET['post'])) OR ($_GET['post'] == "1"))
    { 

        //start building the item
        echo "<item>\n";

        //globally unique identifier
        echo "    <guid>".$settings['url']."/index.php?id=".$fields['id']."</guid>\n";

        //title
        echo "    <title>".chars($fields['title'])."</title>\n";

        //link
        echo "    <link>".$settings['url']."/index.php?id=".$fields['id']."</link>\n";

        //comments
        if ($fields['comment_on'] == "1") 
        {
            echo "    <comments>".$settings['url']."/index.php?id=".$fields['id']."#comments</comments>\n";
        }

        //show categories
        showcats ($fields);    

        //author
        $postauthor = chars(getfullname($fields['author_id']));
        echo "    <dc:creator>".$postauthor."</dc:creator>\n";
        echo "    <itunes:author>".$postauthor."</itunes:author>\n";

        //description and summary
        $desc = chars($fields['message_html']);
        echo "    <description>\n".$desc."\n    </description>\n";
        echo "    <itunes:summary>\n".$desc."\n    </itunes:summary>\n";

        //bodytext
        echo "    <content:encoded>\n" . chars($fields['message_html']);
          
        //hyperlinks

        //resetting variable
        $linkrows = "";

        //getting data from links-table
        $dosql  = "SELECT * FROM {$GLOBALS['prefix']}lb_links "; 
        $dosql .= "WHERE posting_id='{$fields['id']}' ";
        $dosql .= "ORDER BY linkorder ASC;";
        $result = mysql_query($dosql) OR die (mysql_error());
        $j = 0;
        while ($linkrows[$j] = mysql_fetch_assoc($result)) { $j += 1; }
        unset($linkrows[$j]);
        $tmp = "";
    
        if ($j > 0) 
        {
            $tmp = "\n\n    <ul>\n\n";
            //start loop for showing links
            foreach ($linkrows as $link) 
            {
                $tmp .= "    <li>\n<a href=\"{$link['url']}\" ";
                $tmp .= "title=\"{$link['description']}\">";
                $tmp .= "{$link['title']}</a>&nbsp;&ndash; ";
                $tmp .= "{$link['description']}</a>\n    </li>\n\n";
            }
        $tmp .= "    </ul>\n\n";
        
        }
    
        //do we have an audio file? link to it here!
        if ($fields['audio_file'] != "") 
        {
            $tmp .= "<p><a href=\"$tempurl\">Audio Download ";
            $tmp .= "(".getminutes($fields['audio_length'])." min / ".getmegabyte($fields['audio_size'])." MB)</a>";
        }


        echo trim(htmlspecialchars($tmp, ENT_QUOTES));
        echo "    </content:encoded>\n";
    
        //date of publication
        echo "    <pubDate>".date("r", strtotime($fields['posted']))."</pubDate>\n\n";


        //do we add an enclosure?
        if ($fields['audio_file'] != "") 
        {
            echo "    <enclosure url=\"$tempurl\" ";
            echo "length=\"{$fields['audio_size']}\" ";
            echo "type=\"".mime_type($fields['audio_type'])."\" />\n";
            echo "<itunes:duration>".getminutes($fields['audio_length'])."</itunes:duration>\n";
        }

        echo "</item>\n\n";
    
    }

// ------------------------------------------ ADDING COMMENTS --------

    //add comments to current posting
    if ((isset($_GET['com'])) AND ($_GET['com'] != "")) 
    {
    
        //getting data from comments-table
        $dosql  = "SELECT * FROM {$GLOBALS['prefix']}lb_comments WHERE "; 
            
        //comment must belong to current posting
        $dosql .= "posting_id = '". $fields['id']."'";
        $dosql .= " ORDER BY posted ASC";
    
        $result = mysql_query($dosql) OR die (mysql_error());
    
        $i = 0;
        $comrows = "";
        while ($comrows[$i] = mysql_fetch_assoc($result)) { $i += 1; }
        unset($comrows[$i]);
        $i = 0;
    
        foreach ($comrows as $comfields)
        {
            $i += 1;
            //start building the item
            echo "<item>\n";
    
            //globally unique identifier
            echo "    <guid>".$settings['url']."/index.php?id=".$fields['id']."#com".$comfields['id']."</guid>\n";
    
            //title
            echo "    <title>".chars($fields['title'])." (Comment #".$i.")</title>\n";
    
            //link
            echo "    <link>".$settings['url']."/index.php?id=".$fields['id']."#com".$comfields['id']."</link>\n";
    
            //show categories
            showcats ($fields);
        
            //commentator's name
            $author = chars($comfields['name']);
            echo "    <dc:creator>".$author."</dc:creator>\n";
            echo "    <itunes:author>".$author."</itunes:author>\n";
    
            //description
            $mess = chars($comfields['message_html']);
            echo "    <description>\n".$mess."\n    </description>\n";
            echo "    <itunes:summary>\n".$mess."\n    </itunes:summary>\n";
    
            //bodytext
            echo "    <content:encoded>\n".chars($comfields['message_html']);              
            //preparing some variables
            $audiourl = $settings['url']."/audio/".$comfields['audio_file'];
            if ($comfields['audio_file'] == "") { $audiothere = false; } else { $audiothere = true; }
            $tmp = "";
              
            //do we have an audio file? link to it here!
            if ($audiothere) 
            {
                $tmp .= "<p><a href=\"$audiourl\">Audio Download ";
                $tmp .= "(".getminutes($comfields['audio_length'])." min / ".getmegabyte($comfields['audio_size'])." MB)</a>";
            }
            
            echo trim(htmlspecialchars($tmp, ENT_QUOTES));
            echo "    </content:encoded>\n";
        
            //date of publication   
            echo "    <pubDate>".date("r", strtotime($comfields['posted']))."</pubDate>\n\n";
    
            //do we add an enclosure?
            if ($audiothere) 
            {
                echo "    <enclosure url=\"$audiourl\" ";
                echo "length=\"{$comfields['audio_size']}\" ";
                echo "type=\"".mime_type($comfields['audio_type'])."\" />\n";
                echo "    <itunes:duration>".getminutes($comfields['audio_length'])."</itunes:duration>\n";
            }
    
            echo "</item>\n\n";
    
        }
    
    }

}


echo "\n\n</channel>\n\n</rss>";


// ------------------------------------- FUNCTIONS -----------------------------

function showcats ($fields)
{
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
    $tempcats = str_replace("&", "&amp;", trim(substr($tempcats, 0, strrpos($tempcats,","))));
    $tunecats = str_replace (",", "", $tempcats);

    if ($tempcats != "") 
    {
        echo "    <dc:subject>".$tempcats."</dc:subject>\n";
        echo "    <itunes:keywords>".$tunecats."</itunes:keywords>\n";
        
    }
}


function chars ($text) {

$trans_tbl = array(
    "&"=>"&amp;",
    "<"=>"&lt;",
    ">"=>"&gt;",
    "&rsquo;"=>"&apos;"
    );
return trim(strtr(entities_to_chars($text), $trans_tbl));
}


function itunescats() {
$temp = array(
            "Arts &amp; Entertainment"=>"01-00",
            "Architecture"=>"01-01",
            "Books"=>"01-02",
            "Design"=>"01-03",
            "Entertainment"=>"01-04",
            "Games"=>"01-05",
            "Performing Arts"=>"01-06",
            "Photography"=>"01-07",
            "Poetry"=>"01-08",
            "Science Fiction"=>"01-09",
            "Audio Blogs"=>"02-00",
            "Business"=>"03-00",
            "Careers"=>"03-01",
            "Finance"=>"03-02",
            "Investing"=>"03-03",
            "Management"=>"03-04",
            "Marketing"=>"03-05",
            "Comedy"=>"04-00",
            "Education"=>"05-00",
            "K-12"=>"05-01",
            "Higher Education"=>"05-02",
            "Food"=>"06-00",
            "Health"=>"07-00",
            "Diet &amp; Nutrition"=>"07-01",
            "Fitness"=>"07-02",
            "Relationships"=>"07-03",
            "Self-Help"=>"07-04",
            "Sexuality"=>"07-05",
            "International"=>"08-00",
            "Australian"=>"08-01",
            "Belgian"=>"08-02",
            "Brazilian"=>"08-03",
            "Canadian"=>"08-04",
            "Chinese"=>"08-05",
            "Dutch"=>"08-06",
            "French"=>"08-07",
            "German"=>"08-08",
            "Hebrew"=>"08-09",
            "Italian"=>"08-10",
            "Japanese"=>"08-11",
            "Norwegian"=>"08-12",
            "Polish"=>"08-13",
            "Portuguese"=>"08-14",
            "Spanish"=>"08-15",
            "Swedish"=>"08-16",
            "Movies &amp; Television"=>"09-00",
            "Music"=>"10-00",
            "News"=>"11-00",
            "Politics"=>"12-00",
            "Public Radio"=>"13-00",
            "Religion &amp; Spirituality"=>"14-00",
            "Buddhism"=>"14-01",
            "Christianity"=>"14-02",
            "Islam"=>"14-03",
            "Judaism"=>"14-04",
            "New Age"=>"14-05",
            "Philosophy"=>"14-06",
            "Spirituality"=>"14-07",
            "Science"=>"15-00",
            "Sports"=>"16-00",
            "Talk Radio"=>"17-00",
            "Technology"=>"18-00",
            "Computers"=>"18-01",
            "Developers"=>"18-02",
            "Gadgets"=>"18-03",
            "Information Technology"=>"18-04",
            "News"=>"18-05",
            "Operating Systems"=>"18-06",
            "Podcasting"=>"18-07",
            "Smart Phones"=>"18-08",
            "Text/Speech"=>"18-09",
            "Travel"=>"19-00",
            );
return array_flip($temp);
}




?>