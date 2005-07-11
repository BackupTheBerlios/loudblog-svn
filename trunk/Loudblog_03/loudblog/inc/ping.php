<?php

include ('inc/xmlrpc.php');

$pingname = $settings['sitename'];
$pingfeed = $settings['url']."/podcast.php";
$pingurl  = $settings['url'];


//ping audio.weblogs.com
@XMLRPC_request(                     
    "audiorpc.weblogs.com",         //host
    "/RPC2",                        //path
    "weblogUpdates.ping",           //method
    array(
    XMLRPC_prepare($pingname),      //parameter1
    XMLRPC_prepare($pingurl)),      //parameter2
    "Loudblog");                    //user agent
    
//ping audio.weblogs.com RSS
@XMLRPC_request(
    "audiorpc.weblogs.com",         //host
    "/RPC2",                        //path
    "rssUpdate",                    //method
    array(
    XMLRPC_prepare($pingname),      //parameter1
    XMLRPC_prepare($pingfeed)),     //parameter2
    "Loudblog");                    //user agent
        
//ping ping-o-matic
@XMLRPC_request(         
    "rpc.pingomatic.com",           //host
    "/RPC2",                        //path  
    "weblogUpdates.ping",           //method
    array(
    XMLRPC_prepare($pingname),      //parameter1
    XMLRPC_prepare($pingurl),       //parameter2
    XMLRPC_prepare(""),             //parameter3
    XMLRPC_prepare($pingfeed)),     //parameter4
    "Loudblog");                    //user agent

?>