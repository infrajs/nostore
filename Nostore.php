<?php
namespace infrajs\nostore;

class Nostore {
	/**
	 * Возможны только значения no-store и no-cache
	 * no-store - вообще не сохранять кэш.
	 * no-cache - кэш сохранять но каждый раз спрашивать не поменялось ли чего.
	 */
	public static function is()
	{
		$list = headers_list();
		foreach ($list as $name) {
			$r = explode(':', $name, 2);
			if ($r[0] == 'Cache-Control') {
				return (strpos($r[1], 'no-store') === false);
			}
		}

		return true;
	}
	public static function check($call)
	{
		$cache = static::is();
		if (!$cache) {
			//По умолчанию готовы кэшировать
			header('Cache-Control: no-cache');
		}

		$call();

		//Смотрим есть ли возражения
		$cache_after = static::is();

		if (!$cache && $cache_after) {
			//Возражений нет и функция вернёт это в $cache2..
			//но уже была установка что кэш не делать... возвращем эту установку для вообще скрипта
			header('Cache-Control: no-store');
		}
		return $cache_after;
	}
}