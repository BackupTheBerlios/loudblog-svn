<?php

function savepostedcats () {

global $settings;
$countcat = max_id("lb_categories");
    
//delete or update existing categories
for ($j=1; $j<=$countcat; $j++) {
    
    //preparing looped post-array-things
    $tempcat = "cat" . $j; 
    $tempdesc = "desc" . $j;
    $tempdel = "del" . $j;
    
    //delete categories, if requested
    if (isset($_POST[$tempdel])) {
        $dosql = "DELETE FROM ".$GLOBALS['prefix']."lb_categories 
                  WHERE id = '" . $j . "';";
        $result = mysql_query($dosql) OR die (mysql_error());
        
    } else {
        
        //or update existing categories    
        if (isset($_POST[$tempcat])) {
        
            $putcat = htmlentities($_POST[$tempcat], ENT_QUOTES, "UTF-8");
            $putdes = htmlentities($_POST[$tempdesc], ENT_QUOTES, "UTF-8");
            $dosql = "UPDATE ".$GLOBALS['prefix']."lb_categories SET
                      name        = '" . $putcat . "',
                      description = '" . $putdes . "'
                      WHERE id = '" . $j . "';";
            $result = mysql_query($dosql) OR die (mysql_error());

        }
    }
}

//add new categories
if (isset($_POST['newcat']) AND ($_POST['newcat'] != "")) {

    $putcat = htmlentities($_POST['newcat'], ENT_QUOTES, "UTF-8");
    $putdes = htmlentities($_POST['newdesc'], ENT_QUOTES, "UTF-8");
    
    $dosql = "INSERT INTO ".$GLOBALS['prefix']."lb_categories
                (name, description)
                VALUES (
                '" . $putcat . "', '" . $putdes . "' );";
    $result = mysql_query($dosql) OR die (mysql_error());
}

echo "<p class=\"msg\">";
echo "Successfully updated categories.</p>";

}

// ----------------------------------------------------------------

function savepostedauthordata ($editid) {

global $settings;
$message = "";
$return = true;
$message = "Successfully saved changes! ";
$changepass = false;


//preparing posted data for saving
$putnick = htmlentities($_POST['nickname'], ENT_QUOTES, "UTF-8");
$putreal = htmlentities($_POST['realname'], ENT_QUOTES, "UTF-8");
$putmail = htmlentities($_POST['mail'], ENT_QUOTES, "UTF-8");
if (isset($_POST['edit_own']))    $putright1 = "1"; else $putright1 = "0";
if (isset($_POST['publish_own'])) $putright2 = "1"; else $putright2 = "0";
if (isset($_POST['edit_all']))    $putright3 = "1"; else $putright3 = "0";
if (isset($_POST['publish_all'])) $putright4 = "1"; else $putright4 = "0";
if (isset($_POST['admin']))       $putright5 = "1"; else $putright5 = "0";

//you cannot degrade yourself, if you're an administrator!!
if (($putright5 == "0") AND ($editid == getuserid($_SESSION['nickname']))) {
    $putright5 = "1";
    $message = "Administrators cannot degrade themselves! ";
    $return = false;
}

//prepare password-change
if (($_POST['password'] != "default") 
AND ($_POST['password'] == $_POST['password2'])) {

    $putpass = "password = '" . md5($_POST['password']) . "',";
    $return = true;
    $changepass = true;

} else { 
    $putpass = "";
    if ($_POST['password'] != $_POST['password2']) {
    $message = "Password and confirmation did not match! ";
    $return = false;
    }
}
    
$dosql = "UPDATE ".$GLOBALS['prefix']."lb_authors SET
          " . $putpass . "
          nickname    = '" . $putnick . "',
          realname    = '" . $putreal . "',
          mail        = '" . $putmail . "',
          edit_own    = '" . $putright1 . "',
          publish_own = '" . $putright2 . "',
          edit_all    = '" . $putright3 . "',
          publish_all = '" . $putright4 . "',
          admin       = '" . $putright5 . "'
          
          WHERE id = '" . $editid . "';";
$result = mysql_query($dosql) OR die (mysql_error());

echo "<p class=\"msg\">". $message . "</p>";

//set fresh cookies, if user is editing his own data
if ($editid == getuserid($_SESSION['nickname'])) {
    $_SESSION['nickname'] = $putnick;
    if ($changepass) {
        $_SESSION['password'] = md5($_POST['password']);
    }
}

return $return;

}

// ----------------------------------------------------------------

function deleteauthor ($delid) {

global $settings;
//delete author from database
$dosql = "DELETE FROM ".$GLOBALS['prefix']."lb_authors 
          WHERE id = '". $delid . "';";
$result = mysql_query($dosql) OR die (mysql_error());
}         


// ----------------------------------------------------------------

