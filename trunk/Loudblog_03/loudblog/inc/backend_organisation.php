<?php
echo "<h1>Organisation</h1>\n";

include ('inc/navigation.php');
include ('inc/functions_organisation.php');

//check the rights
if (!allowed(3,"")) 
{ die("<p class=\"msg\">Administrators do some wild party in here. You are not invited :-(</p>"); }

//what do we get from url? go to appropiate function(s)!

if (!isset($_GET['do'])) { 
    showcatsandauthors (); } 

else { 

if ($_GET['do'] == "editauthor") { 
    showauthor ($_GET['id'], false); }
    
if ($_GET['do'] == "newauthor") { 
    showauthor (0, true); }
    
if ($_GET['do'] == "delauthor") {
    if ($_GET['id'] != $_SESSION['authorid']) {
        deleteauthor ($_GET['id']);
    } else { echo "<p class=\"msg\">You cannot delete yourself!</p>"; }
    showcatsandauthors (); }
    
if ($_GET['do'] == "saveauthor") { 
    if (savepostedauthordata ($_GET['id'])) { showcatsandauthors (); }
    else { showauthor ($_GET['id'], false); }
    }
    
if ($_GET['do'] == "savecats") { 
    savepostedcats (); 
    showcatsandauthors (); }


}

?>