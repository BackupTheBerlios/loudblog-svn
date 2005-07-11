<?php


// ----------------------------------------------------------------


function upload_browser($update_id) {

global $settings;
deleteupdatedfile ($update_id);

//checks the uploaded file
if ($_FILES['fileupload']['error'] != "0") { die("<p class=\"msg\">something is wrong, upload did not succeed.</p>"); }

//rename or not?? if not, kick the spaces!
if ($settings['rename'] == 1) { 
    $newfilename = 
        freshaudioname(strrchr($_FILES['fileupload']['name'], "."), $settings['filename']); 
} else { $newfilename = tunefilename($_FILES['fileupload']['name']); }

$newfilepath = $GLOBALS['audiopath'] . $newfilename; 

//put the uploaded file into the desired directory
move_uploaded_file($_FILES['fileupload']['tmp_name'], $newfilepath) 
OR die ("<p class=\"msg\">something is wrong, upload did not succeed.</p>");
//change the chmod
chmod ($newfilepath, 0777);

//make a valid temp-title
$temptitle = stripsuffix(htmlspecialchars($_FILES['fileupload']['name'], ENT_QUOTES));

//big question: are we just updating or creating a new file? 
if (!$update_id) {

//insert a new row to the database and fill it with some nice data
$dosql = "INSERT INTO {$GLOBALS['prefix']}lb_postings
         (author_id, title, posted, filelocal,  
         audio_file, audio_type, audio_size, status)
         VALUES
         (
         '{$_SESSION['authorid']}',
         '$temptitle',
         '" . date('Y-m-d H:i:s') . "',
         '1',
         '$newfilename',
         '" . type_mime ($_FILES['fileupload']['type']) . "',
         '" . $_FILES['fileupload']['size'] . "',
         '1'
         );";
$result = mysql_query($dosql) OR die (mysql_error());

//add default id3 tags, if needed
defaultid3tags(($GLOBALS['audiopath'].$newfilename), $temptitle);

echo "<p class=\"msg\">Successfully uploaded!</p>";

} else {

//update an existing row in the database
$dosql = "UPDATE {$GLOBALS['prefix']}lb_postings SET

         author_id = '{$_SESSION['authorid']}',
         filelocal = '1',  
         audio_file= '$newfilename',
         audio_type= '" . type_mime ($_FILES['fileupload']['type']) . "',
         audio_length= '',
         audio_size= '{$_FILES['fileupload']['size']}' 
         
         WHERE id = '$update_id';";
$result = mysql_query($dosql) OR die (mysql_error());

//add default id3 tags, if needed
defaultid3tags($GLOBALS['audiopath'].$newfilename, gettitlefromid($update_id));

echo "<p class=\"msg\">uploading was successful.</p>";

}

//get id for editing data after finishing this function
$dosql = "SELECT id FROM {$GLOBALS['prefix']}lb_postings WHERE audio_file='". $newfilename ."';";
$result = mysql_query($dosql) OR die (mysql_error());
$row = mysql_fetch_assoc($result);

return $row['id'];
}


// ----------------------------------------------------------------

