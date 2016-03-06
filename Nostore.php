<?php
namespace infrajs\nostore;
use infrajs\each\Each;

class Nostore {
	/**
	 * Возможны значения no-store, no-cache, public
	 * no-store - вообще не сохранять кэш.
	 * no-cache - кэш сохранять но каждый раз спрашивать не поменялось ли чего.
	 * public - кэш сохранять. Спрашивать об изменениях раз 5 часов или если открыта консоль разработчика. 
	 */
	public static $conf=array(
		"max-age-dyn" => 86400, //24 часа, администратор может изменить
		"max-age-stat" => 604800, //1 неделя, изменяется только при обновлении сайта программистом 
		
		//План обновлений сайта программистом. В Expires будет подставляться следующая непрошедшая дата в 00:00 часов считывается кэш. 8 марта 00:00 статья 080316 Праздник.docx в этоже время начнёт показываться
		"expires-year" => [
			'05.03','10.03',
			'25.01','01.01','18.01',
			'18.02','25.02'],
		
		"expires-month" => [],//Дата месяца
		"expires-str" => [], //'next monday'
		"public" => true //expires работает с этим ключём
	);
	public static function getExpires()
	{
		$conf=static::$conf;
		$time = time() + $conf["max-age-stat"];
		Each::exec($conf["expires-year"], function ($dm) use (&$time) {
			$p = explode('.', $dm);
			$year = date('Y');
			$day = (int) $p[0];
			$month = (int) $p[1];
			$t = mktime(0, 0, 0, $month, $day, $year);
			if ($t < time()) return;
			if ($t < $time) $time = $t;
		});
		Each::exec($conf["expires-month"], function ($dm) use (&$time) {
			$year = date('Y');
			$day = $dm;
			$n = date('d');
			$month = date('n');
			if ($day < $n) $month+=1;
			$t = mktime(0, 0, 0, $month, $day, $year);
			if ($t < $time) $time = $t;
		});
		Each::exec($conf["expires-str"], function ($dm) use (&$time) {
			$t = strtotime($dm);
			if ($t < time()) return;
			if ($t < $time) $time = $t;
		});
		return $time;
	}
	/**
	 * в автозапуск инициализацию вынести нельзя так как нет причин обращаться к Nostore а автозапуск
	 * привязан к обращение к классу
	 **/
	public static function init(){

		$conf=Nostore::$conf;
		//Значения по умолчанию выставляются
		if ($conf['public']) {
			Nostore::pubDyn(); //Администраторы вкурсе кэша
		} else {
			Nostore::off(); //Администраторы не знают как отключать кэш в браузере или для удобства
		}
	}
	public static function initStat()
	{

		$conf=Nostore::$conf;
		//Значения по умолчанию выставляются
		if ($conf['public']) {
			Nostore::pubStat(); //Администраторы вкурсе кэша
		} else {
			Nostore::off(); //Администраторы не знают как отключать кэш в браузере или для удобства
		}
	}
	public static function initDyn()
	{

		$conf=Nostore::$conf;
		//Значения по умолчанию выставляются
		if ($conf['public']) {
			Nostore::pubDyn(); //Администраторы вкурсе кэша
		} else {
			Nostore::off(); //Администраторы не знают как отключать кэш в браузере или для удобства
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
		Nostore::pubDyn();
	}
	public static function pubDyn()
	{
		if (Nostore::is()) return;
		header('Cache-Control: public, max-age='.static::$conf['max-age-dyn']); //Переадресация на статику кэшируется max-age-dyn
		header('Expires:'.date('D, d M Y H:i:s', static::getExpires()));
	}
	public static function pubStat()
	{
		if (Nostore::is()) return;
		header('Cache-Control: public, max-age='.static::$conf['max-age-stat']);
		header('Expires:'.date('D, d M Y H:i:s', static::getExpires()));
	}
	public static function on()
	{
		header('Cache-Control: no-store, max-age=0');
		header('Expires:'.date('D, d M Y H:i:s'));
	}
	/**
	 * no-cache или public выбирается только общим конфигом. И не рекомендуеся вызывать off() отдельно.
	 */
	public static function off()
	{
		header('Cache-Control: no-cache, max-age=0'); //no-cache ключевое слово используемое в infra_cache
		header('Expires:'.date('D, d M Y H:i:s'));
	}
	/**
	 * Реагируем на no-store
	 **/
	public static function check($call)
	{
		$nostore = static::is();
		if ($nostore) { //Есть no-store
			//По умолчанию готовы кэшировать
			static::pubDyn(); //Выставяем public любой, так как потом всё равно нужно будет сбросить в no-store
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