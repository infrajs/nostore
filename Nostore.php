<?php
namespace infrajs\nostore;

class Nostore {
	/**
	 * Возможны значения no-store, no-cache, public
	 * no-store - вообще не сохранять кэш.
	 * no-cache - кэш сохранять но каждый раз спрашивать не поменялось ли чего.
	 * public - кэш сохранять. Спрашивать об изменениях раз 5 часов или если открыта консоль разработчика. 
	 */
	public static $conf=array(
		"max-age" => 28000,
		"public" => true
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
	public static function isPub()
	{
		$list = headers_list();
		foreach ($list as $name) {
			$r = explode(':', $name, 2);
			if ($r[0] == 'Cache-Control') {
				return (strpos($r[1], 'public') !== false);
			}
		}

		return true;
	}
	public static function pub()
	{
		header('Cache-Control: max-age='.static::$conf['max-age'].', public'); //Переадресация на статику кэшируется на 5 часов. (обновлять сайт надо вечером, а утром у всех всё будет ок)
	}
	public static function on()
	{
		header('Cache-Control: no-store'); 
	}
	public static function off($r = null)
	{
		if (!$r) {
			echo '<pre>';
			throw new \Exception('no-cache или public выбирается только общим конфигом. И не рекомендуеся вызывать off() отдельно.');
		}
		header('Cache-Control: no-cache'); //no-cache ключевое слово используемое в infra_cache
	}
	/**
	 * Реагируем на no-store
	 **/
	public static function check($call)
	{
		$nostore = static::is();
		if ($nostore) { //Есть no-store
			//По умолчанию готовы кэшировать
			static::pub(); //Выставяем любое
		}

		$call();

		//Смотрим есть ли возражения, установил ли кто-то там no-store
		$nostore_after = static::is();
		//Никто не установил но надо вернуть если такой заголовок уже был
		if ($nostore && !$nostore_after) {
			static::on();
		}
		return $nostore_after;
	}
}