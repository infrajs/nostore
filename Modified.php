<?php
namespace infrajs\nostore;

class Modified {
	public static $time = 0;
	public static function sent($time) {
		if (Modified::$time < $time) {
			Modified::$time = $time;
			//Выставить нужно максимальный Last-Modified
			$strtime = gmdate('D, d M Y H:i:s', $time).' GMT';
			header('Last-Modified: '.$strtime);
		}
		//header_remove('Expires');
		//header_remove('Cache-Control');
	}
	public static function time($time) {
		if (!$time) return;

		Modified::sent($time);

		if (empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) return;
		if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $time) {
			http_response_code(304);
			exit;
		}
	}
	public static function etagtime($etag, $time) { //304 только если оба условия удовлетворены
		if (!$etag) return;
		if (!$time) return;

		Modified::sent($time);

		header('ETag: '.$etag);

		if (empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) return;
		if (empty($_SERVER['HTTP_IF_NONE_MATCH'])) return;

		if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $time) return;
		if ($_SERVER['HTTP_IF_NONE_MATCH'] == $etag) return;

		http_response_code(304);
		exit;
	}
	public static function etag($etag) {
		if (!$etag) return;
		header('ETag: '.$etag);
		if (empty($_SERVER['HTTP_IF_NONE_MATCH'])) return;
		if ($_SERVER['HTTP_IF_NONE_MATCH'] == $etag) return;
		http_response_code(304);
		exit;
	}
}