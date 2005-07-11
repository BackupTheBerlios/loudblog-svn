<?php

//sorting-variables
if (isset($_GET['sort'])) { 
    $sortby = substr($_GET['sort'],1);
    $sortdir = substr($_GET['sort'],0,1); 
    if ($sortdir == "0") { $order = "ASC"; } else { $order = "DESC"; }
} else { 
    $_GET['sort'] = "1posted"; 
    $sortby = "posted"; 
    $order= "DESC"; 
}

//check the rights
if (!allowed(3,"")) 
{ die("<p class=\"msg\">Administrators do some wild party in here. You are not invited :-(</p>"); }

include ('inc/navigation.php');

//offset-calculations
if (isset($_GET['nr'])) {
$currsite = $_GET['nr'];
$offset = ($currsite * $settings['showpostings'])-$settings['showpostings'];
} else { $offset = 0; $currsite = 1; }

$currsite .= "/";
$numbsite = round(countrows("lb_comments")/$settings['showpostings']);
if ($numbsite < countrows("lb_comments")/$settings['showpostings']) { $numbsite += 1; }

//building link to previous site
$prevsite = "<a href=\"index.php".addToUrl("nr","");
if ($currsite == 1) { $prevsite = ""; } 
else { $prevsite .= ($_GET['nr']-1) . "\">&laquo;</a> "; }

//building link to next site
$nextsite = " <a href=\"index.php".addToUrl("nr","");
if ($currsite == $numbsite) { $nextsite = ""; } 
else { $nextsite .= ($currsite+1) . "\">&raquo;</a>"; }

echo "<h1>Comments&nbsp;&nbsp;". $prevsite . $currsite . $numbsite . $nextsite . "</h1>\n";


include ('inc/navigation.php');



//delete data in filesystem and database, if required by url!
if ((isset($_GET['do'])) AND ($_GET['do'] == "x") AND (isset($_GET['id']))) {

    $dosql = "SELECT audio_file 
              FROM ".$GLOBALS['prefix']."lb_comments 
              WHERE id=" . $_GET['id'] . ";";
    $result = mysql_query($dosql) OR die (mysql_error());
    $row = mysql_fetch_assoc($result);
    if ($row['audio_file'] != "") {
        $deletepath = $GLOBALS['audiopath'] . $row['audio_file'];
        unlink ($deletepath);
    }
    $dosql = "DELETE FROM ".$GLOBALS['prefix']."lb_comments 
             WHERE id=" . $_GET['id'] . ";";
    $result = mysql_query($dosql) OR die (mysql_error());

    echo "<p class=\"msg\">successfully deleted comment nr. " . $_GET['id'] . "!</p>";
}

//getting all sql-data needed for the table
$dosql = "SELECT * FROM ".$GLOBALS['prefix']."lb_comments 
          ORDER BY ".$sortby." ".$order." 
          LIMIT ".$offset.", ".$settings['showpostings'].";";
$result = mysql_query($dosql) OR die (mysql_error());

//puttin the sql-data into one huge 2-dimensional array
$rowcount = 0;
while ($row = mysql_fetch_assoc($result)) {
$showtable[$rowcount] = $row;
$rowcount += 1;
}

//getting all posting-titles
$dosql = "SELECT id, title FROM ".$GLOBALS['prefix']."lb_postings;";
$result = mysql_query($dosql) OR die (mysql_error());

//puttin the sql-data into one huge array
while ($row = mysql_fetch_assoc($result)) {
$posting[$row['id']] = $row['title'];
}


//getting current sorting order and direction from url
if (isset($_GET['sort'])) {
    $currsort = substr($_GET['sort'],1);
    $currdir = substr($_GET['sort'],0,1);
} else { $currsort = "posted"; $currdir = "0"; }

//default values for new url-requests
$dirpost = "1"; $dirname = "0"; $dirmess = "0"; $dirbelo = "1"; $dirtime = "0"; 

