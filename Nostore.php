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
		"max-age" => 86400, //24 часа
		"public" => true
	);
	/**
	 * в автозапуск инициализацию вынести нельзя так как нет причин обращаться к Nostore а автозапуск
	 * привязан к обращение к классу
	 **/
	public static function init(){

		$conf=Nostore::$conf;
		//Значения по умолчанию выставляются
		if ($conf['public']) {
			Nostore::pub(); //Администраторы вкурсе кэша
		} else {
			Nostore::off(true); //Администраторы не знают как отключать кэш в браузере или для удобства
		}
	}
	public static function is()
	{
		$list = headers_list();
		foreach ($list as $name) {
			$r = explode(':', $name, 2);
			if ($r[0] == 'Cache-Control') {
				return (strpos($r[1], 'no-store') !== false);
			}
		}
		return false;
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
	/**
	 * Используется в php для включения в кэш браузера
	 * Если кэш в конфиге запрещён public = false вызов проигнорируется
	 * Вызывается в init
	 **/
	public static function pub()
	{
		if (Nostore::is()) return;
		header('Cache-Control: max-age='.static::$conf['max-age'].', public'); //Переадресация на статику кэшируется
	}
	public static function on()
	{
		header('Cache-Control: no-store'); 
	}

	/**
	 * no-cache или public выбирается только общим конфигом. И не рекомендуеся вызывать off() отдельно.
	 */
	public static function off()
	{
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