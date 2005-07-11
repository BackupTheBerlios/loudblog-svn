<?php
echo "<h1>Settings</h1>\n";

include ('inc/navigation.php');

//check the rights
if (!allowed(3,"")) 
{ die("<p class=\"msg\">Administrators do some wild party in here. You are not invited :-(</p>"); }


//put the posted data into the databse
if ((isset($_GET['do'])) AND ($_GET['do'] == "save")) {


if ($_FILES['itunes_image']['error'] == "0") { 
    $newfilename = $GLOBALS['audiopath'] . "itunescover.jpg";
    move_uploaded_file($_FILES['itunes_image']['tmp_name'], $newfilename) 
        OR die ("<p class=\"msg\">something is wrong, upload did not succeed.</p>");
    chmod ($newfilename, 0777);
}

if ($_FILES['feedimage']['error'] == "0") { 
    $newfilename = $GLOBALS['audiopath'] . "rssimage.jpg";
    move_uploaded_file($_FILES['feedimage']['tmp_name'], $newfilename) 
        OR die ("<p class=\"msg\">something is wrong, upload did not succeed.</p>");
    chmod ($newfilename, 0777);
}



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
init();

?>

<form action="index.php?page=settings&amp;do=save" 
    method="post" enctype="multipart/form-data">


<!--  ++++++++++++++++++++++++++++++++++  -->

<h2>Website Meta Information</h2>
<table>
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
    <td class="left">Short description:</td>
    <td class="center">
    <textarea name="description" rows="4"><?php echo $settings['description']; ?></textarea>
    </td>
    <td class="right">
    Max. 256 characters
    </td>
</tr>

<tr>
    <td class="left">URL of your site:</td>
    <td class="center">
    <input name="url" type="text"
    value="<?php echo $settings['url']; ?>" />
    </td>
    <td class="right">
    Without trailing slash. Example:<br />
    <code>http://www.mikeswebsite.com/mikesnetradio</code>
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

</table>

<!--  ++++++++++++++++++++++++++++++++++  -->

<h2>Podcast Feed Information</h2>
<table>
<tr>
    <td class="left">Author:</td>
    <td class="center">
    <input name="itunes_author" type="text"
    value="<?php echo $settings['itunes_author']; ?>" />
    </td>
    <td class="right">
    Example: "Homer J. Simpson"
    </td>
</tr>

<tr>
    <td class="left">Author's eMail:</td>
    <td class="center">
    <input name="itunes_email" type="text"
    value="<?php echo $settings['itunes_email']; ?>" />
    </td>
    <td class="right">
    Example: "homersimpson@fox.com"
    </td>
</tr>

<tr>
    <td class="left">Copyright:</td>
    <td class="center">
    <input name="copyright" type="text"
    value="<?php echo $settings['copyright']; ?>" />
    </td>
    <td class="right">
    Some words on copyright and/or legal stuff. Maybe you want to use a Creative Commons License?
    </td>
</tr>

<tr>
    <td class="left">Number of items:</td>
    <td class="center">
    <input name="rss_postings" type="text"
    value="<?php echo $settings['rss_postings']; ?>" />
    </td>
    <td class="right">
    Assign how many recent postings will appear in your RSS/Podcasting-Feed.
    </td>
</tr>

<tr>
    <td class="left">iTunes Cover Art:</td>
    <td class="center">
    <input class="fileupper" type="file" name="itunes_image" accept="image/*" />
    </td>
    <td class="right">
    <a href="../audio/itunescover.jpg"><img class="coverart" src="../audio/itunescover.jpg" /></a>This is for the iTunes Podcasting directory. Upload a square image as JPG. 300x300 pixels is the best size.
    </td>
</tr>

<tr>
    <td class="left">RSS Image:</td>
    <td class="center">
    <input class="fileupper" type="file" name="feedimage" accept="image/*" />
    </td>
    <td class="right">
    <a href="../audio/rssimage.jpg"><img class="rssimage" src="../audio/rssimage.jpg" /></a>Please use a JPG image. Max size: 144x400 pixels, default size: 88x31 pixels.
    </td>
</tr>

<tr>
    <td class="left">Do you publish explicit material?</td>
    <td class="center">
    <?php $temp = array ("", ""); 
    $temp[$settings['itunes_explicit']] = " checked=\"checked\""; ?>
    <input class="radio" name="itunes_explicit" type="radio" value="1"<?php echo $temp[1]; ?> />yes&nbsp;&nbsp;
    <input class="radio" name="itunes_explicit" type="radio" value="0"<?php echo $temp[0]; ?> />no
    </td>
    <td class="right">
    If you say "yes", a parental advisory will appear next to the cover art at the iTunes directory. 
    </td>
</tr>

<tr>
    <td class="left">iTunes Categories:</td>
    <td class="center">
    
    <?php 
    
        echo "<select class=\"itcats\" name=\"feedcat1\">";
        foreach ($itunescats as $long => $short) {
            echo "<option value=\"".$short."\"";
            if ($settings['feedcat1'] == $short) {
                echo "selected=\"selected\"";
            }
            echo ">".$long."</option>\n";
        }
        echo "</select>";
        
        echo "<select class=\"itcats\" name=\"feedcat2\">";
        foreach ($itunescats as $long => $short) {
            echo "<option value=\"".$short."\"";
            if ($settings['feedcat2'] == $short) {
                echo "selected=\"selected\"";
            }
            echo ">".$long."</option>\n";
        }
        echo "</select>";
        
        echo "<select class=\"itcats\" name=\"feedcat3\">";
        foreach ($itunescats as $long => $short) {
            echo "<option value=\"".$short."\"";
            if ($settings['feedcat3'] == $short) {
                echo "selected=\"selected\"";
            }
            echo ">".$long."</option>\n";
        }
        echo "</select>";
        
        echo "<select class=\"itcats\" name=\"feedcat4\">";
        foreach ($itunescats as $long => $short) {
            echo "<option value=\"".$short."\"";
            if ($settings['feedcat4'] == $short) {
                echo "selected=\"selected\"";
            }
            echo ">".$long."</option>\n";
        }
        echo "</select>";
        
        ?>
    </td>
    <td class="right">
    Choose up to four categories that might be suitable for your Podcast. These are the official iTunes categories, by the way. 
    </td>
</tr>



</table>

<!--  ++++++++++++++++++++++++++++++++++  -->

<h2>Various Settings</h2>
<table>
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
    <td class="left">Language/Location:</td>
    <td class="center">
        <select name="languagecode">
        
        <?php 
            
        foreach ($langs as $long => $short) {
        echo "<option value=\"".$short."\"";
        
        if ($settings['languagecode'] == $short) {
            echo "selected=\"selected\"";
        }
        
        echo ">".$long."</option>\n";
        }
        
        ?>
        </select>    
    </td>
    <td class="right">
    This is for identifying the language you are writing/speaking on this Podcast.
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



</table>


<!--  ++++++++++++++++++++++++++++++++++  -->

<h2>Filename Settings</h2>
<table>
<tr>
    <td class="left">Auto-rename audiofiles?</td>
    <td class="center">
    <?php $temp = array ("", ""); 
    $temp[$settings['rename']] = "checked=\"checked\""; ?>
    <input class="radio" name="rename" type="radio" value="1" <?php echo $temp[1]; ?>/>yes&nbsp;&nbsp;
    <input class="radio" name="rename" type="radio" value="0" <?php echo $temp[0]; ?>/>no
    </td>
    <td class="right">
    After copied into the system, Loudblog can rename your audio files, using a consistent pattern. This option is recommended.
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
</table>

<!--  ++++++++++++++++++++++++++++++++++  -->

<h2>ID3 Tag Settings</h2>
<table>
<tr>
    <td class="left">Overwrite ID3 tags?</td>
    <td class="center">
    <?php $temp = array ("", ""); 
    $temp[$settings['id3_overwrite']] = "checked=\"checked\""; ?>
    <input class="radio" name="id3_overwrite" type="radio" value="1" 
        <?php echo $temp[1]; ?>/>yes&nbsp;&nbsp;
    <input class="radio" name="id3_overwrite" type="radio" value="0" 
        <?php echo $temp[0]; ?>/>no
    </td>
    <td class="right">
    Loudblog can write default ID3 tags into every incoming MP3 file. This option is recommended.
    </td>
</tr>

<tr>
    <td class="subset">Default album:</td>
    <td class="center">
    <input name="id3_album" type="text"
    value="<?php echo $settings['id3_album']; ?>" />
    </td>
    <td class="right">
    The default "album" tag. This should be the name of your podcast/audioblog.
    </td>
</tr>

<tr>
    <td class="subset">Default artist:</td>
    <td class="center">
    <input name="id3_artist" type="text"
    value="<?php echo $settings['id3_artist']; ?>" />
    </td>
    <td class="right">
    The default "artist" tag. This can be your name.
    </td>
</tr>

<tr>
    <td class="subset">Default genre:</td>
    <td class="center">
    <input name="id3_genre" type="text"
    value="<?php echo $settings['id3_genre']; ?>" />
    </td>
    <td class="right">
    The default "genre" tag. This can be "Podcast", "Vocal", or the music genre of your choice.
    </td>
</tr>

<tr>
    <td class="subset">Default comment:</td>
    <td class="center">
    <textarea name="id3_comment" rows="4"><?php echo $settings['id3_comment']; ?></textarea>
    </td>
    <td class="right">
    The default "comment" tag. Write up to 256 characters. You should include the URL of your website and/or your Podcast feed here.
    </td>
</tr>
</table>

<!--  ++++++++++++++++++++++++++++++++++  -->

<h2>FTP Upload Settings</h2>
<table>
<tr>
    <td class="left">Use FTP for uploading?</td>
    <td class="center">
    <?php $temp = array ("", ""); 
    $temp[$settings['ftp']] = "checked=\"checked\""; ?>
    <input class="radio" name="ftp" type="radio" value="1" <?php echo $temp[1]; ?>/>yes&nbsp;&nbsp;
    <input class="radio" name="ftp" type="radio" value="0" <?php echo $temp[0]; ?>/>no
    </td>
    <td class="right">
    You can upload your audio files using a simple Java applet or your preferred FTP-Client.
    </td>
</tr>

<tr>
    <td class="subset">FTP server:</td>
    <td class="center">
    <input name="ftp_server" type="text"
    value="<?php echo $settings['ftp_server']; ?>" />
    </td>
    <td class="right">
    The server of your FTP account, without "ftp://". <br />Example: <code>ftp.mikeswebsite.com</code>
    </td>
</tr>

<tr>
    <td class="subset">FTP username:</td>
    <td class="center">
    <input name="ftp_user" type="text"
    value="<?php echo $settings['ftp_user']; ?>" />
    </td>
    <td class="right">
    The username of your FTP account
    </td>
</tr>

<tr>
    <td class="subset">FTP password:</td>
    <td class="center">
    <input name="ftp_pass" type="password"
    value="<?php echo $settings['ftp_pass']; ?>" />
    </td>
    <td class="right">
    The matching password of your FTP account
    </td>
</tr>

<tr>
    <td class="subset">FTP path:</td>
    <td class="center">
    <input name="ftp_path" type="text"
    value="<?php echo $settings['ftp_path']; ?>" />
    </td>
    <td class="right">
    Full path to the "upload"-folder. Without trailing slash. Example:
    <br />/www/56784/mypodcast/upload
    </td>
</tr>
</table>


<!--  ++++++++++++++++++++++++++++++++++  -->

<h2>CGI/Perl Upload Settings</h2>
<table>
<tr>
    <td class="left">Use CGI for uploading?</td>
    <td class="center">
    <?php $temp = array ("", ""); 
    $temp[$settings['cgi']] = "checked=\"checked\""; ?>
    <input class="radio" name="cgi" type="radio" value="1" <?php echo $temp[1]; ?>/>yes&nbsp;&nbsp;
    <input class="radio" name="cgi" type="radio" value="0" <?php echo $temp[0]; ?>/>no
    </td>
    <td class="right">
    Every PHP server has a size limit for browser uploads. Yours is <?php echo getmegabyte(uploadlimit()); ?> MB. Use CGI/Perl to exceed this limitation.</td>
</tr>

<tr>
    <td class="left">CGI script location:</td>
    <td class="center">
    <?php $temp = array ("", ""); 
    $temp[$settings['cgi_local']] = "checked=\"checked\""; ?>
    <input class="radio" name="cgi_local" type="radio" value="1" <?php echo $temp[1]; ?>/>on this server&nbsp;&nbsp;
    <input class="radio" name="cgi_local" type="radio" value="0" <?php echo $temp[0]; ?>/>on a remote server
    </td>
    <td class="right">
    If your server executes cgi scripts, you should make use of it. Otherwise you have to install those scripts on a remote location.
    </td>
</tr>

<tr>
    <td class="subset">Remote location:</td>
    <td class="center">
    <input name="cgi_url" type="text"
    value="<?php echo $settings['cgi_url']; ?>" />
    </td>
    <td class="right">
    If your server does not execute CGI/Perl, you can assign a remote location for the upload script; without trailing slash. Example:<br /><code>http://mikesgeekfriend.com/cgi-bin</code>
    </td>
</tr>
</table>

<div class="savebutton">
    <input type="submit" value="save all settings" />
</div>



</form>


<?php

function init() {
global $itunescats;
$itunescats = array(
            "----"=>"00-00",
            "Arts &amp; Entertainment"=>"01-00",
            "-- Architecture"=>"01-01",
            "-- Books"=>"01-02",
            "-- Design"=>"01-03",
            "-- Entertainment"=>"01-04",
            "-- Games"=>"01-05",
            "-- Performing Arts"=>"01-06",
            "-- Photography"=>"01-07",
            "-- Poetry"=>"01-08",
            "-- Science Fiction"=>"01-09",
            "Audio Blogs"=>"02-00",
            "Business"=>"03-00",
            "-- Careers"=>"03-01",
            "-- Finance"=>"03-02",
            "-- Investing"=>"03-03",
            "-- Management"=>"03-04",
            "-- Marketing"=>"03-05",
            "Comedy"=>"04-00",
            "Education"=>"05-00",
            "-- K-12"=>"05-01",
            "-- Higher Education"=>"05-02",
            "Food"=>"06-00",
            "Health"=>"07-00",
            "-- Diet &amp; Nutrition"=>"07-01",
            "-- Fitness"=>"07-02",
            "-- Relationships"=>"07-03",
            "-- Self-Help"=>"07-04",
            "-- Sexuality"=>"07-05",
            "International"=>"08-00",
            "-- Australian"=>"08-01",
            "-- Belgian"=>"08-02",
            "-- Brazilian"=>"08-03",
            "-- Canadian"=>"08-04",
            "-- Chinese"=>"08-05",
            "-- Dutch"=>"08-06",
            "-- French"=>"08-07",
            "-- German"=>"08-08",
            "-- Hebrew"=>"08-09",
            "-- Italian"=>"08-10",
            "-- Japanese"=>"08-11",
            "-- Norwegian"=>"08-12",
            "-- Polish"=>"08-13",
            "-- Portuguese"=>"08-14",
            "-- Spanish"=>"08-15",
            "-- Swedish"=>"08-16",
            "Movies &amp; Television"=>"09-00",
            "Music"=>"10-00",
            "News"=>"11-00",
            "Politics"=>"12-00",
            "Public Radio"=>"13-00",
            "Religion &amp; Spirituality"=>"14-00",
            "-- Buddhism"=>"14-01",
            "-- Christianity"=>"14-02",
            "-- Islam"=>"14-03",
            "-- Judaism"=>"14-04",
            "-- New Age"=>"14-05",
            "-- Philosophy"=>"14-06",
            "-- Spirituality"=>"14-07",
            "Science"=>"15-00",
            "Sports"=>"16-00",
            "Talk Radio"=>"17-00",
            "Technology"=>"18-00",
            "-- Computers"=>"18-01",
            "-- Developers"=>"18-02",
            "-- Gadgets"=>"18-03",
            "-- Information Technology"=>"18-04",
            "-- News"=>"18-05",
            "-- Operating Systems"=>"18-06",
            "-- Podcasting"=>"18-07",
            "-- Smart Phones"=>"18-08",
            "-- Text/Speech"=>"18-09",
            "Travel"=>"19-00",
            );
            
            global $langs;
            $langs = array(
            "----"=>"en-us",
"Albanian" => "sq", 
"Arabic - Egypt" => "ar-eg", 
"Arabic - Iraq" => "ar-iq", 
"Arabic - Tunisia" => "ar-tn", 
"Armenian" => "hy", 
"Bulgarian" => "bg", 
"Chinese - China" => "zh-cn", 
"Croatian" => "hr", 
"Czech" => "cs", 
"Danish" => "da", 
"Dutch - The Netherlands" => "nl-nl", 
"English - Australia" => "en-au", 
"English - Canada" => "en-ca", 
"English - Ireland" => "en-ie", 
"English - South Africa" => "en-za", 
"English - United Kingdom" => "en-gb", 
"English - United States" => "en-us", 
"Estonian" => "et", 
"Finnish" => "fi", 
"French - Belgium" => "fr-be", 
"French - Canada" => "fr-ca", 
"French - France" => "fr-fr", 
"FYRO Macedonian" => "mk", 
"German - Austria" => "de-at", 
"German - Germany" => "de-de", 
"German - Switzerland" => "de-ch", 
"Greek" => "el", 
"Hebrew" => "he", 
"Hungarian" => "hu", 
"Icelandic" => "is", 
"Indonesian" => "id", 
"Italian - Italy" => "it-it", 
"Japanese" => "ja", 
"Korean" => "ko", 
"Latvian" => "lv", 
"Norwegian" => "no-no", 
"Polish" => "pl", 
"Portuguese - Brazil" => "pt-br", 
"Portuguese - Portugal" => "pt-pt", 
"Romanian - Romania" => "ro", 
"Russian" => "ru", 
"Serbian" => "sr-sp", 
"Slovak" => "sk", 
"Slovenian" => "sl", 
"Sorbian" => "sb", 
"Spanish - Argentina" => "es-ar", 
"Spanish - Chile" => "es-cl", 
"Spanish - Colombia" => "es-co", 
"Spanish - Mexico" => "es-mx", 
"Spanish - Spain" => "es-es", 
"Swedish - Sweden" => "sv-se", 
"Turkish" => "tr", 
"Ukrainian" => "uk", 
            );
            

}


?>

