<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<?php
require_once('parser.php');
$artist=(isset($_GET['artist']) ? $_GET['artist'] : "The Beatles");
$bcp = new BCParser("http://chartarchive.org", "/a/", "http://dbpedia.org/resource/The_Beatles");
$triples = $bcp->getChartsByArtist($artist);
var_dump($triples);
//echo $triples;
/*foreach($triples as $key => $value)
{
	echo $key." : ";
	foreach($value as $value)
	{
		echo $value." ";	
	}
	echo "<br />";	
}*/

?>
</body>
</html>