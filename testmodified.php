<?php

ob_start();

//echo 1;
//echo '<pre>';
//print_r($_SERVER);
//header('Cache-Controll: no-cache');


$time = filemtime(__FILE__);


header_remove('Cache-Control');
header_remove('Expires');

if (empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
	echo '<br>'.'Пустой HTTP_IF_MODIFIED_SINCE';
} else {
	if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $time) {
		http_response_code(304);
		exit;
	}
}
$strtime = gmdate('D, d M Y H:i:s', $time).' GMT';
header('Last-Modified: '.$strtime);

echo '<br>'.'20';

?>

<a href="/-nostore/testmodified.php?t&-access=true">t сбрасывает ServiceWorkder, -access=true сбрастывает Router modified</a>

