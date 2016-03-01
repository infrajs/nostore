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
header('Cache-control: no-store');
$res = Nostore::is();
//var_dump($res);die;

/**
 * Nostore::pub - Если отсутствует заголовок 'Cache-control' со значением 'no-store', то данный метод
 * устанавливает заголовок 'Cache-control' со значением max-age, которое определено в конфигурации системы.
 * По умолчанию данное значения равно 5 часам.
 */
Nostore::pub();

/**
 * Nostore::init - Если в конфигурации системы свойство public установлено в значение true, то
 * если отсутствует заголовок 'Cache-control' со значением 'no-store', данный метод
 * устанавливит заголовок 'Cache-control' со значением max-age, которое определено в конфигурации системы.
 * По умолчанию данное значения равно 5 часам.
 * Если свойство public будет равно false, то метод установит значение заголовка 'Cache-control' равное 'no-cache'.
 */
Nostore::init();

/**
 * Nostore::isPub - Данный метод ищет в списке заголовков заголовок 'Cache-control' со значением 'public'.
 * Если такое значение будет найдено, то метод вернет false, иначе метод вернет true.
 */
Nostore::isPub();

/**
 * Nostore::on - Данный метод устанавливает заголовку 'Cache-control' значение 'no-store'
 */
Nostore::on();

/**
 * Nostore::off -
 */