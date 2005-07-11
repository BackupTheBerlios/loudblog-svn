<?php ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="content-language" content="en" />
    <title><?php echo pagetitle(); ?></title>
    <meta name="robots" content="noindex, nofollow" />
    <meta name="description" content="Loudblog" />
    <meta name="author" content="Gerrit van Aaken" />
    
    <link rel="stylesheet" type="text/css" href="backend/screen.css" />
    <!--[if IE]>
    <link rel="stylesheet" type="text/css" href="backend/ie.css"  />
    <![endif]-->
    <script type="text/javascript">
    <!--
    function areyousure(thisform) { 
    if (confirm("Are you sure?")) { 
    return true; 
    } else { 
    return false; 
    } 
    } 
    -->
    </script>
</head>




<body id="<?php 

if (!$access) { echo "login"; }
else {
    if (isset($_GET['page'])) { echo $_GET['page']; }
    else { echo "postings"; }
} 

?>">

<div id="wrapper">



