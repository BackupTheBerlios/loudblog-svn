<?php 

function bloglines($content) {
//calls external service Bloglines.com for generating a blogroll
$att = getattributes($content);
if (isset($att['username'])) { 
    $remote = "http://rpc.bloglines.com/blogroll?html=1&id={$att['username']}";
    if (isset($att['folder'])) {
        $remote .= "&folder={$att['folder']}";
    }
} else { $remote = ""; }

if (ini_get('allow_url_fopen')) {

    //copy the file from the url to variable
    $sourcefile = fopen ($remote, "rb") OR die("not successfully!");
    $eof = false;
    $return = "";
    do {
        $temp = fread ($sourcefile, 1024) OR $eof = true;
        $return .= $temp;
    } while ($eof==false);
    fclose($sourcefile);

} else {
    $return  = "<script language=\"javascript\"";
    $return .= "type=\"text/javascript\" src=\"$remote\"></script>";
    $return = str_replace("?html=1&", "?", $return);
}

return $return;
}


?>