function cgi_copy($update_id) {
//This function is heavily based on Christian Rotzoll's CGI Upload-Relais. Thanks for the kind support and those great video tutorials!

global $settings;

//where is the CGI-script located?
if ((isset($settings['cgi_local'])) AND ($settings['cgi_local'] == 1)) {
    $downloadscript = $GLOBALS['path']."/loudblog/modules/cgi-bin/download.cgi";
} else { $downloadscript = $settings['cgi_url']."/download.cgi"; }

//getting upload-id from cgi-script
if (isset($_GET['uploadid'])) {
     $uploadid = htmlspecialchars($_GET['uploadid']);
} else { die ("Sorry! Uploading was not successful!"); }

//check if file with this id exists on server
if ($wp = fopen($downloadscript."?request=vorhanden&id=".$uploadid,"r")) {
     $upload_existent = fread($wp,2048);
     fclose($wp);
     $upload_existent = trim($upload_existent);
} else { 
    die("Sorry! Uploading was not successful: "."$downloadscript?request=vorhanden&id=$uploadid".") cannot be executed.");
}

//get filename from cgi-script
if ($wp = fopen("$downloadscript?request=filename&id=$uploadid","r")) {
    $oldfilename = fread($wp,2048);
    fclose($wp);
    $oldfilename = trim($oldfilename);
} else { 
    die("Sorry! Uploading was not succesful: "."$downloadscript?request=vorhanden&id=$uploadid".") cannot be executed.");
}
    
//rename or not??
if ($settings['rename'] == 1) { 
    $newfilename = 
        freshaudioname(strrchr($oldfilename, "."), $settings['filename']); 
} else { $newfilename = tunefilename($oldfilename); }
$newfilepath = $GLOBALS['audiopath'] . $newfilename; 

//copy the file from cgi to ... whatever
$sourcefile = fopen ("$downloadscript?request=filedata&id=$uploadid", "rb") 
    OR die("<p class=\"msg\">Did not find url!</p>");
$destfile = fopen ($newfilepath, "wb");
$eof = false;
$filesize = 0;

//copies the file in fragments of 1024 bytes
do {
    $file = fread ($sourcefile, 1024) OR $eof = true;
    $filesize = $filesize + 1024;
    fwrite ($destfile, $file) OR fclose($destfile);
} while ($eof==false);
fclose($sourcefile);

//change the chmod
chmod ($newfilepath, 0777);

//make a valid temp-title
$temptitle = stripsuffix(htmlspecialchars($oldfilename, ENT_QUOTES));

//big question: are we just updating or creating a new file? 
if (!$update_id) {

//insert a new row to the database and fill it with some nice data
$dosql = "INSERT INTO {$GLOBALS['prefix']}lb_postings
         (author_id, title, posted, filelocal, 
         audio_file, audio_type, audio_size, status)
         VALUES
         (
         '{$_SESSION['authorid']}',
         '$temptitle',
         '" . date('Y-m-d H:i:s') . "',
         '1',
         '$newfilename',
         '" . type_suffix($newfilename) . "',
         '$filesize',
         '1'
         );";
$result = mysql_query($dosql) OR die (mysql_error());

//add default id3 tags, if needed
defaultid3tags(($GLOBALS['audiopath'].$newfilename), $temptitle);

//if the parser gets until here, all should be good
echo "<p class=\"msg\">$oldfilename successfully uploaded.</p>";

} else {

//update an existing row in the database
$dosql = "UPDATE {$GLOBALS['prefix']}lb_postings SET

         author_id = '{$_SESSION['authorid']}',
         filelocal = '1',  
         audio_file= '$newfilename',
         audio_type= '" . type_suffix($newfilename) . "',
         audio_length= '',
         audio_size= '$filesize'  
         
         WHERE id = '$update_id';";
$result = mysql_query($dosql) OR die (mysql_error());

//add default id3 tags, if needed
defaultid3tags(($GLOBALS['audiopath'].$newfilename), gettitlefromid($update_id));

}

//get id for editing data after finishing this function
$dosql = "SELECT id FROM {$GLOBALS['prefix']}lb_postings 
          WHERE audio_file='$newfilename';";
$result = mysql_query($dosql) OR die (mysql_error());
$row = mysql_fetch_assoc($result);

return $row['id'];
}


// ----------------------------------------------------------------

function link_web($update_id) {

global $settings;
deleteupdatedfile ($update_id);

$filetype = type_suffix(extractfilename($_POST['linkurl']));
$filename = stripsuffix(extractfilename($_POST['linkurl']));
$fileatts = remote_fileatts($_POST['linkurl']);


//big question: are we just updating or creating a new file? 
if (!$update_id) {

//insert a new row to the database and fill it with some nice data
$dosql = "INSERT INTO {$GLOBALS['prefix']}lb_postings
         (author_id, title, posted, filelocal, audio_size, audio_length, audio_type, audio_file, status)
         VALUES
         (
         '{$_SESSION['authorid']}',
         '$filename',
         '" . date('Y-m-d H:i:s') . "', '0',
         '".$fileatts['size']."', '".$fileatts['length']."',
         '$filetype',
         '{$_POST['linkurl'] }',
         '1'
         );";
$result = mysql_query($dosql) OR die (mysql_error());

//if the parser gets until here, all should be good
echo "<p class=\"msg\">Successfully linked. Fight content-stealing!</p>";

} else {

//update an existing row in the database
$dosql = "UPDATE {$GLOBALS['prefix']}lb_postings SET

         author_id = '{$_SESSION['authorid']}',
         filelocal = '0',  
         audio_file= '" . $_POST['linkurl'] . "',
         audio_size= '".$fileatts['size']."',
         audio_length= '".$fileatts['length']."',
         audio_type= '" . $filetype . "' 
         
         WHERE id = '" . $update_id . "';";
$result = mysql_query($dosql) OR die (mysql_error());

}


//get id for editing data after finishing this function
$dosql = "SELECT id FROM {$GLOBALS['prefix']}lb_postings 
          WHERE audio_file='". $_POST['linkurl'] ."';";
$result = mysql_query($dosql) OR die (mysql_error());
$row = mysql_fetch_assoc($result);

return $row['id'];
}

