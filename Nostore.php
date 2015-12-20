<?php
namespace infrajs\nostore;

class Nostore {
	/**
	 * Возможны только значения no-store и no-cache
	 * no-store - вообще не сохранять кэш.
	 * no-cache - кэш сохранять но каждый раз спрашивать не поменялось ли чего.
	 */
	public static $conf=array(
		"max-age"=>28000
	);
	public static function is()
	{
		$list = headers_list();
		foreach ($list as $name) {
			$r = explode(':', $name, 2);
			if ($r[0] == 'Cache-Control') {
				return (strpos($r[1], 'no-store') !== false);
			}
		}

		return true;
	}
	public static function cache()
	{
		header('Cache-Control: max-age='.static::$conf['max-age'].', public'); //Переадресация на статику кэшируется на 5 часов. (обновлять сайт надо вечером, а утром у всех всё будет ок)
	}
	public static function on()
	{
		header('Cache-Control: no-store'); 
	}
	public static function off()
	{
		header('Cache-Control: no-cache'); //no-cache ключевое слово используемое в infra_cache
	}
	/**
	 * Реагируем на no-store
	 **/
	public static function check($call)
	{
		$nocache = static::is();
		if ($nocache) { //Есть no-store
			//По умолчанию готовы кэшировать
			header('Cache-Control: no-cache');
		}

		$call();

		//Смотрим есть ли возражения, установил ли кто-то там no-store
		$nocache_after = static::is();
		//Никто не установил но надо вернуть если такой заголовок уже был
		if ($nocache && !$nocache_after) {
			header('Cache-Control: no-store');
		}
		return $nocache_after;
	}
}