function showauthor ($editid, $new) {

global $settings;
if ($new) {
    $tempdate = date('Y-m-d H:i:s');

    //insert a new row to the database and fill it with empty data
    $dosql = "INSERT INTO ".$GLOBALS['prefix']."lb_authors
             (joined, nickname, realname, mail, password,
             edit_own, publish_own, edit_all, publish_all, admin)   
             VALUES
             (
             '".$tempdate."', '".$_POST['newnick']."', 
             '".$_POST['newname']."', '".$_POST['newmail']."', 
             '', '1', '0', '0', '0', '0'
             );";
    $result = mysql_query($dosql) OR die (mysql_error());

    //finding the id of the new entry
    $dosql = "SELECT id FROM ".$GLOBALS['prefix']."lb_authors 
              WHERE joined = '".$tempdate."';";
    $result = mysql_query($dosql) OR die (mysql_error());
    $row = mysql_fetch_assoc($result);
    $editid = $row['id'];
}

//getting data for requested author-id from authors-table
$dosql = "SELECT * FROM ".$GLOBALS['prefix']."lb_authors 
          WHERE id = '" . $editid . "';";
$result = mysql_query($dosql) OR die (mysql_error());
$author = mysql_fetch_assoc($result);

echo "<div id=\"authordetails\">\n";
echo "<h2>Change details for ".$author['nickname']."</h2>\n";

//start the form
echo "<form action=\"index.php?page=organisation&amp;do=saveauthor&amp;id=". $editid;
echo "\" method=\"post\" enctype=\"multipart/form-data\">\n\n";

echo "<table>\n\n";

//showing date/time of joining
$dateformat = $settings['dateformat'];
$showdate = date($dateformat , strtotime($author['joined']));
echo "<tr><td class=\"left\">joined:</td><td>" . $showdate . "</td></tr>\n";

//nickname
echo "<tr><td class=\"left\">Nickname:</td><td>";
echo "<input type=\"text\" name=\"nickname\" value=\"";
echo $author['nickname'] . "\" /></td></tr>\n";

//real name
echo "<tr><td class=\"left\">Real Name:</td><td>";
echo "<input type=\"text\" name=\"realname\" value=\"";
echo $author['realname'] . "\" /></td></tr>\n";

//email-adress
echo "<tr><td class=\"left\">eMail:</td><td>";
echo "<input type=\"text\" name=\"mail\" value=\"";
echo $author['mail'] . "\" /></td></tr>\n";

//show the author's publication-rights
echo "<tr><td class=\"left\">Edit own:</td>\n<td class=\"explain\">";
echo "<input name=\"edit_own\" type=\"checkbox\" ";
echo checker($author['edit_own']) . " /> ";
echo $author['nickname'] . " may create new postings and edit them later</td></tr>\n";

echo "<tr><td class=\"left\">Publish own:</td>\n<td class=\"explain\">";
echo "<input name=\"publish_own\" type=\"checkbox\" ";
echo checker($author['publish_own']) . " /> ";
echo $author['nickname'] . " may set own postings to live status</td></tr>\n";

echo "<tr><td class=\"left\">Edit all:</td>\n<td class=\"explain\">";
echo "<input name=\"edit_all\" type=\"checkbox\" ";
echo checker($author['edit_all']) . " /> ";
echo $author['nickname'] . " may edit postings from any author</td></tr>\n";

echo "<tr><td class=\"left\">Publish all:</td>\n<td class=\"explain\">";
echo "<input name=\"publish_all\" type=\"checkbox\" ";
echo checker($author['publish_all']) . " /> ";
echo $author['nickname'] . " may set postings from any author to live status</td></tr>\n";

echo "<tr><td class=\"left\">Administrator:</td>\n<td class=\"explain\">";
echo "<input name=\"admin\" type=\"checkbox\" ";
echo checker($author['admin']) . " /> ";
echo $author['nickname'] . " is administrator and can do anything</td></tr>\n";


//password with password-confirm
if ($new) { $hiddenpass = ""; } else { $hiddenpass = "default"; }

echo "<tr><td class=\"left\">Change password:</td>\n<td>";
echo "<input type=\"password\" name=\"password\" value=\"";
echo $hiddenpass . "\" /></td></tr>\n";

echo "<tr><td class=\"left\">Repeat password:</td>\n<td>";
echo "<input type=\"password\" name=\"password2\" value=\"";
echo $hiddenpass . "\" /></td></tr>\n";

//update-button
echo "<tr><td class=\"left\"></td><td>";
echo "<input type=\"submit\" name=\"update\" value=\"save\" /></td></tr>\n";

echo "</table>";

//finish the form
echo "</form>";

echo "</div>";

}

// ----------------------------------------------------------------