// ----------------------------------------------------------------

function fetch_web($update_id) {

global $settings;
deleteupdatedfile ($update_id);

//rename or not??
if ($settings['rename'] == 1) { 
    $newfilename = 
        freshaudioname(strrchr($_POST['linkurl'], "."), $settings['filename']);
} else { 
$newfilename = extractfilename($_POST['linkurl'], true);
$newfilename = tunefilename($newfilename);
}
$newfilepath = $GLOBALS['audiopath'] . $newfilename; 

//copy the file from the url to ... whatever
$sourcefile = fopen ($_POST['linkurl'], "rb") 
    OR die("<p class=\"msg\">Did not find url!</p>");
$destfile = fopen ($newfilepath, "wb");
$eof = false;
$filesize = 0;

//copies the file in fragments of 1024 bytes
do {
$file = fread ($sourcefile, 1024) OR $eof = true;
$filesize = $filesize + 1024;
fwrite ($destfile, $file) OR fclose($destfile);
} while ($eof==false);
fclose($sourcefile);

//change the chmod
chmod ($newfilepath, 0777);

//make a valid temp-title
$temptitle = stripsuffix(htmlspecialchars(extractfilename($_POST['linkurl'], false), ENT_QUOTES));

//big question: are we just updating or creating a new file? 
if (!$update_id) {

//insert a new row to the database and fill it with some nice data
$dosql = "INSERT INTO {$GLOBALS['prefix']}lb_postings
         (author_id, title, posted, filelocal, 
         audio_file, audio_type, audio_size, status)
         VALUES
         (
         '{$_SESSION['authorid']}',
         '$temptitle',
         '" . date('Y-m-d H:i:s') . "',
         '1',
         '$newfilename',
         '" . type_suffix($newfilename) . "',
         '$filesize',
         '1'
         );";
$result = mysql_query($dosql) OR die (mysql_error());

//add default id3 tags, if needed
defaultid3tags(($GLOBALS['audiopath'].$newfilename), $temptitle);

//if the parser gets until here, all should be good
echo "<p class=\"msg\">Successfully copied. Fight content-stealing!</p>";

} else {

//update an existing row in the database
$dosql = "UPDATE {$GLOBALS['prefix']}lb_postings SET

         author_id = '{$_SESSION['authorid']}',
         filelocal = '1',  
         audio_file= '$newfilename',
         audio_type= '" . type_suffix($newfilename) . "',
         audio_length= '',
         audio_size= '$filesize'  
         
         WHERE id = '$update_id';";
$result = mysql_query($dosql) OR die (mysql_error());

//add default id3 tags, if needed
defaultid3tags(($GLOBALS['audiopath'].$newfilename), gettitlefromid($update_id));

}

//get id for editing data after finishing this function
$dosql = "SELECT id FROM {$GLOBALS['prefix']}lb_postings 
          WHERE audio_file='$newfilename';";
$result = mysql_query($dosql) OR die (mysql_error());
$row = mysql_fetch_assoc($result);

return $row['id'];
}

// ----------------------------------------------------------------

