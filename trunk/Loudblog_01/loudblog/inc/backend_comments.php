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
// http://creativecommons.org/licenses/by-sa/2.0/        //
//                                                       //
// Have Fun! Drop me a line if you like Loudblog!        //
// ----------------------------------------------------- //

echo "<h1>Comments</h1>\n";

include ('inc/navigation.php');

//check the rights
if (!allowed(3,"")) 
{ die("<p class=\"msg\">Administrators do some wild party in here. You are not invited :-(</p>"); }



?>