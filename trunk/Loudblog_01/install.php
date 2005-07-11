<?php 

// ----------------------------------------------------- //
// Loudblog                                              //
// easy-to-use audioblogging and podcasting              //
// Version 0.1 (2005-04-11)                              // 
// http://loudblog.com                                   //
//                                                       //
// Written by Gerrit van Aaken (gerrit@praegnanz.de)     //
//                                                       //
// Published under a Creative Commons License            //
// http://creativecommons.org/licenses/by-nc-sa/2.0      //
//                                                       //
// Have Fun! Drop me a line if you like Loudblog!        //
// ----------------------------------------------------- //


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="content-language" content="en" />
    <title>Loudblog Install</title>
    <meta name="robots" content="noindex, nofollow" />
    <meta name="description" content="Loudblog" />
    <meta name="author" content="Gerrit van Aaken" />
    
    <link rel="stylesheet" type="text/css" href="loudblog/backend/screen.css" />

    <style type="text/css">
    <!--
        table { width: 740px; }

        td {
        vertical-align: top;
        padding: 5px 15px 5px 0;
        }

        th { padding-top: 30px; }

        input[type=text],
        input[type=password], 
        select
        { width: 280px; }

        textarea {
        font: normal 10px/1.2 monaco, courier, "Courier New", fixed;
        margin: 15px 0;
        width: 400px;
        height: 200px;
        }

        td.right {
        color: white;
        font-size: 0.9em;
        width: 270px;
        }

        td.left { width: 140px; }

        #last th { padding: 20px 0 10px 0; }
        #last input { width: 90px; }

        td.right a:link, 
        td.right a:visited { color: white; } 
        
        ul, li {
        list-style-type: square;
        }
        
        li {
        margin: 3px 0 0 20px;
        }


    -->
    </style>

</head>

<body>

<div id="wrapper">

<?php

if (!isset($_GET['do'])) { step1(); }
if ((isset($_GET['do'])) AND ($_GET['do'] == "2")) { step2(); }
if ((isset($_GET['do'])) AND ($_GET['do'] == "3")) { step3(); }

?>

</div>

<div id="footer">
    <p>Loudblog 0.1 by Gerrit van Aaken</p>
</div>

</body>
</html>


<?php

// ------------------------------------------------------------------------

function step1() {

echo "<h1>Installation [1/3]</h1>\n\n";

echo "<form action=\"install.php?do=2\" 
        method=\"post\" enctype=\"multipart/form-data\">";

echo "<table>\n";
echo "<tr><th colspan=\"3\">Create your first login</th><tr>\n\n";

echo "<tr>\n";
echo "    <td class=\"left\">Nickname</td>\n";
echo "    <td class=\"center\">\n";
echo "    <input name=\"nick\" type=\"text\" value=\"\" />\n";
echo "    </td>\n";
echo "    <td class=\"right\">\n";
echo "    Note that this is cASe-SenSiTIv!\n";
echo "    </td>\n";
echo "</tr>\n\n";

echo "<tr>\n";
echo "    <td class=\"left\">Password</td>\n";
echo "    <td class=\"center\">\n";
echo "    <input name=\"pass\" type=\"password\" value=\"\" />\n";
echo "    </td>\n";
echo "    <td class=\"right\">";
echo "    You can add or change all details later.\n";
echo "    </td>\n";
echo "</tr>\n\n";

echo "<tr><th colspan=\"3\">Details of your MySQL-Account</th><tr>\n\n";

echo "<tr>\n";
echo "    <td class=\"left\">Host</td>\n";
echo "    <td class=\"center\">\n";
echo "    <input name=\"sqlhost\" type=\"text\" value=\"localhost\" />\n";
echo "    </td>\n";
echo "    <td class=\"right\">\n";
echo "    In most cases this is simply <code>localhost.</code>\n";
echo "    </td>\n";
echo "</tr>\n\n";

echo "<tr>\n";
echo "    <td class=\"left\">Database</td>\n";
echo "    <td class=\"center\">\n";
echo "    <input name=\"sqldata\" type=\"text\" value=\"\" />\n";
echo "    </td>\n";
echo "    <td class=\"right\">\n";
echo "    The name of your MySQL database.\n";
echo "    </td>\n";
echo "</tr>\n\n";

echo "<tr>\n";
echo "    <td class=\"left\">Username</td>\n";
echo "    <td class=\"center\">\n";
echo "    <input name=\"sqluser\" type=\"text\" value=\"\" />\n";
echo "    </td>\n";
echo "    <td class=\"right\">\n";
echo "    Your MySQL username for the database above.\n";
echo "    </td>\n";
echo "</tr>\n\n";

echo "<tr>\n";
echo "    <td class=\"left\">Password</td>\n";
echo "    <td class=\"center\">\n";
echo "    <input name=\"sqlpass\" type=\"password\" value=\"\" />\n";
echo "    </td>\n";
echo "    <td class=\"right\">\n";
echo "    The appropiate MySQL password.\n";
echo "    </td>\n";
echo "</tr>\n\n";

echo "<tr>\n";
echo "    <td class=\"left\">Table-Prefix</td>\n";
echo "    <td class=\"center\">\n";
echo "    <input name=\"sqlprefix\" type=\"text\" value=\"\" />\n";
echo "    </td>\n";
echo "    <td class=\"right\">\n";
echo "    If you wish to have more than one Loudblog-installation on your database, you have to give each of them an unique prefix.\n";
echo "    </td>\n";
echo "</tr>\n\n";

echo "<tr><th colspan=\"3\">One more thing ...</th><tr>\n\n";

echo "<tr>\n";
echo "    <td class=\"left\">Website-URL</td>\n";
echo "    <td class=\"center\">\n";
echo "    <input name=\"siteurl\" type=\"text\" value=\"http://\" />\n";
echo "    </td>\n";
echo "    <td class=\"right\">\n";
echo "    Address of your Loudblog-powered audioblog or podcast. Without trailing slash! Example:<br /><code>http://www.mikeswebsite.com/podcast</code>";
echo "    </td>\n";
echo "</tr>\n\n";

echo "<tr id=\"last\">\n";
echo "    <th class=\"left\"></th>\n";
echo "    <th colspan=\"2\">\n";
echo "    <input type=\"submit\" value=\"go to step 2\" />\n";
echo "    </th>\n";
echo "</tr>\n\n";

echo "</table>\n";
echo "</form>";

}

