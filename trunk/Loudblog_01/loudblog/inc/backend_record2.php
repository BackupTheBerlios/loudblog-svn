<?php ?>

<h1>Recording (step 2)</h1>

<?php

include ('inc/functions_record.php');
include ('inc/navigation.php');


//where did we get the audio file from? go to appropriate function!
//and don't come back without the id of the posting we will edit later!

if (isset($_GET['do'])) {

if ($_GET['do'] == "browser") {
    if (isset($_GET['id'])) $edit_id = upload_browser($_GET['id']);
    else $edit_id = upload_browser(false);
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
if (($_GET['do'] == "edit") OR ($_GET['do'] == "saveid3"))
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
    
    //include a markup-helper and make use of it
    switch ($settings['markuphelp']) {
    case "1":
        include ('inc/markuphelp/textile.php');
        $textile = new Textile;
        $temphtml = $textile->TextileThis($_POST['message']);
        break;
    case "2":
        include ('inc/markuphelp/markdown.php');
        $temphtml = Markdown($_POST['message']);
        break;
    case "3":
        include ('inc/markuphelp/stringparser_bbcode.class.php');
        include ('inc/markuphelp/bbcode.php');
        $temphtml = $bbcode->parse($_POST['message']);
        break;
    case "0":
        $temphtml = $_POST['message'];
        break;
    }
    
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

}


//gets all the data from the posting-id we want to edit
$dosql = "SELECT * FROM ".$GLOBALS['prefix']."lb_postings 
          WHERE id='". $edit_id ."';";
$result = mysql_query($dosql) OR die (mysql_error());
$fields = mysql_fetch_assoc($result);


//if we come from the id3edit-page, put posted id3-Tags to mp3-file!
if ($_GET['do'] == "saveid3") {
include('inc/id3.php');
$tempfile = $GLOBALS['audiopath'] . $fields['audio_file'];
$id3 = new ID3v1x($tempfile);

if($id3->write_tag(1, $_POST['id3title'], $_POST['id3artist'], 
    $_POST['id3album'], $_POST['id3year'], $_POST['id3comment'], "2", "13") == true) {  echo "Successful!"; }
else { echo "Error!"; }
}


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
echo "<h3>audio (uploaded by " . $tempauth . ")</h3>\n";

//showing and linking to audiofile. local or not?
if ($fields['filelocal'] == 1)
    $link = $settings['url'] . "/audio/" . $fields['audio_file'];
else  $link = $fields['audio_file']; 

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
echo "<p class=\"filename topspace2\"><a href=\"" . $link . "\">"; 
echo $fields['audio_file'] . "</a></p>\n"; 

//button for changing the audio file
if (allowed(1,$edit_id)) {
    echo "<input id=\"change\" value=\"change\" type=\"button\"";
    echo " onClick=\"self.location.href='index.php?page=record1&amp;do=update&amp;id=";
    echo $fields['id'] . "'\" />";
}

/* SEEMS NOT TO WORK PROPERLY. WAITING FOR A BETTER CLASS OR FUNCTION

//button for manipulating id3-tags
if (allowed(1,$edit_id)) {
    echo "<input value=\"id3-tags\" type=\"button\"";
    echo " onClick=\"self.location.href='index.php?page=id3&amp;id=";
    echo $fields['id'] . "'\" />";
}

*/

?>

<hr />


<!--                                      type  -->
<div class="fileinfo left">
<h3>type</h3>
<select <?php echo readonly($edit_id); ?> name="audio_type">
<option value="0">--</option>
<?php
    for ($i=1; $i<5; $i++) {
        echo "<option value=\"" . $i . "\" ";
        if ($fields['audio_type'] == $i) echo " selected "; 
        echo readonly($edit_id);
        echo ">" . getmediatypename($i)."</option>\n";
    } ?>
    
</select>
</div>


<!--                                      size  -->
<div class="fileinfo center">
<h3>size</h3>

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

<div class="fileinfo right">
<h3>duration</h3>
<input <?php echo readonly($edit_id); ?>type="text" name="audio_length" value="<?php 
    echo $fields['audio_length'] . " min"; ?>" />
</div>

<hr />



<!--                                      comments  -->

<div class="fileinfo left">
<?php
//preparing preselection of the comments-on/off-switch
if ($fields['comment_on'] == 1) { $temp1 = 'checked="checked"'; $temp2 = ''; }
                          else { $temp1 = ''; $temp2 = 'checked="checked" '; }
                  
//preparing preselection of the comments-size-menue     
$tempcommsize = array("204800", "512000", "1048576", "1572864", "2097152", "5242880", "10485760");
$tempcommshow = array("200 KB", "500 KB", "1 MB", "1.5 MB", "2 MB", "5 MB", "10 MB");
?>

<h3>Comments</h3>
<input <?php echo readonly($edit_id); ?>class="radio" type="radio" name="comment_on" value="on" <?php echo $temp1; ?> />On
<input <?php echo readonly($edit_id); ?>class="radio" type="radio" name="comment_on" value="off" <?php echo $temp2; ?>/>Off
</div>

<div class="fileinfo center">
<h3>limit</h3>
<select <?php echo readonly($edit_id); ?>name="comment_size">
<?php

//generating dropdownmenue with comment-sizes
for ($i=0; $i<=6; $i++) {
    echo "<option " . readonly($edit_id) . "value=\"".$tempcommsize[$i]."\"";
    if ($tempcommsize[$i] == $fields['comment_size']) echo " selected";
    echo ">".$tempcommshow[$i]."</option>\n";
}

?>
<option>no limit</option>
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
        <?php echo readonly($edit_id) . $temp[1]; ?> />draft

<input type="radio" name="status" value="2" 
        <?php echo readonly($edit_id) . $temp[2]; ?> />finished

<input type="radio" name="status" value="3" 

        <?php if (!allowed(2,$fields['id'])) { echo "readonly=\"readonly\""; }
        echo $temp[3]; ?> />live

<hr />

</div>

<div id="postsave">



<!--                                      Posted  -->

<h3 class="clearfloat">Posting Time</h3>
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