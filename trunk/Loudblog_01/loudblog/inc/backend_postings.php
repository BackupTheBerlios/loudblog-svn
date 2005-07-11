<?php

//sorting-variables
if (isset($_GET['sort'])) { 
    $sortby = $_GET['sort']; 
    if ($sortby = "title") { $order = "ASC"; }
    if ($sortby = "posted") { $order = "DESC"; }
    if ($sortby = "status") { $order = "DESC"; }
}
else { $sortby = "posted"; $order= "DESC"; }

//offset-calculations
if (isset($_GET['nr'])) {
$currsite = $_GET['nr'];
$offset = ($currsite * $settings['showpostings'])-$settings['showpostings'];
} else { $offset = 0; $currsite = 1; }

$currsite .= "/";
$numbsite = round(countrows("lb_postings")/$settings['showpostings']);
if ($numbsite < countrows("lb_postings")/$settings['showpostings']) { $numbsite += 1; }

//building link to previous site
$prevsite = "<a href=\"index.php?page=postings&nr=";
if ($currsite == 1) { $prevsite = ""; } 
else { $prevsite .= ($_GET['nr']-1) . "\">&laquo;</a> "; }

//building link to previous site
$nextsite = " <a href=\"index.php?page=postings&nr=";
if ($currsite == $numbsite) { $nextsite = ""; } 
else { $nextsite .= ($currsite+1) . "\">&raquo;</a>"; }

echo "<h1>Postings&nbsp;&nbsp;". $prevsite . $currsite . $numbsite . $nextsite . "</h1>\n";


include ('inc/navigation.php');



//delete data in filesystem and database, if required by url!
if ((isset($_GET['do'])) AND ($_GET['do'] == "x") AND (isset($_GET['id']))) {

    $dosql = "SELECT filelocal, audio_file 
              FROM ".$GLOBALS['prefix']."lb_postings 
              WHERE id=" . $_GET['id'] . ";";
    $result = mysql_query($dosql) OR die (mysql_error());
    $row = mysql_fetch_assoc($result);
    if ($row['filelocal'] == 1) {
        $deletepath = $GLOBALS['audiopath'] . $row['audio_file'];
        unlink ($deletepath);
    }
    $dosql = "DELETE FROM ".$GLOBALS['prefix']."lb_postings 
             WHERE id=" . $_GET['id'] . ";";
    $result = mysql_query($dosql) OR die (mysql_error());

    echo "<p class=\"msg\">successfully deleted entry nr. " . $_GET['id'] . "!</p>";
}



//getting all sql-data needed for the table
$dosql = "SELECT 
            id, author_id, posted, title, filelocal, audio_file, audio_type, audio_size, audio_length, status 
          FROM 
            ".$GLOBALS['prefix']."lb_postings 
          ORDER BY 
            ".$sortby." ".$order." 
          LIMIT ".$offset.", ".$settings['showpostings'].";";
$result = mysql_query($dosql) OR die (mysql_error());


//puttin the sql-data into one huge 2-dimensional array
$rowcount = 0;
while ($row = mysql_fetch_assoc($result)) {
$showtable[$rowcount] = $row;
$rowcount += 1;
}


//table which should be coded way more beautiful
echo "<table>\n<tr>\n";
echo "<th>date</th>\n<th>by</th>\n<th>title</th>\n<th>play audio</th>\n";
echo "<th>size</th>\n<th>status</th>\n<th></th>\n</tr>\n\n";


//one table-row for each entry in the database
for ($i=0; $i<$rowcount; $i++) {

    echo "<tr>\n";

    //showing the date/time
    $dateformat = $settings['dateformat'];
    $showdate = date($dateformat , strtotime($showtable[$i]['posted']));
    echo "<td>" . $showdate . "</td>\n";
    
    //showing the author
    $tempauth = getnickname($showtable[$i]['author_id']);
    if ($tempauth == $_SESSION['nickname']) { $tempauth = "<b>yourself</b>"; }
    echo "<td>" . $tempauth . "</td>\n";

    //generating the link
    if ($showtable[$i]['filelocal'] == 1)
    $link = $settings['url'] . "/audio/" . $showtable[$i]['audio_file'];
    else $link = $showtable[$i]['audio_file'];

    //showing the title
    echo "<td>"; 
    echo "<a href=\"index.php?page=record2&amp;do=edit&amp;";
    echo "id=" . $showtable[$i]['id'] . "\">\n";
    echo $showtable[$i]['title'] . "</a></td>\n";
    
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
            echo "<a href=\"" . $link . "\">Listen to 
                 ".getmediatypename($showtable[$i]['audio_type'])."</a>";
            echo "</td>\n";
        }
    }
    
    /*
    
    //a beautiful button for listening
    echo "<td>\n";
    echo "<form method=\"get\"";
    echo "action=\"" . $link . "\">\n";
    echo "<input type=\"submit\" value=\"listen\" />\n";
    echo "</form>\n</td>\n";

    */

    //showing audio size in megabyte
    echo "<td>" . getmegabyte($showtable[$i]['audio_size']) . " MB</td>\n";





    //the status radio buttons
    $temp = $showtable[$i]['status'];
    echo "<td>\n";
    if ($temp == 1) { echo "<span style=\"color:#dd0067;\">draft</span>"; }
    if ($temp == 2) { echo "<span style=\"color:#090;\">finished</span>"; }
    if ($temp == 3) { echo "live"; }
    
    echo "</td>\n";


    //a beautiful button for deleting
    echo "<td class=\"right\">\n";
    
    if (allowed(1,$showtable[$i]['id'])) {
        echo "<form method=\"post\" enctype=\"multipart/form-data\" ";
        echo "action=\"index.php?page=postings&amp;do=x&amp;";
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