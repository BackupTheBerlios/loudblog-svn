<?php
echo "<h1>Settings</h1>\n";

include ('inc/navigation.php');

//check the rights
if (!allowed(3,"")) 
{ die("<p class=\"msg\">Administrators do some wild party in here. You are not invited :-(</p>"); }

//put the posted data into the databse
if ((isset($_GET['do'])) AND ($_GET['do'] == "save")) {

//patented loopsave of post-data, wow!
foreach ($_POST as $setname => $setvalue) {

$setvalue = htmlentities($setvalue, ENT_QUOTES, "UTF-8");

//write things from post-data into database
$dosql = "UPDATE ".$GLOBALS['prefix']."lb_settings SET    
          value = '" . $setvalue . "' 
          WHERE name = '" . $setname . "';";
$result = mysql_query($dosql) OR die (mysql_error());
}
}

//now begin with showing forms!!

$settings = getsettings ();

?>

<form action="index.php?page=settings&amp;do=save" 
    method="post" enctype="multipart/form-data">

<table>

<tr><th colspan="3">Website Meta Information</th></tr>

<tr>
    <td class="left">Official name:</td>
    <td class="center">
    <input name="sitename" type="text"
    value="<?php echo $settings['sitename']; ?>" />
    </td>
    <td class="right">
    Example: "Mike's Podcast"
    </td>
</tr>

<tr>
    <td class="left">Slogan/Subline:</td>
    <td class="center">
    <input name="slogan" type="text"
    value="<?php echo $settings['slogan']; ?>" />
    </td>
    <td class="right">
    Example: "Urban Stories from Delaware"
    </td>
</tr>

<tr>
    <td class="left">Short description</td>
    <td class="center">
    <textarea name="description" rows="4"><?php echo $settings['description']; ?></textarea>
    </td>
    <td class="right">
    Max. 256 characters
    </td>
</tr>

<tr>
    <td class="left">URL of your site</td>
    <td class="center">
    <input name="url" type="text"
    value="<?php echo $settings['url']; ?>" />
    </td>
    <td class="right">
    Without trailing slash. Example:<br />
    <code>http://www.mikeswebsite.com/podcast</code>
    </td>
</tr>

<tr><th colspan="3">File settings</th></tr>

<tr>
    <td class="left">Auto-rename audiofiles?</td>
    <td class="center">
    <?php $temp = array ("", ""); 
    $temp[$settings['rename']] = "checked=\"checked\""; ?>
    <input class="radio" name="rename" type="radio" value="1" <?php echo $temp[1]; ?>/>yes
    <input class="radio" name="rename" type="radio" value="0" <?php echo $temp[0]; ?>/>no
    </td>
    <td class="right">
    After copied into the system, Loudblog can rename your audio files, using a consistent pattern. Do you want that?
    </td>
</tr>

<tr>
    <td class="left">Custom part of filename:</td>
    <td class="center">
    <input name="filename" type="text"
    value="<?php echo $settings['filename']; ?>" />
    </td>
    <td class="right">
    Using auto-rename, you can assign the first part of all new filenames.
    </td>
</tr>

<tr>
    <td class="left">Filenames will look like:</td>
    <td class="center">
    <code><?php echo $settings['filename']; ?>-2005-05-27-51816.mp3 
    </code>
    </td>
    <td class="right">
    Example of an auto-renamed file: After the custom part there is the date of copying, followed by a unique 5-digits-id.
    </td>
</tr>

<!--

<tr>
    <td class="left">Overwrite id3-tags of mp3-files?</td>
    <td class="center">
    <?php $temp = array ("", ""); 
    $temp[$settings['id3_overwrite']] = "checked=\"checked\""; ?>
    <input class="radio" name="id3_overwrite" type="radio" value="1" 
        <?php echo $temp[1]; ?>/>yes
    <input class="radio" name="id3_overwrite" type="radio" value="0" 
        <?php echo $temp[0]; ?>/>no
    </td>
</tr>

