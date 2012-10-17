<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<?php
error_reporting(E_ALL);
require_once('parser.php');
$artist=(isset($_GET['artist']) ? $_GET['artist'] : "The Beatles");
$bcp = new BCParser("http://chartarchive.org", "/a/");
$triples = $bcp->getChartsByArtist($artist, "http://dbpedia.org/resource/The_Beatles");
//var_dump($bcp->unresolvedReleases);
var_dump($triples);


?>
</body>
</html>