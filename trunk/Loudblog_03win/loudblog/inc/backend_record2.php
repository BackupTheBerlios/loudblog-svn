<?php

echo "<h1>Recording (step 2)</h1>";

include ('inc/functions_record.php');
include ('inc/navigation.php');


//where did we get the audio file from? go to appropriate function!
//and don't come back without the id of the posting we will edit later!

if (isset($_GET['do'])) {

if ($_GET['do'] == "browser") {
    if (isset($_GET['id'])) $edit_id = upload_browser($_GET['id']);
    else $edit_id = upload_browser(false);
}

if ($_GET['do'] == "cgi") {
    if (isset($_GET['id'])) $edit_id = cgi_copy($_GET['id']);
    else $edit_id = cgi_copy(false);
}
    
if (($_GET['do'] == "web") AND ($_POST['method'] == "link")) {
    if (isset($_GET['id'])) $edit_id = link_web($_GET['id']);
    else $edit_id = link_web(false); 
}
    
if (($_GET['do'] == "web") AND ($_POST['method'] == "copy")) {
    if (isset($_GET['id'])) $edit_id = fetch_web($_GET['id']);
    else $edit_id = fetch_web(false); 
}

if ($_GET['do'] == "ftp") {
    if (isset($_GET['id'])) $edit_id = copy_ftp($_GET['id']);
    else $edit_id = copy_ftp(false); 
}

if ($_GET['do'] == "nofile") {
    if (isset($_GET['id'])) $edit_id = nofile($_GET['id']);
    else $edit_id = nofile(false); 
}


//oh, we only got here from the postings-page to edit some posting?
//oh, we only got back here from the id3edit-page?
if ($_GET['do'] == "edit")
$edit_id = $_GET['id']; 

//we want to save some changes? here we go!
if ($_GET['do'] == "save") {
    $edit_id = $_GET['id']; 
    
    //build the date string
    if (!isset($_POST['now'])) {
    $posted = $_POST['post1'] . "-" . $_POST['post2'] . "-" .
              $_POST['post3'] . " " . $_POST['post4'] . ":" .
              $_POST['post5'] . ":00";
    } else { $posted = date("Y-m-d H:i:s"); }
    
    //make a valid temp-title and put textile onto the posted bodytext
    $temptitle = htmlentities($_POST['title'], ENT_QUOTES, "UTF-8");
    $tempmess = htmlentities($_POST['message'], ENT_QUOTES, "UTF-8");
    
    //extract duration- and size integer from input 
    $pieces = explode (" ", $_POST['audio_length']);
    $lengthint = round ($pieces[0], 1);
    $pieces2 = explode (" ", $_POST['audio_size']);
    $sizeint = round ($pieces2[0], 1) * 1024 * 1024;
    
    //use preferred html-helper tool
    $temphtml = makehtml($_POST['message']);
    
    //get the data for comment-options
    if ($_POST['comment_on'] == "on") $comments = "1"; else $comments = "0";    
    
    //write things from post-data into database
    $dosql = "UPDATE ".$GLOBALS['prefix']."lb_postings SET
    
              title         = '" . $temptitle . "',
              message_input = '" . $tempmess . "',
              message_html  = '" . $temphtml . "',
              posted        = '" . $posted . "',
              comment_on    = '" . $comments . "',
              audio_length  = '" . $lengthint . "', 
              audio_size    = '" . $sizeint . "', 
              comment_size  = '" . $_POST['comment_size'] . "',
              category1_id  = '" . $_POST['cat1'] . "',
              category2_id  = '" . $_POST['cat2'] . "',
              category3_id  = '" . $_POST['cat3'] . "',
              category4_id  = '" . $_POST['cat4'] . "',
              audio_type    = '" . $_POST['audio_type'] . "',
              status        = '" . $_POST['status'] . "'
              
              WHERE id = '" . $edit_id . "';";
    $result = mysql_query($dosql) OR die (mysql_error());
    
    //deleting links from database
    $dosql = "DELETE FROM ".$GLOBALS['prefix']."lb_links 
              WHERE posting_id=" . $edit_id . ";";
    $result = mysql_query($dosql) OR die (mysql_error());
        
    //put posted links into database
    for ($i = 0; $i< $settings['showlinks']; $i++) {
        $temptit = "linktit" . $i;
        $tempurl = "linkurl" . $i;
        $tempdes = "linkdes" . $i;
        
        if ($_POST[$tempurl] != "") {
        $dosql = "INSERT INTO ".$GLOBALS['prefix']."lb_links
                 (posting_id, linkorder, title, url, description)
                 VALUES
                 (
                 '" . $edit_id . "',
                 '" . $i . "',
                 '" . htmlentities($_POST[$temptit], ENT_QUOTES, "UTF-8") . "',
                 '" . htmlentities($_POST[$tempurl], ENT_QUOTES, "UTF-8") . "',
                 '" . htmlentities($_POST[$tempdes], ENT_QUOTES, "UTF-8") . "'
                 );";   
        $result = mysql_query($dosql) OR die (mysql_error());
        }
    }

    //ping via xml-rpc, if status is "on air"
    if ($_POST['status'] == 3) { include ('inc/ping.php'); }

}


//gets all the data from the posting-id we want to edit
$dosql = "SELECT * FROM ".$GLOBALS['prefix']."lb_postings 
          WHERE id='". $edit_id ."';";
$result = mysql_query($dosql) OR die (mysql_error());
$fields = mysql_fetch_assoc($result);


// --------------------------------------------------------------------------


//show us the (pre-filled) forms to change the details!!!
echo "<form action=\"index.php?page=record2&amp;do=save&amp;id=". $edit_id;
echo "\" method=\"post\" enctype=\"multipart/form-data\">";
?>

<div id="leftcolumn">

<!--                                      title  -->
<h3>Title</h3>
<input id="title" type="text" name="title" 
    <?php 
    echo readonly($edit_id);
    echo " value=\"" . urldecode($fields['title']) . "\" />";
    ?>


<!--                                      text message  -->



<h3>Message</h3>
<?php 
echo "<textarea " . readonly($edit_id) . " name=\"message\">";
echo $fields['message_input']; 
echo "</textarea>";
?>


<!--                                      categories  -->

<h3>Categories</h3>

<?php

//getting all data from category-table
$dosql = "SELECT * FROM ".$GLOBALS['prefix']."lb_categories ORDER BY id;";
$result = mysql_query($dosql) OR die (mysql_error());
$i = 0;
while ($row = mysql_fetch_assoc($result)) {
    $cats[$i] = $row;
    $i += 1;
}

//show four lists with categories
for ($i=1; $i<5; $i++) {
    echo "<select class=\"category\" ". readonly($edit_id);
    echo " name=\"cat" . $i . "\">\n";
    echo "<option value=\"NULL\">---</option>\n";

    //show all items in each list
    $j = 0;
    foreach ($cats as $showcat) {
        echo "<option value=\"" . $cats[$j]['id'] . "\"";
        $temp = "category" . ($i) . "_id";
        if ($fields[$temp] == $cats[$j]['id']) echo " selected";
        echo ">" . urldecode($cats[$j]['name']) . "</option>\n";
        $j += 1;
    }
    echo "</select>\n";
}?>

</div>

<div id="rightcolumn">


<!--                                      file  -->

<?php
$tempauth = getnickname($fields['author_id']);
if ($tempauth == $_SESSION['nickname']) { $tempauth = "yourself"; }

if (!empty($fields['audio_file']) AND ($fields['audio_file'] != "NULL")) {

echo "<h3>audio (by " . $tempauth . ")</h3>\n";

//do we have a local file? then show detailled information!
if ($fields['filelocal'] == 1) {

//showing and linking to audiofile. local or not?
$link = $settings['url'] . "/audio/" . $fields['audio_file'];
 

//can...only...play...mp3...with...flash...player
if ($fields['audio_type'] == 1) {
    echo "<object class=\"topspace2\" type=\"application/x-shockwave-flash\"\n";
    echo "data=\"backend/emff_rec2.swf?src=". $link . "\""; 
    echo " width=\"295\" height=\"9\">\n";
    echo "<param name=\"movie\" 
          value=\"backend/emff_rec2.swf?src=".$link."\" />";
    echo "</object>\n";
}

//preparing data
$id3 = getid3data($GLOBALS['audiopath'].$fields['audio_file'],"back");

//showing data for that mp3 file
echo "<table id=\"audiodata\">\n";
echo "<tr><td>Filename</td><td><a href=\"" . $link . "\">"; 
echo $fields['audio_file'] . "</a></td></tr>\n"; 

echo "<tr><td>Size/Duration</td><td>".getmegabyte($id3['size'])."MB / ". $id3['duration']."sec</td></tr>\n";

echo "<tr><td>Quality</td><td>";
echo ($id3['audio']['bitrate'] / 1000)."kb/s (";
echo strtoupper($id3['audio']['bitrate_mode']).") / ";
echo ($id3['audio']['sample_rate'] / 1000)."kHz / ";
echo ($id3['audio']['channelmode']);
echo "</td></tr>\n";

echo "<tr><td>ID3 Title/Track</td><td>".$id3['title'];
echo " (".$id3['track'].")</td></tr>\n";
echo "</table>\n";

//button for manipulating id3-tags
if (allowed(1,$edit_id)) {
    echo "<input href=\"index.php?page=id3&amp;id={$fields['id']}\" ";
    echo "class=\"audiobutton\" value=\"edit ID3 tags\" type=\"button\" ";
    echo "onClick=\"link_popup(this,780,390); return false\" />";
}

//button for changing the audio file
if (allowed(1,$edit_id)) {
    echo "<input class=\"audiobutton right\" value=\"change audio file\" type=\"button\"";
    echo " onClick=\"self.location.href='index.php?page=record1&amp;do=update&amp;id={$fields['id']}'\" />";
}


//sending size/length/type information for database
echo "<input type=\"hidden\" name=\"audio_length\" value=\"".getseconds($id3['duration'])."\" />";
echo "<input type=\"hidden\" name=\"audio_size\" value=\"".getmegabyte($id3['size'])."\" />";
echo "<input type=\"hidden\" name=\"audio_type\" value=\"".type_suffix($fields['audio_file'])."\" />";

} else {
//we have only a link to a remote file? show other data!

$link = $fields['audio_file']; 

//can...only...play...mp3...with...flash...player
if ($fields['audio_type'] == 1) {
    echo "<object class=\"topspace2\" type=\"application/x-shockwave-flash\"\n";
    echo "data=\"backend/emff_rec2.swf?src=". $link . "\""; 
    echo " width=\"295\" height=\"9\">\n";
    echo "<param name=\"movie\" 
          value=\"backend/emff_rec2.swf?src=".$link."\" />";
    echo "</object>\n";
}

//showing plain link
echo "<table id=\"audiodata\">\n";
echo "<tr><td><a href=\"" . $link . "\">"; 
echo wordwrap($fields['audio_file'], 50, "<br />", 1) . "</a></td></tr>\n"; 
echo "</table>\n";

echo "<input type=\"hidden\" name=\"audio_type\" value=\"".type_suffix($fields['audio_file'])."\" />";

?>

<hr />


<!--                                      size  -->
<div class="rec2size">
<h3>Size</h3>

<?php
if ($fields['filelocal'] == 1) {
echo "<input ". readonly($edit_id) . "type=\"text\" readonly=\"readonly\" ";
echo "value=\"" . getmegabyte($fields['audio_size']) . " MB\" />";
echo "<input type=\"hidden\" name=\"audio_size\" ";
echo "value=\"" . getmegabyte($fields['audio_size']) . "\" />";
} else {
echo "<input ". readonly($edit_id) . "type=\"text\" name=\"audio_size\"";
echo "value=\"" . getmegabyte($fields['audio_size']) . " MB\" />";
}
?>

</div>
    
    
<!--                                      length  -->

<div class="rec2size">
<h3>Length</h3>
<input <?php echo readonly($edit_id); ?>type="text" name="audio_length" value="<?php 
    echo $fields['audio_length'] . " sec"; ?>" />
</div>


<div class="fileinfo right">
<h3>Audio file</h3>
<?php

//button for changing the audio file

if (allowed(1,$edit_id)) {
    echo "<input class=\"audiobutton change\" value=\"change audio file\" type=\"button\"";
    echo " onClick=\"self.location.href='index.php?page=record1&amp;do=update&amp;id=";
    echo $fields['id'] . "'\" />";
}
echo "</div>";



}

echo "<hr />";

} else {
// We have no audio file at all? Huh? Okay then...

echo "<h3>No Audio</h3>\n";
echo "<div class=\"fileinfo left\">\n";
echo "<p>Maybe next time ...</p>\n";
echo "</div>\n\n";

//show hidden input fields with zero values
echo "<input type=\"hidden\" name=\"audio_length\" value=\"0\" />";
echo "<input type=\"hidden\" name=\"audio_size\" value=\"0\" />";
echo "<input type=\"hidden\" name=\"audio_type\" value=\"0\" />";

echo "<div class=\"fileinfo right\">";
if (allowed(1,$edit_id)) {
    echo "<input class=\"audiobutton change\" value=\"add audio file\" type=\"button\"";
    echo " onClick=\"self.location.href='index.php?page=record1&amp;do=update&amp;id=";
    echo $fields['id'] . "'\" />";
}
echo "</div>";
echo "<hr />";
}

?>

<!--                                      comments  -->


<?php
//preparing preselection of the comments-on/off-switch
if ($fields['comment_on'] == 1) { $temp1 = 'checked="checked"'; $temp2 = ''; }
                          else { $temp1 = ''; $temp2 = 'checked="checked" '; }
                  
//preparing preselection of the comments-size-menue     
$tempcommsize = array(
    "0"=>"no audio allowed",
    "204800"=>"200 KB", 
    "512000"=>"500 KB", 
    "1048576"=>"1 MB", 
    "1572864"=>"1.5 MB", 
    "2097152"=>"2 MB", 
    "5242880"=>"5 MB", 
    "10485760"=>"10 MB",
    "999999999"=>"no limit");
?>

<div class="fileinfo left">
<h3>Comments</h3>
<input <?php echo readonly($edit_id); ?>class="radio" type="radio" name="comment_on" value="on" <?php echo $temp1; ?> />On&nbsp;&nbsp;
<input <?php echo readonly($edit_id); ?>class="radio" type="radio" name="comment_on" value="off" <?php echo $temp2; ?>/>Off
</div>


<div class="fileinfo right">
<h3>Size Limit</h3>
<?php
echo "<select ".readonly($edit_id)." name=\"comment_size\">";

//generating dropdownmenue with comment-sizes
foreach ($tempcommsize as $tempsize => $tempshow) {
    echo "<option " . readonly($edit_id) . "value=\"".$tempsize."\"";
    if ($tempsize == $fields['comment_size']) echo " selected";
    echo ">".$tempshow."</option>\n";
}
?>
</select>
</div>

<hr />




<!--                                      status -->

<?php
//very un-elegant preparing for showing the current status
$temp = array ("", "", "", "");
$temp[$fields['status']] = "checked=\"checked\"";
?>

<h3>Status</h3>

<input type="radio" name="status" value="1" 
        <?php echo readonly($edit_id) . $temp[1]; ?> />Draft&nbsp;&nbsp;

<input type="radio" name="status" value="2" 
        <?php echo readonly($edit_id) . $temp[2]; ?> />Finished&nbsp;&nbsp;

<input type="radio" name="status" value="3" 

        <?php if (!allowed(2,$fields['id'])) { echo "readonly=\"readonly\""; }
        echo $temp[3]; ?> />On Air!


<hr />

</div>

<div id="postsave">



<!--                                      Posted  -->

<h3>Posting Time</h3>
<div id="date">



<input id="year" type="text" name="post1" maxlength="4" value="<?php 
    echo date("Y", strtotime($fields['posted'])); 
    echo "\" " . readonly($edit_id); ?>/>
<input type="text" name="post2" maxlength="2" value="<?php 
    echo date("m", strtotime($fields['posted']));
    echo "\" " . readonly($edit_id); ?>/>
<input type="text" name="post3" maxlength="2" value="<?php 
    echo date("d", strtotime($fields['posted']));
    echo "\" " . readonly($edit_id); ?>/>
<h4> at </h4>
<input type="text" name="post4" maxlength="2" value="<?php 
    echo date("H", strtotime($fields['posted']));
    echo "\" " . readonly($edit_id); ?>/>
<h4>:</h4>
<input type="text" name="post5" maxlength="2" value="<?php 
    echo date("i", strtotime($fields['posted']));
    echo "\" " . readonly($edit_id); ?>/>
  
<h4> Set to now</h4>  
<input <?php echo readonly($edit_id); ?> id="now" type="checkbox" name="now" />

</div>



<!--                                      submit-button  -->
<div class="submit">
<?php
if (allowed(1,$edit_id)) {
echo "<input class=\"save\" type=\"submit\" value=\"save all\" />";
}
?>
</div>

</div>


<div id="hyperlinks">



<!--                                      links  -->
<?php

//getting existing links from database
$dosql = "SELECT * FROM ".$GLOBALS['prefix']."lb_links 
          WHERE posting_id = '" . $edit_id . "' 
          ORDER BY linkorder ASC;";
$result = mysql_query($dosql) OR die (mysql_error());
$i = 0;
while ($row = mysql_fetch_assoc($result)) {
    $links[$i] = $row;
    $i += 1;
}

echo "<table class=\"plain topspace\">\n<tr>\n<th>link URL</th>\n";
echo "<th>link name</th>\n<th>";
echo "link description</th>\n</tr>\n";

for ($i = 0; $i < $settings['showlinks']; $i++) {
    
    //no entry in database? generate empty data!
    if (!isset($links[$i]['linkorder'])) {
        $links[$i]['url'] = "";
        $links[$i]['title'] = "";
        $links[$i]['description'] = "";
    }
 
    //show the link-forms
    echo "<tr>";
    
    echo "<td class=\"left\"><input ". readonly($edit_id) . " type=\"text\" value=\"" . $links[$i]['url'];
    echo "\" name=\"linkurl" . $i . "\" /></td>\n";
    
    echo "<td class=\"center\"><input ". readonly($edit_id) . " type=\"text\" value=\"" . $links[$i]['title'];
    echo "\" name=\"linktit" . $i . "\" /></td>\n";
    
    echo "<td class=\"right\"><input ". readonly($edit_id) . " type=\"text\" value=\"" . $links[$i]['description'];
    echo "\" name=\"linkdes" . $i . "\" /></td>\n";
    
    echo "</tr>";
}

?>
</table>

</div>



<!--                                      submit-button  -->
<div class="submit">

<?php
if (allowed(1,$edit_id)) {
echo "<input class=\"save\" type=\"submit\" value=\"save all\" />";
}
?>
</div>



</form>

<?php 


    
}

//no audio file? error message!
else { 

echo "<p class=\"msg\">No audio file defined!</p>\n\n"; 

}


?>