//make 0 to 1 and vice versa
function changedir($x) {
if ($x == "0") { return "1"; }
if ($x == "1") { return "0"; }
}

//a click on the active sorting order link changes the direction
if ($currsort == "posted") { $dirpost = changedir($currdir); }
if ($currsort == "name") { $dirname = changedir($currdir); }
if ($currsort == "message_input") { $dirmess = changedir($currdir); }
if ($currsort == "posting_id") { $dirbelo = changedir($currdir); }
if ($currsort == "audio_length") { $dirtime = changedir($currdir); }




//table which should be coded way more beautiful
echo "<table>\n<tr>\n";
echo "<th><a href=\"index.php".addToUrl("sort",$dirpost."posted")."\">date</a></th>\n";
echo "<th><a href=\"index.php".addToUrl("sort",$dirname."name")."\">name</a></th>\n";
echo "<th><a href=\"index.php".addToUrl("sort",$dirmess."message_input")."\">message</a></th>\n";
echo "<th><a href=\"index.php".addToUrl("sort",$dirbelo."posting_id")."\">belongs to</a></th>\n";
echo "<th>play</th>\n";
echo "<th class=\"size\"><a href=\"index.php".addToUrl("sort",$dirtime."audio_length")."\">time</a></th>\n";
echo "<th></th>\n</tr>\n\n";


//one table-row for each entry in the database
for ($i=0; $i<$rowcount; $i++) {

    echo "<tr>\n";

    //showing the date/time
    $dateformat = $settings['dateformat'];
    $showdate = date($dateformat , strtotime($showtable[$i]['posted']));
    echo "<td>" . $showdate . "</td>\n";
    
    //showing the commentators name
    echo "<td>" . $showtable[$i]['name'] . "</td>\n";
    
    //generating the link
    $link = $settings['url'] . "/audio/" . $showtable[$i]['audio_file'];

    //showing the message
    $text = strip_tags($showtable[$i]['message_html']);
    if (substr($text,0,80) == $text) {
        $more = "";
    } else { $more = "&hellip;"; }
    echo "<td class=\"message\">".substr($text,0,80).$more."</td>\n";
    
    //showing title of related posting
    echo "<td class=\"postings\">".$posting[$showtable[$i]['posting_id']];
    echo "</td>\n";

    
    //flash player for instant access! (only when file is an .mp3)
    echo "<td>\n";
    if ($showtable[$i]['audio_type'] == 1) {
        echo "<object type=\"application/x-shockwave-flash\" ";
        echo "data=\"backend/emff_list.swf?src=" . $link;
        echo "\" width=\"90\" height=\"19\">\n";
        echo "<param name=\"movie\" value=\"backend/emff_list.swf?src=" . $link;
        echo "\" />\n";
        echo "</object>\n</td>\n";
    } else {
    
        //if its not an mp3, show a simple link!
        if ($showtable[$i]['audio_file'] != "") {
            echo "<a href=\"" . $link . "\">
                 ".getmediatypename($showtable[$i]['audio_type'])."</a>";
            echo "</td>\n";
        }
    }
    

    //showing length in minutes
    echo "<td class=\"size\">" . getminutes($showtable[$i]['audio_length']) . "</td>\n";


    //a beautiful button for deleting
    echo "<td class=\"right\">\n";
    
    if (allowed(1,$showtable[$i]['id'])) {
        echo "<form method=\"post\" enctype=\"multipart/form-data\" ";
        echo "action=\"index.php?page=comments&amp;do=x&amp;";
        echo "id=" . $showtable[$i]['id'] . "\" ";
        echo "onSubmit=\"return areyousure(this)\">\n";
        echo "<input type=\"submit\" value=\"delete\" />\n";
        echo "</form>\n";
    }
    echo "</td>\n";
    
    echo "</tr>\n\n";
}

echo "</table>";



?>