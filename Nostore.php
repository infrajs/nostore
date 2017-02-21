<?php
namespace infrajs\nostore;
use infrajs\each\Each;
use infrajs\ans\Ans;

class Nostore {
	/**
	 * Возможны значения no-store, no-cache, public
	 * no-store - вообще не сохранять кэш.
	 * no-cache - кэш сохранять но каждый раз спрашивать не поменялось ли чего.
	 * public - кэш сохранять. Спрашивать об изменениях раз 5 часов или если открыта консоль разработчика. 
	 * private
	 */
	public static $public = 'public';
	public static $conf = array(
		"max-age" => 86400, //24 часа, время кэша, когда public:true, для динамики
		"max-age-stat" => 604800, //1 неделя, время кэша когда  public:true и вызван Nostore::pubStat() для статики
		
		//План обновлений сайта программистом. В Expires будет подставляться следующая непрошедшая дата
		"expires-year" => [ //dd.mm
			'05.03','10.03',
			'25.01','01.01','18.01',
			'18.02','25.02'],
		"expires-month" => [],//Дата месяца 1,20
		"expires-str" => [], //'next monday'
		"public" => true, //expires работает с этим ключём
		"port" => array( //указанные файлы можно загрузить по адресу vendor/infrajs/nostore/?port=watch
			"watch" => "https://mc.yandex.ru/metrika/watch.js",
			"twitter" => "http://platform.twitter.com/widgets.js"
		)
	);
	public static function getExpires()
	{
		$conf=static::$conf;
		$time = time() + $conf["max-age-stat"];
		Each::exec($conf["expires-year"], function &($dm) use (&$time) {
			$r = null;
			$p = explode('.', $dm);
			$year = date('Y');
			$day = (int) $p[0];
			$month = (int) $p[1];
			$t = mktime(0, 0, 0, $month, $day, $year);
			if ($t < time()) return $r;
			if ($t < $time) $time = $t;
			return $r;
		});
		Each::exec($conf["expires-month"], function &($dm) use (&$time) {
			$r = null;
			$year = date('Y');
			$day = $dm;
			$n = date('d');
			$month = date('n');
			if ($day < $n) $month+=1;
			$t = mktime(0, 0, 0, $month, $day, $year);
			if ($t < $time) $time = $t;
			return $r;
		});
		Each::exec($conf["expires-str"], function &($dm) use (&$time) {
			$r = null;
			$t = strtotime($dm);
			if ($t < time()) return $r;
			if ($t < $time) $time = $t;
			return $r;
		});
		return $time;
	}
	/**
	 * в автозапуск инициализацию вынести нельзя так как нет причин обращаться к Nostore а автозапуск
	 * привязан к обращение к классу
	 **/
	public static function init()
	{
		$conf = Nostore::$conf;
		$action = Ans::GET('-nostore','string');

		if ($action === 'true') return Nostore::on();
		
		if ($conf['public']) {
			//Значения по умолчанию выставляются
			Nostore::off(); //Администраторы вкурсе кэша
			if (!Router::$main) {
				Nostore::pubStat();
			}
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
				return ((strpos($r[1], 'public') !== false)||(strpos($r[1], 'private') !== false));
			}
		}

		return true;
	}
	public static function isPrivate()
	{
		$list = headers_list();
		foreach ($list as $name) {
			$r = explode(':', $name, 2);
			if ($r[0] == 'Cache-Control') {
				return (strpos($r[1], 'private') !== false);
			}
		}

		return false;
	}
	/**
	 * Используется в php для включения в кэш браузера
	 * Если кэш в конфиге запрещён public = false вызов проигнорируется
	 * Вызывается в init
	 **/
	public static function pub()
	{
		if (Nostore::is()) return;
		if (!Nostore::$conf['public']) return;
		header('Cache-Control: '.static::$public.', max-age='.static::$conf['max-age']); //Переадресация на статику кэшируется max-age
		header('Expires:'.date('D, d M Y H:i:s', static::getExpires()));
	}
	public static function priv()
	{
		if (Nostore::is()) return;
		if (!Nostore::$conf['public']) return;
		static::$public = 'private';
		header('Cache-Control: '.static::$public.', max-age='.static::$conf['max-age']); //Переадресация на статику кэшируется max-age
		header('Expires:'.date('D, d M Y H:i:s', static::getExpires()));
	}
	public static function pubStat()
	{
		if (Nostore::is()) return;
		if (!Nostore::$conf['public']) return;
		header('Cache-Control: '.static::$public.', max-age='.static::$conf['max-age-stat']);
		header('Expires:'.date('D, d M Y H:i:s', static::getExpires()));
	}
	/*
	 * Включить запрет кэширования
	 */
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
		
		if (Nostore::$conf['public']) {
			static::pub();
		} else {
			header('Cache-Control: no-cache, max-age=0'); //no-cache ключевое слово используемое в infra_cache
			header('Expires:'.date('D, d M Y H:i:s'));
		}
	}
	public static function offPrivate()
	{
		if (Nostore::$conf['public']) {
			static::priv();
		} else {
			header('Cache-Control: no-cache, max-age=0'); //no-cache ключевое слово используемое в infra_cache
			header('Expires:'.date('D, d M Y H:i:s'));
		}
	}
	public static function offStat()
	{
		header('Cache-Control: no-cache, max-age=0'); //no-cache ключевое слово используемое в infra_cache
		header('Expires:'.date('D, d M Y H:i:s'));
		if (Nostore::$conf['public']) static::pubStat();
	}
	/**
	 * Реагируем на no-store
	 **/
	public static function check($call)
	{
		$nostore = static::is();
		if ($nostore) { //Есть no-store
			//По умолчанию готовы кэшировать
			static::pub(); //Выставяем public любой, так как потом всё равно нужно будет сбросить в no-store
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
