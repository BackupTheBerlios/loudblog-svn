<?php ?>

<h1>Recording (step 1)</h1>

<?php include ('inc/navigation.php'); 

//check url whether we want to update the file of an existing posting
if ((isset($_GET['do'])) AND ($_GET['do'] == "update")) 
    $update_id = $_GET['id']; 
else $update_id = false;

?>

<!--  NO FILE FOR UPLOADING  -->

<div class="method">
<form method="post" action="index.php?page=record2&amp;do=nofile<?php
if ($update_id != false) echo "&amp;id=" . $update_id;
?>" enctype="multipart/form-data">
<h2>No Audio File</h2>
<div class="data"><p>You can define an audio-file later</p></div>
<input id="butt_nofile" type="submit" value="next step" />
</form>
</div>

<!--  BROWSER-UPLOAD (PHP OR CGI)  -->

<div class="method">

<?php

//do we use a CGI-script for uploading?
if ((isset($settings['cgi'])) AND ($settings['cgi'] == 1)) {

    //where is the CGI-script located?
    if ((isset($settings['cgi_local'])) AND ($settings['cgi_local'] == 1)) {
        $tempaddress = "modules/cgi-bin/upload.cgi";
    } else { $tempaddress = $settings['cgi_url']."/upload.cgi"; }

    echo "<form method=\"post\" action=\"".$tempaddress."\" enctype=\"multipart/form-data\" onSubmit=\"return saythis('This may take some minutes. Start uploading now!')\">\n";
    
    echo "<h2>Upload via Browser</h2>\n<div class=\"data\">\n";
    echo "<input id=\"choosefile\" type=\"file\" name=\"file\" size=\"20\" accept=\"audio/*\" />\n";
    
    echo "</div>\n";
    echo "<input type=\"submit\" value=\"get file\" />\n";
    echo "<input type=\"hidden\" name=\"callback_script\" value=\"".$settings['url']."/loudblog/index.php?page=record2&amp;do=cgi";
    
    if ($update_id != false) { echo "&amp;id=" . $update_id; }
    
    echo "\" />\n</form>\n";

} else {  //okay, we use the classic php method for uploading!

    echo "<form method=\"post\" action=\"index.php?page=record2&amp;do=browser";
    
    if ($update_id != false) { echo "&amp;id=" . $update_id; }
    
    echo "\" enctype=\"multipart/form-data\" onSubmit=\"return saythis('This may take some minutes. Start uploading now!')\">\n";
    echo "<h2>Via Browser <small>(<".getmegabyte(uploadlimit())."MB)</small></h2>\n";
    echo "<div class=\"data\">\n";
    echo "<input id=\"choosefile\" type=\"file\" name=\"fileupload\" size=\"20\" accept=\"audio/*\" />";
    echo "</div>\n<input type=\"submit\" value=\"get file\" />\n</form>";
}

?>

</div>

<?php

if ($settings['ftp'] == 1) {

echo "<!--  UPLOAD VIA FTP  -->";

echo "\n\n<div class=\"method\">\n";
echo "<h2>Upload via FTP</h2>\n";
echo "<div class=\"data\">\n";

//not forget the id, if we are updating a file
if (isset($_GET['id'])) { $idlink = "&id=".$_GET['id']; } else { $idlink = ""; }

echo "<a href=\"index.php?page=javaload".$idlink."\" ";
echo "onclick=\"link_popup(this,500,380); return false\" ";
echo "title=\"Use ZUpload, a nice little Java Applet by David Zhao.\">";
echo "Java FTP Client</a>\n | ";
echo "<a href=\"ftp://" . $settings['ftp_user'] . ":";
echo $settings['ftp_pass']. "@" . $settings['ftp_server'];
echo $settings['ftp_path']; 
echo "\" title=\"Use your default FTP client. You can define this at the Internet Options of your OS.\">";
echo "Default FTP Client</a>\n";

echo "</div>\n</div>\n\n";

}

?>

<!--  SEARCH FTP FOLDER  -->

<div class="method">
<form method="post" action="index.php?page=record2&amp;do=ftp<?php
if ($update_id != false) echo "&amp;id=" . $update_id;
?>" enctype="multipart/form-data">
<h2>Search Upload Folder</h2>
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

<!--  WEB TRANSFER  -->

<div class="method">
<form method="post" action="index.php?page=record2&amp;do=web<?php
if ($update_id != false) echo "&amp;id=$update_id";
?>" enctype="multipart/form-data">
<h2>Get from Web</h2>
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


