<?php 

function delicious($content) {
//calls external service del.icio.us for generating a linklist
//This is based on MagPieRSS and a nice php-script by Richard Eriksson

$att = getattributes($content);

if (isset($att['number'])) { $number = $att['number']; } else { $number = 10; }

$user = ""; $tag = "";
if (isset($att['username'])) { $user = "/" . $att['username']; }

if (isset($att['tag'])) { 
    if ($user == "") { $tag = "/tag"; }
    $tag  .= "/" . $att['tag']; 
}


$return = "<ul>";
require_once "loudblog/inc/magpierss/rss_fetch.inc"; 
$url = "http://del.icio.us/rss$user$tag";
$yummy = fetch_rss($url);
$maxitems = $number;
$yummyitems = array_slice($yummy->items, 0, $maxitems);
foreach ($yummyitems as $yummyitem) {
    $return .= '<li>';
    $return .= '<a href="';
    $return .= $yummyitem['link'];
    $return .= '">';
    $return .= $yummyitem['title'];
    $return .= '</a>';
    $return .= '</li>';
}
$return .= "</ul>";

return $return;
}


?>