function copy_ftp ($update_id) {

global $settings;
deleteupdatedfile ($update_id);

//no file in upload-folder? error!
if ($_POST['filename'] == "") { die("<p class=\"msg\">No file defined!</p>"); }
$oldfilename = $_POST['filename'];

//rename or not??
if ($settings['rename'] == 1) { 
    $newfilename = 
        freshaudioname(strrchr($_POST['filename'], "."), $settings['filename']);
} else { $newfilename = tunefilename(urldecode($oldfilename)); }

//copy the file and delete the old one
$oldpath = $GLOBALS['uploadpath'] . urldecode($oldfilename);
$newfilepath = $GLOBALS['audiopath'] . $newfilename; 
copy ($oldpath, $newfilepath);
unlink ($oldpath);

//change the chmod
chmod ($newfilepath, 0777);

$filesize = filesize ($newfilepath);

//make a valid temp-title
$temptitle = stripsuffix(htmlspecialchars(urldecode($oldfilename), ENT_QUOTES));

//big question: are we just updating or creating a new file? 
if (!$update_id) {

//insert a new row to the database and fill it with some nice data
$dosql = "INSERT INTO {$GLOBALS['prefix']}lb_postings
         (author_id, title, posted, 
         filelocal, audio_file, audio_type, audio_size, status)
         VALUES
         ('{$_SESSION['authorid']}', '$temptitle', '".date('Y-m-d H:i:s')."',
        '1', '$newfilename', '" . type_suffix($newfilename) . "', 
        '$filesize', '1');";
$result = mysql_query($dosql) OR die (mysql_error());

//add default id3 tags, if needed
defaultid3tags(($GLOBALS['audiopath'].$newfilename), $temptitle);

//if the parser gets until here, all should be good
echo "<p class=\"msg\">$newfilename - Copying was successful.</p>";

} else {

//update an existing row in the database
$dosql = "UPDATE {$GLOBALS['prefix']}lb_postings SET

         author_id = '{$_SESSION['authorid']}',
         filelocal = '1',  
         audio_file= '$newfilename',
         audio_type= '" . type_suffix($newfilename) . "',
         audio_length= '',
         audio_size= '$filesize'  
         WHERE id = '$update_id';";
$result = mysql_query($dosql) OR die (mysql_error());

//add default id3 tags, if needed
defaultid3tags(($GLOBALS['audiopath'].$newfilename), gettitlefromid($update_id));

}

//get id for editing data after finishing this function
$dosql = "SELECT id FROM {$GLOBALS['prefix']}lb_postings 
          WHERE audio_file='$newfilename';";
$result = mysql_query($dosql) OR die (mysql_error());
$row = mysql_fetch_assoc($result);



return $row['id'];

}

// ----------------------------------------------------------------

function nofile($update_id) {

global $settings;
deleteupdatedfile ($update_id);

$tempdate = date('Y-m-d H:i:s');

//big question: are we just updating or creating a new file? 
if (!$update_id) {

//insert a new row to the database and fill it with some nice data
$dosql = "INSERT INTO {$GLOBALS['prefix']}lb_postings
         (author_id, title, posted, filelocal, status)
         VALUES
         (
         '{$_SESSION['authorid']}',
         'New Posting',
         '" . date('Y-m-d H:i:s') . "',
         '0',
         '1'
         );";
$result = mysql_query($dosql) OR die (mysql_error());

//if the parser gets until here, all should be good
echo "<p class=\"msg\">Posting without audio was created successfully!</p>";

} else {

//update an existing row in the database
$dosql = "UPDATE {$GLOBALS['prefix']}lb_postings SET

         author_id = '{$_SESSION['authorid']}',
         filelocal = '0',  
         audio_file= '',
         audio_size= '',
         audio_length= '',
         audio_type= '0' 
         
         WHERE id = '$update_id';";
$result = mysql_query($dosql) OR die (mysql_error());

}


//get id for editing data after finishing this function
$dosql = "SELECT id FROM {$GLOBALS['prefix']}lb_postings 
          WHERE posted='". $tempdate ."';";
$result = mysql_query($dosql) OR die (mysql_error());
$row = mysql_fetch_assoc($result);
return $row['id'];
}

// ----------------------------------------------------------------

function deleteupdatedfile ($id) {
//deletes the old file when it is to be updated

global $settings;
if ($id != false) {
$dosql = "SELECT audio_file, filelocal FROM {$GLOBALS['prefix']}lb_postings 
          WHERE id='$id';";
$result = mysql_query($dosql) OR die (mysql_error());
$row = mysql_fetch_assoc($result);

$filepath = $GLOBALS['audiopath'] . $row['audio_file'];
if ($row['filelocal'] == 1) unlink($filepath);
}
}



// ----------------------------------------------------------------

function defaultid3tags($filename, $title) {
//takes the uploaded file and changes the id3 tagging

global $settings;

if ($settings['id3_overwrite'] == 1) {

    require_once('inc/id3/getid3.php');
    $getID3 = new getID3;
    $getID3->encoding = 'UTF-8'; 
    
    require_once('inc/id3/write.php');
    $tagwriter = new getid3_writetags;
    $tagwriter->filename   = $filename;
    $tagwriter->tagformats = array('id3v2.3', 'ape');
    $tagwriter->overwrite_tags = true; 
    $tagwriter->remove_other_tags = true; 

    $TagData['title'][]  = $title;
    $TagData['artist'][]  = $settings['id3_artist'];
    $TagData['album'][]  = $settings['id3_album'];
    $TagData['genre'][]  = $settings['id3_genre'];
    $TagData['year'][]  = date('Y');
    $TagData['comment'][]  = $settings['id3_comment'];
    $tagwriter->tag_data = $TagData;
    if ($tagwriter->WriteTags()) {
    } else {
	   echo "<p class=\"msg\">Changing ID3 tags failed. Sorry.</p>";
    }
}

}



?>