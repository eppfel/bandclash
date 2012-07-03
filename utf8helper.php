<?php
//UTF-8 Helper converts all strings to UTF-8 to avoid display and storing issues
mb_internal_encoding( 'UTF-8' );

function fixUtf8($text)
{
	$text = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.'|(?<=^|[\x00-\x7F])[\x80-\xBF]+'.'|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.'|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.'|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/','�', $text );

	$text = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]'.'|\xED[\xA0-\xBF][\x80-\xBF]/S','?', $text );
return $text;
}
?>