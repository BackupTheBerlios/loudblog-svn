<?php

echo "<h1>Upload Audio</h1>\n";

echo "<applet code=\"ZUpload\" archive=\"modules/ZUpload.jar\" width=\"450\" height=\"250\" border=\"0\" alt=\"Obviously your browser has not the ability to show Java-applets.\">\n";

echo "<param name=\"host\" value=\"".$settings['ftp_server']."\" />\n";
echo "<param name=\"user\" value=\"".$settings['ftp_user']."\" />\n";
echo "<param name=\"pass\" value=\"".$settings['ftp_pass']."\" />\n";
echo "<param name=\"path\" value=\"".$settings['ftp_path']."\" />\n";
echo "<param name=\"postscript\" value=\"index.php?page=record1\" />\n";
echo "</applet>\n"; 

echo "<input onClick=\"opener.location.reload(); window.close();\" type=\"submit\" value=\"Upload finished? Click here!\" />";


?>