function showcatsandauthors () {

global $settings;

//-------------------- authors-list ----------

echo "<div id=\"authors\">\n";
echo "<h2>Edit authors</h2>\n\n";

//starting the table
echo "<table>\n\n";
echo "<tr><th>nickname</th><th>full name</th><th>e-mail</th>";
echo "<th>rights</th><th></th></tr>";

//getting all data from authors-table
$dosql = "SELECT * FROM ".$GLOBALS['prefix']."lb_authors ORDER BY id;";
$result = mysql_query($dosql) OR die (mysql_error());
$i = 1;
while ($row = mysql_fetch_assoc($result)) {

echo "<tr>\n";

/*
//showing date/time of joining
$dateformat = $settings['dateformat'];
$showdate = date($dateformat , strtotime($row['joined']));
echo "<td>" . $showdate . "</td>\n";
*/

echo "<td><a href=\"index.php?page=organisation&amp;do=editauthor&amp;id=".$row['id'];
echo "\">" . $row['nickname'] . "</a></td>\n";
echo "<td><a href=\"index.php?page=organisation&amp;do=editauthor&amp;id=".$row['id'];
echo "\">" . $row['realname'] . "</a></td>\n";
echo "<td><a href=\"mailto:".$row['mail']."\">".$row['mail']."</a></td>\n";

//show the author's publication-rights
echo "<td>\n";
echo "<input type=\"checkbox\" disabled=\"disabled\" ";
echo checker($row['edit_own']) . " title=\"User may create new postings and edit them later\" />\n";
echo "<input type=\"checkbox\" disabled=\"disabled\" ";
echo checker($row['publish_own']) . " title=\"User may set own postings to live status\" />\n";
echo "<input type=\"checkbox\" disabled=\"disabled\" ";
echo checker($row['edit_all']) . " title=\"User may edit postings from any author\" />\n";
echo "<input type=\"checkbox\" disabled=\"disabled\" ";
echo checker($row['publish_all']) . " title=\"User may set postings from any author to live status\" />\n";
echo "<input type=\"checkbox\" disabled=\"disabled\" ";
echo checker($row['admin']) . " title=\"User is administrator and can do anything\" />\n</td>\n";


//a simple delete button
echo "<td class=\"right\">\n";
echo "<form method=\"post\" enctype=\"multipart/form-data\" 
      action=\"index.php?page=organisation&amp;do=delauthor&amp;id=".$row['id'];
echo "\" onSubmit=\"return areyousure(this)\">\n";
echo "<input type=\"submit\" value=\"delete\" />\n</form>\n</td>\n";


echo "</tr>\n\n";

$i += 1;
}

//button for new author
echo "<form method=\"post\" enctype=\"multipart/form-data\" 
      action=\"index.php?page=organisation&amp;do=newauthor\">";
      echo "<tr>\n";
echo "<td><input type=\"text\" name=\"newnick\" value=\"\" /></td>\n";
echo "<td><input type=\"text\" name=\"newname\" value=\"\" /></td>\n";
echo "<td><input type=\"text\" name=\"newmail\" value=\"\" /></td>\n";
echo "<td></td>\n<td class=\"right\">\n";
echo "<input type=\"submit\" value=\"new\" />\n";

echo "</td>\n</tr>\n</form>\n</table>\n";
echo "</div>\n\n\n";


//-------------------- categories ----------

echo "<div id=\"categories\">\n";
echo "<h2>Edit categories</h2>\n\n";

//getting all data from category-table
$dosql = "SELECT * FROM ".$GLOBALS['prefix']."lb_categories ORDER BY id;";
$result = mysql_query($dosql) OR die (mysql_error());
$i = 1;
while ($row = mysql_fetch_assoc($result)) {
    $cats[$i] = $row;
    $i += 1;
}

//start the form
echo "<form action=\"index.php?page=organisation&amp;do=savecats\"";
echo " method=\"post\" enctype=\"multipart/form-data\">\n\n";

//show all items in each list
echo "<table>\n";
echo "<tr><th>name</th><th>description</th><th></th></tr>";

$i = 1;
foreach ($cats as $showcat) {

    //show category
    echo "<tr>\n<td>\n";
    echo "<input class=\"cat\" type=\"text\" value=\"" . $cats[$i]['name'];
    echo "\" name=\"cat" . $cats[$i]['id'] . "\" />\n</td>\n<td>";
    
    //show description
    echo "<input class=\"desc\" type=\"text\" value=\"" . $cats[$i]['description'];
    echo "\" name=\"desc" . $cats[$i]['id'] . "\" />\n</td>\n";
    
    //show delete button
    echo "<td class=\"right\"><input onClick=\"return areyousure(this)\" type=\"submit\" value=\"delete\" ";
    echo "name=\"del" . $cats[$i]['id'] . "\" />\n</td>\n</tr>\n\n";
    
    $i += 1;
}

//show a new category, which is to be filled
echo "<tr>\n<td>";
echo "<input class=\"cat\" name=\"newcat\" type=\"text\" value=\"\" /></td>\n";
echo "<td><input class=\"desc\"name=\"newdesc\" type=\"text\" value=\"\" />";
echo "</td><td class=\"right\"><<< add new&nbsp;</td>\n";
echo "</tr>\n";
echo "<tr class=\"last\"><td colspan=\"2\"></td>";
echo "<td class=\"right\"><input type=\"submit\" value=\"save all\" /></td>";

echo "</table>";



echo "</form>\n";

echo "</div>";

} 

?>






