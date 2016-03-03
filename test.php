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
$res = headers_list();
foreach ($res as $r) {
    $r = explode(':', $r, 2);
    if ($r[0] == 'Cache-Control' && $r[1] == ' max-age=18000, public') {
        $res = true;
    } else {
        $res = false;
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
Nostore::$conf['public'] = true;
header('Cache-Control: no-cache');
Nostore::init();
$res = headers_list();
foreach ($res as $r) {
    $r = explode(':', $r, 2);
    if ($r[0] == 'Cache-Control' && $r[1] == ' max-age=18000, public') {
        $res = true;
    } else {
        $res = false;
    }
}
assert(true == $res);

header('Cache-Control: no-store');
Nostore::init();
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

Nostore::$conf['public'] = false;
Nostore::init();
$res = headers_list();
foreach ($res as $r) {
    $r = explode(':', $r, 2);
    if ($r[0] == 'Cache-Control' && $r[1] == ' no-cache') {
        $res = true;
    } else {
        $res = false;
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
    if ($r[0] == 'Cache-Control' && $r[1] == ' no-store') {
        $res = true;
    } else {
        $res = false;
    }
}
assert(true == $res);

/**
 * Nostore::off - Даннлый метод не рекомендуется использовать отдельно.
 * Если его использовать отдельно, то обязательно должен быть передан аргумент.
 * Он устанавливает заголовку 'Cache-control' значение 'no-cache'
 */
Nostore::off(true);
$res = headers_list();
foreach ($res as $r) {
    $r = explode(':', $r, 2);
    if ($r[0] == 'Cache-Control' && $r[1] == ' no-cache') {
        $res = true;
    } else {
        $res = false;
    }
}
assert(true == $res);