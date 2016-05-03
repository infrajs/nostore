<?php
namespace infrajs\nostore;
if (!is_file('vendor/autoload.php')) {
	chdir('../../../'); //Согласно фактическому расположению файла
	require_once('vendor/autoload.php');
}

/**
 * Nostore::is - Проверяет, есть ли в списке отправленных заголовков, заголовок 'Cache-control'
 * с переданным значением 'no-store'.
 * Если есть, то возвращает true, иначе возвращает false.
 */
header('Cache-Control: public');
$res = Nostore::is();
assert(false === $res);
header('Cache-Control: no-store');
$res = Nostore::is();
assert(true === $res);

/**
 * Nostore::pub - Если отсутствует заголовок 'Cache-control' со значением 'no-store', то данный метод
 * устанавливает заголовок 'Cache-control' со значением max-age, которое определено в конфигурации системы.
 * По умолчанию данное значения равно 5 часам.
 */
header('Cache-Control: no-store');
Nostore::pub();
$res = headers_list();
foreach ($res as $r) {
	$r = explode(':', $r, 2);
	if ($r[0] == 'Cache-Control' && $r[1] == ' no-store') {
		$res = true;
	} else {
		$res = false;
	}
}
assert(true == $res);

header('Cache-Control: no-cache');
Nostore::pub();
$list = headers_list();
$res = false;
foreach ($list as $r) {
	$r = explode(':', $r, 2);
	if ($r[0] == 'Cache-Control') {
		$res = (strstr($r[1], 'public') !== false);
		break;
	}
}
assert(true == $res);

/**
 * Nostore::init - Если в конфигурации системы свойство public установлено в значение true, то
 * если отсутствует заголовок 'Cache-control' со значением 'no-store', данный метод
 * устанавливит заголовок 'Cache-control' со значением max-age, которое определено в конфигурации системы.
 * По умолчанию данное значения равно 5 часам.
 * Если свойство public будет равно false, то метод установит значение заголовка 'Cache-control' равное 'no-cache'.
 */
$origpublic = Nostore::$conf['public'];
Nostore::$conf['public'] = true;
header('Cache-Control: no-cache');
Nostore::init();
$res = headers_list();
foreach ($res as $r) {
	$r = explode(':', $r, 2);
	if ($r[0] == 'Cache-Control') {
		$res = (strstr($r[1], 'public') !== false);
		break;
	}
}
assert(true == $res);

header('Cache-Control: no-store');
Nostore::init();
$res = headers_list();
foreach ($res as $r) {
	$r = explode(':', $r, 2);
	if ($r[0] == 'Cache-Control') {
		$res = (strstr($r[1], 'no-store') !== false);
		break;
	}
}
assert(true == $res);

Nostore::$conf['public'] = false;
Nostore::init();
$res = headers_list();
foreach ($res as $r) {
	$r = explode(':', $r, 2);
	if ($r[0] == 'Cache-Control') {
		$res = (strstr($r[1], 'no-cache') !== false);
		break;
	}
}
assert(true == $res);

/**
 * Nostore::isPub - Данный метод ищет в списке заголовков заголовок 'Cache-control' со значением 'public'.
 * Если такое значение будет найдено, то метод вернет false, иначе метод вернет true.
 */
header('Cache-Control: max-age=18000, public');
$res = Nostore::isPub();
assert(true == $res);
header('Cache-Control: no-cache');
$res = Nostore::isPub();
assert(false == $res);

/**
 * Nostore::on - Данный метод устанавливает заголовку 'Cache-control' значение 'no-store'
 */
header('Cache-Control: max-age=18000, public');
Nostore::on();
$res = headers_list();
foreach ($res as $r) {
	$r = explode(':', $r, 2);
	if ($r[0] == 'Cache-Control') {
		$res = (strstr($r[1], 'no-store') !== false);
		break;
	}
}
assert(true == $res);

/**
 * Nostore::off - Даннлый метод не рекомендуется использовать отдельно.
 * no-cache или public выбирается только общим конфигом. И не рекомендуеся вызывать off() отдельно.
 * Он устанавливает заголовку 'Cache-control' значение 'no-cache'
 */
Nostore::off();
$res = headers_list();
foreach ($res as $r) {
	$r = explode(':', $r, 2);
	if ($r[0] == 'Cache-Control') {
		$res = (strstr($r[1], 'no-cache') !== false);
		break;
	}
}
assert(true == $res);

/**
 * Nostore::check - Данный метод проверяет, включился ли кэш в выполняемой функции $callback.
 */
header('Cache-control: no-cache');
$res = Nostore::check( function (){
	header('Cache-Control: no-cache');
});
assert(false === $res);

header('Cache-control: no-cache');
$res = Nostore::check( function (){
	header('Cache-Control: no-store');
});
assert(true === $res);

header('Cache-control: no-store');
$res = Nostore::check( function (){
	header('Cache-Control: no-cache');
});
assert(false === $res);

header('Cache-control: no-store');
$res = Nostore::check( function (){
	header('Cache-Control: no-store');
});
assert(true === $res);

if (!session_id()) { //Тест сработает только если сессия ещё не стартовала
	$res = Nostore::check( function (){
		session_start(); //В этот момент php отправляет заголовок Cache-Controll no-store и мы об этом узнаём с помощью функции check
	});
	assert(true === $res);
}
Nostore::$conf['public'] = $origpublic;
Nostore::on(); //Когда идут тесты кэшировать сайт нельзя

echo '{"result":1,"msg":"Все тесты пройдены"}';