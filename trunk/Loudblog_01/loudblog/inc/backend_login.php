<?php
echo "<h1>Login</h1>\n";

//choosing an appropriate welcome-message
if (!isset($HTTP_SESSION_VARS['nickname']) OR !isset($_POST['nickname'])) 
    $addmessage = ""; 
if (isset($_POST['nickname'])) 
    $addmessage = "Sorry: incorrect login or password!";
if ((isset($_GET['do'])) AND ($_GET['do'] == "logout")) 
    $addmessage = "Logged out. It was a pleasure to work with you!";

//delete session, if still active
if (isset($SESSION['nickname'])) { session_unset(); session_destroy(); }
    
//simply put the message onto the screen    
echo "<p class=\"msg\">" . $addmessage . "</p>";

echo "<form class=\"plain\"";
echo "action=\"index.php?page=record1\" method=\"post\">\n";
echo "<table>\n";
echo "<tr>\n<th>nickname</th>\n<th>password</th>\n<th></th>\n</tr>\n";
echo "<tr>\n<td><input type=\"text\" name=\"nickname\" /></td>\n";
echo "<td><input type=\"password\" name=\"password\" /></td>\n";
echo "<td><input type=\"submit\" name=\"submit\" value=\"Login\" /></td>\n";
echo "</tr>\n</table>\n</form>\n";

?>