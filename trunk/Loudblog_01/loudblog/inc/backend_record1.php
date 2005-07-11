<?php ?>

<h1>Recording (step 1)</h1>

<?php include ('inc/navigation.php'); 

//check url whether we want to update the file of an existing posting
if ((isset($_GET['do'])) AND ($_GET['do'] == "update")) 
    $update_id = $_GET['id']; 
else $update_id = false;

?>

<div class="method">
<form method="post" action="index.php?page=record2&amp;do=nofile<?php
if ($update_id != false) echo "&amp;id=" . $update_id;
?>" enctype="multipart/form-data">
<h2>No audio file</h2>
<div class="data"><p>You can define an audio-file later</p></div>
<input id="butt_nofile" type="submit" value="next step" />
</form>
</div>

<div class="method">
<form method="post" action="index.php?page=record2&amp;do=browser<?php
if ($update_id != false) echo "&amp;id=" . $update_id;
?>" enctype="multipart/form-data">
<h2>Upload via browser</h2>
<div class="data">
<input id="choosefile" type="file" name="fileupload" size="20" accept="audio/*" />
</div>
<input type="submit" value="get file" />
</form>
</div>

<div class="method">
<form method="post" action="index.php?page=record2&amp;do=ftp<?php
if ($update_id != false) echo "&amp;id=" . $update_id;
?>" enctype="multipart/form-data">
<h2>Search upload-folder</h2>
<div class="data">
<select class="datainput" name="filename">
<option value="">choose a file</option>
<option value="">--------</option>
<?php

//gets the filenames of all the files in the upload-folder. make a list.
$uploadfolder = opendir('../upload'); 

while ($file = readdir($uploadfolder)) { 
    if ( substr($file, 0, 1) != ".") { 
        $choosefile = $file; 
        echo "<option value=\"" . urlencode($choosefile) . "\">";
        echo $choosefile . "</option>";
    }
}
closedir($uploadfolder);
?>
</select>
</div>
<input type="submit" value="get file" />
</form>
</div>


<div class="method">
<form method="post" action="index.php?page=record2&amp;do=web<?php
if ($update_id != false) echo "&amp;id=$update_id";
?>" enctype="multipart/form-data">
<h2>Get from web</h2>
<div class="data">
<input class="datainput" type="text" name="linkurl" value="URL goes here" /><br />
<div class="bottomspace"></div>
<input type="radio" name="method" value="link" checked="checked" />link to file
<?php
if (ini_get('allow_url_fopen')) {
    echo "<input type=\"radio\" name=\"method\" value=\"copy\" />copy file to webspace";
}
?>
</div>
<input type="submit" value="get file" />
</form>
</div>