<tr>
    <td class="subset">(id3) Default album:</td>
    <td class="center">
    <input name="id3_album" type="text"
    value="<?php echo $settings['id3_album']; ?>" />
    </td>
</tr>

<tr>
    <td class="subset">(id3) Default artist:</td>
    <td class="center">
    <input name="id3_artist" type="text"
    value="<?php echo $settings['id3_artist']; ?>" />
    </td>
</tr>

<tr>
    <td class="subset">(id3) Default genre:</td>
    <td class="center">
    <input name="id3_genre" type="text"
    value="<?php echo $settings['id3_genre']; ?>" />
    </td>
</tr>

<tr>
    <td class="subset">(id3) Default comment:</td>
    <td class="center">
    <textarea name="id3_comment" rows="4"><?php echo $settings['id3_comment']; ?></textarea>
    </td>
</tr>

-->




<tr><th colspan="3">Various settings</th></tr>

<tr>
    <td class="left">Preferred HTML-helper:</td>
    <td class="center">
    <?php $temp = array ("", "", "", ""); 
    $temp[$settings['markuphelp']] = " checked=\"checked\""; ?>
    
    <input class="radio" name="markuphelp" type="radio" value="1" <?php echo $temp[1]; ?>/>Textile (<a href="http://www.textism.com/tools/textile/index.html">info</a>)<br />
    <input class="radio" name="markuphelp" type="radio" value="2" <?php echo $temp[2]; ?>/>Markdown (<a href="http://daringfireball.net/projects/markdown/">info</a>)<br />
    <input class="radio" name="markuphelp" type="radio" value="3" <?php echo $temp[3]; ?>/>BBCode (<a href="http://www.phpbb.com/phpBB/faq.php?mode=bbcode">info</a>)<br />
    <input class="radio" name="markuphelp" type="radio" value="0" <?php echo $temp[0]; ?>/>None
    </td>
    <td class="right">
    With these you can easily create structured html-code using some simple rules. It is useful for your text messages as well as for the user comments.
    </td>
</tr>

<tr>
    <td class="left">Template for website:</td>
    <td class="center">
    <select name="template">
    <?php 
    //gets the filenames of all the files in the upload-folder. make a list.
$templatefolder = opendir('custom/templates'); 

while ($file = readdir($templatefolder)) { 
    if ( substr($file, 0, 1) != ".") { 
        $choosefile = $file; 
        echo "<option value=\"" . $choosefile . "\"";
        if ($choosefile == $settings['template']) { 
            echo " selected=\"selected\"";
        }
        echo "\">" . $choosefile . "</option>";
    }
}
closedir($templatefolder);
    
 ?>   
    
    </select>
    </td>
    <td class="right">
    The list shows all Templates located in the <code>loudblog/custom/templates/</code>-folder. Simply choose the one you like most!
    </td>
</tr>

<tr>
    <td class="left">Hyperlinks per posting:</td>
    <td class="center">
    <input name="showlinks" type="text"
    value="<?php echo $settings['showlinks']; ?>" />
    </td>
    <td class="right">
    You can assign the maximum number of hyperlinks that are displayed for each posting. Technically, there is no limit.
    </td>
</tr>

<tr>
    <td class="left">Date format:</td>
    <td class="center">
    <input name="dateformat" type="text"
    value="<?php echo $settings['dateformat']; ?>" />
    </td>
    <td class="right">
    You can define the structure of date/time-information very flexibly using the PHP-Syntax. <a href="http://www.w3schools.com/php/php_date.asp">Click here for help!</a>
    </td>
</tr>

<tr>
    <td class="left">Items in RSS-Feed:</td>
    <td class="center">
    <input name="rss_postings" type="text"
    value="<?php echo $settings['rss_postings']; ?>" />
    </td>
    <td class="right">
    Assign how many recent postings will appear in your RSS/Podcasting-Feed.
    </td>
</tr>

<tr id="last">
    <th class="left"></th>
    <th colspan="2">
    <input type="submit" value="save all" />
    </th>
</tr>

</table>



</form>