// ------------------------------------------------------------------------

function step2 () {

echo "<h1>Installation [2/3]</h1>\n\n";

if (create() != false) {

echo "<h2>That was cool!</h2>";
echo "<p>All necessary data was written into your MySQL-database.</p>";
echo "<h2>Next up</h2>";
echo "<p>You have to do some copy-and-pasting now. Don't panic!</p>";
echo "<ul><li>Copy everything from the text-field below into the clipboard.</li>";
echo "<li>Paste it into a text-file called \"config.php\".</li>";
echo "<li>This file can be found in the subfolder \"custom\" which is located in the folder \"loudblog\".</li>";
echo "<li>Save this file.</li></ul>";

echo "<textarea>";
echo '<?php
// YOUR MYSQL DATA --------------------
$lb_host = "' . $_POST['sqlhost'] . '";
$lb_data = "' . $_POST['sqldata'] . '";
$lb_user = "' . $_POST['sqluser'] . '";
$lb_pass = "' . $_POST['sqlpass'] . '";
$lb_pref = "' . $_POST['sqlprefix'] . '";

// DOCUMENT ROOT ---------------------
$lb_path = "' . getcwd() . '";
?>';

echo "</textarea>";

echo "<form action=\"install.php?do=3\" 
        method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"submit\" value=\"I've done that!\" />\n";
echo "</form>";

}

}

// ------------------------------------------------------------------------

function step3 () {

echo "<h1>Installation [3/3]</h1>\n\n";

echo "<h2>Setting rights</h2>";
echo "<p>Finally you must make sure that the folders \"audio\" and \"upload\" are on \"chmod 777\", so I can read AND write exciting audio content there.</p><p>Don't know what I'm talking about? <a href=\"http://www.tamba2.org.uk/wordpress/ftp/chmod/index.html\">Read this!</a></p>";
echo "<h2>That's it!</h2>";
echo "<p>All good. You should delete this installation file now. It may harm your data.</p>";

echo "<h2>And now?</h2>";
echo "<form style=\"float: left\" action=\"index.php\" 
        method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"submit\" value=\"visit your new website\" />\n";
echo "</form>";

echo "<form action=\"loudblog/index.php\" 
        method=\"post\" enctype=\"multipart/form-data\">";
echo "<input type=\"submit\" value=\"go to administration\" />\n";
echo "</form>";

}


// ------------------------------------------------------------------------

