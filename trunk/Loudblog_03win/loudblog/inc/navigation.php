<?php

echo "<div id=\"navi\">\n";
echo "<h2>Navigation</h2>\n";
echo "<ul>\n";
echo "<li id=\"tab_record\">";
echo "<a href=\"index.php?page=record1\">recording</a></li>\n";
echo "<li id=\"tab_postings\"><a href=\"index.php?page=postings\">";
echo "postings</a></li>\n";

if (allowed(3,"")) {

echo "<li id=\"tab_comments\"><a href=\"index.php?page=comments\">";
echo "comments</a></li>\n";
echo "<li id=\"tab_organisation\">";
echo "<a href=\"index.php?page=organisation\">organisation</a></li>\n";
echo "<li id=\"tab_settings\">";
echo "<a href=\"index.php?page=settings\">settings</a></li>\n";

}

echo "<li id=\"tab_logout\"><a href=\"index.php?do=logout\">logout</a></li>\n";

echo "</ul>\n";
echo "</div>\n";

?>


