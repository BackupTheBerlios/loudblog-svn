<?php

include ('inc/functions_record.php');
global $fields;

$edit_id = $_GET['id'];

//update stuff, if requested by url
if ((isset($_GET['do'])) AND ($_GET['do'] == "save")) { 
    echo "<h1>Edit ID3 Tags".saveid3($edit_id)."</h1>";
} else {
    //gets the filename from the posting-id we want to edit
    $dosql = "SELECT title, audio_file, filelocal FROM ".$GLOBALS['prefix']."lb_postings 
              WHERE id='". $edit_id ."';";
    $result = mysql_query($dosql) OR die (mysql_error());
    $fields = mysql_fetch_assoc($result);
    
    echo "<h1>Edit ID3 Tags</h1>";
}



if ($fields['filelocal'] != 1) { 
    echo "<p class=\"msg\">Cannot change id3-tags from remote files</p>"; } 
else {



$id3data = getid3data($GLOBALS['audiopath'].$fields['audio_file'],"back");


echo "<form action=\"index.php?page=id3&amp;do=save&amp;id=". $edit_id;
echo "\" method=\"post\" enctype=\"multipart/form-data\">";

echo "<table summary=\"ID3 tags of this audio posting\">\n\n<tr>\n";

echo "<td>Posting Title:</td>\n";
echo "<td><input type=\"text\" style=\"color: #999;\" readonly=\"readonly\" ";
echo "value=\"".$fields['title']."\" /></td>\n";

echo "<td class=\"right text\">Filename:</td>\n";
echo "<td class=\"right\"><input style=\"color: #999;\" type=\"text\" readonly=\"readonly\" ";
echo "value=\"".$fields['audio_file']."\" /></td>\n";

echo "</tr>\n\n";
echo "<tr>\n";

echo "<td>ID3 Title:</td>\n";
echo "<td><input type=\"text\" name=\"id3title\" 
      value=\"".$id3data['title']."\" /></td>\n\n";
      
echo "<td class=\"right text\">ID3 Artist:</td>\n";
echo "<td class=\"right\"><input type=\"text\" name=\"id3artist\" 
      value=\"".$id3data['artist']."\" /></td>\n";

echo "</tr>\n\n";
echo "<tr>\n";

echo "<td>ID3 Album:</td>\n";
echo "<td><input type=\"text\" name=\"id3album\" 
      value=\"".$id3data['album']."\" /></td>\n\n";
      
echo "<td class=\"right text\">ID3 Year:</td>\n";
echo "<td class=\"right\"><input type=\"text\" name=\"id3year\" 
      value=\"".$id3data['year']."\" /></td>\n";

echo "</tr>\n\n";
echo "<tr>\n";

echo "<td>ID3 Track-Nr:</td>\n";
echo "<td><input type=\"text\" name=\"id3track\" 
      value=\"".$id3data['track']."\" /></td>\n\n";
      
echo "<td class=\"right text\">ID3 Genre:</td>\n";
echo "<td class=\"right\"><input type=\"text\" name=\"id3genre\" 
      value=\"".$id3data['genre']."\" /></td>\n";

echo "</tr>\n\n";
echo "<tr>\n";
      
echo "<td>ID3 Comment:</td>\n";
echo "<td><textarea name=\"id3comment\">". 
     $id3data['comment']."</textarea></td>\n";
echo "<td></td><td></td>";

echo "</tr>\n\n";

echo "</table>\n";
echo "<div id=\"save\"><input type=\"submit\" value=\"update mp3\" />";
echo "<input onClick=\"window.close();\" type=\"submit\" value=\"close\" /></div>";
echo "</form>";

}

/* --------------------------------------- */

function saveid3($update_id) {
global $settings;
global $fields;

//getting the filename from the id
$dosql = "SELECT title, id, filelocal, audio_file FROM ".$GLOBALS['prefix']."lb_postings 
          WHERE id='" . $update_id . "';";
$result = mysql_query($dosql) OR die (mysql_error());
$fields = mysql_fetch_assoc($result);


//Warning if remote file is to be changed :-)
if ($fields['filelocal'] != "1") { 
    echo "<p>You cannot change remote files.</p>";
} else {

//change posted ID3-data
$filename = $GLOBALS['audiopath'] . $fields['audio_file'];

// Initialize getID3 engine
require_once('inc/id3/getid3.php');
$getID3 = new getID3;
$getID3->encoding = 'UTF-8';   

require_once('inc/id3/write.php');
$tagwriter = new getid3_writetags;
$tagwriter->filename   = $filename;
$tagwriter->tagformats = array('id3v2.3', 'ape');
$tagwriter->overwrite_tags = true; 
$tagwriter->remove_other_tags = true; 


// populate data array 
$TagData['title'][]  = stripslashes($_POST['id3title']);
$TagData['artist'][]  = stripslashes($_POST['id3artist']);
$TagData['album'][]  = stripslashes($_POST['id3album']);
$TagData['track'][]  = stripslashes($_POST['id3track']);
$TagData['genre'][]  = stripslashes($_POST['id3genre']);
$TagData['year'][]  = stripslashes($_POST['id3year']);
$TagData['comment'][]  = stripslashes($_POST['id3comment']);
$tagwriter->tag_data = $TagData;

// write tags 
if ($tagwriter->WriteTags()) { 
    return " &mdash; Success!";

} else {
	return " &mdash; Failure!";
	    	if (!empty($tagwriter->warnings)) { 
        echo 'There were some warnings:<br>'.implode('<br><br>', $tagwriter->warnings); }

}
}
}








?>