function create() {

if (isset($_POST['sqluser']) AND ($_POST['sqluser'] != "")) {

//connect to the database
mysql_connect($_POST['sqlhost'], $_POST['sqluser'], $_POST['sqlpass']) OR
die("Unfortunately I couldn't connect to the database. <br />".mysql_error());
mysql_select_db($_POST['sqldata']) OR
die("Unfortunately I couldn't work with this database. <br />".mysql_error());


//build prefix-strings
$lb_authors = $_POST['sqlprefix'] . "lb_authors";
$lb_categories = $_POST['sqlprefix'] . "lb_categories";
$lb_comments = $_POST['sqlprefix'] . "lb_comments";
$lb_links = $_POST['sqlprefix'] . "lb_links";
$lb_postings = $_POST['sqlprefix'] . "lb_postings";
$lb_settings = $_POST['sqlprefix'] . "lb_settings";

$dosql =
"CREATE TABLE ".$lb_authors." (
  `id` int(4) NOT NULL auto_increment,
  `nickname` varchar(32) default NULL,
  `password` varchar(32) default NULL,
  `mail` varchar(128) default NULL,
  `realname` varchar(64) default NULL,
  `joined` datetime default NULL,
  `edit_own` int(2) default '1',
  `publish_own` int(2) default '0',
  `edit_all` int(2) default '0',
  `publish_all` int(2) default '0',
  `admin` int(2) default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";
$result = mysql_query($dosql) OR die (mysql_error());

$dosql = 
"INSERT INTO ".$lb_authors." (`nickname`,`password`,`joined`,`edit_own`,`publish_own`,`edit_all`,`publish_all`,`admin`) VALUES (\"".$_POST['nick']."\",\"".md5($_POST['pass'])."\",\"".date("Y-m-d H:i:s")."\",\"1\",\"1\",\"1\",\"1\",\"1\");";
$result = mysql_query($dosql) OR die (mysql_error());

$dosql = 
"CREATE TABLE `".$lb_categories."` (
  `id` int(4) NOT NULL auto_increment,
  `name` varchar(32) default NULL,
  `description` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";
$result = mysql_query($dosql) OR die (mysql_error());

$dosql = 
"INSERT INTO `".$lb_categories."` (`id`,`name`,`description`) VALUES (\"1\",\"default\",\"This is the default category\");";
$result = mysql_query($dosql) OR die (mysql_error());

$dosql = 
"CREATE TABLE `".$lb_comments."` (
  `id` int(11) NOT NULL auto_increment,
  `posting_id` int(11) default NULL,
  `posted` datetime default NULL,
  `name` varchar(64) default NULL,
  `mail` varchar(128) default NULL,
  `web` varchar(128) default NULL,
  `ip` varchar(32) default NULL,
  `message_input` text,
  `message_html` text,
  `audio_file` varchar(255) default NULL,
  `audio_type` int(4) default NULL,
  `audio_length` int(8) default NULL,
  `audio_size` int(11) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";
$result = mysql_query($dosql) OR die (mysql_error());

$dosql = 
"CREATE TABLE `".$lb_links."` (
  `id` int(11) NOT NULL auto_increment,
  `posting_id` int(11) default NULL,
  `description` varchar(255) default NULL,
  `title` varchar(255) default NULL,
  `url` varchar(255) default NULL,
  `linkorder` int(3) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";
$result = mysql_query($dosql) OR die (mysql_error());

$dosql = 
"CREATE TABLE `".$lb_postings."` (
  `id` int(11) NOT NULL auto_increment,
  `author_id` int(4) default NULL,
  `title` varchar(255) default NULL,
  `posted` datetime default NULL,
  `filelocal` int(2) default NULL,
  `audio_file` varchar(255) default NULL,
  `audio_type` int(4) default NULL,
  `audio_length` int(8) default NULL,
  `audio_size` int(11) default NULL,
  `message_input` text,
  `message_html` text,
  `comment_on` int(2) default NULL,
  `comment_size` int(11) default NULL,
  `category1_id` int(4) default NULL,
  `category2_id` int(4) default NULL,
  `category3_id` int(4) default NULL,
  `category4_id` int(4) default NULL,
  `status` int(2) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";
$result = mysql_query($dosql) OR die (mysql_error());

$dosql = 
"INSERT INTO `".$lb_postings."` (`id`,`author_id`,`title`,`posted`,`filelocal`,`audio_file`,`audio_type`,`audio_length`,`audio_size`,`message_input`,`message_html`,`comment_on`,`comment_size`,`category1_id`,`category2_id`,`category3_id`,`category4_id`,`status`) VALUES (\"1\",\"1\",\"Loudblog\",\"2005-03-29 16:32:42\",\"1\",\"podcast-2005-03-29-69562.mp3\",\"1\",\"0\",\"28877\",\"\",\"\n\n\n \",\"1\",\"1048576\",\"0\",\"0\",\"0\",\"0\",\"3\");";
$result = mysql_query($dosql) OR die (mysql_error());

$dosql = 
"CREATE TABLE `".$lb_settings."` (
  `name` varchar(32) default NULL,
  `value` varchar(255) default NULL
) TYPE=MyISAM;";
$result = mysql_query($dosql) OR die (mysql_error());

$dosql = array( 
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"sitename\",\"My Loudblog\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"slogan\",\"blogging it loud since 2005\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"description\",\"My first Loudblog installation\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"url\",\"".$_POST['siteurl']."\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"flashcom_on\",\"0\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"markuphelp\",\"1\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"filename\",\"podcast\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"dateformat\",\"Y-m-d\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"rename\",\"1\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"showlinks\",\"10\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"comments_on\",\"0\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"id3_overwrite\",\"0\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"id3_album\",\"\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"id3_artist\",\"\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"id3_year\",\"\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"id3_genre\",\"\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"id3_comment\",\"\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"rss_postings\",\"10\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"showpostings\",\"15\")",
"INSERT INTO `".$lb_settings."` (`name`,`value`) VALUES (\"template\",\"default\");");

foreach ($dosql as $sql) {
$result = mysql_query($sql) OR die (mysql_error());
}

return true;



} else { return false; }


}


?>