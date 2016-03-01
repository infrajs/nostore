<?php
namespace infrajs\nostore;
if (!is_file('vendor/autoload.php')) {
    chdir('../../../'); //Согласно фактическому расположению файла
    require_once('vendor/autoload.php');
}

/**
 * Nostore::is - Проверяет, есть ли в списке отправленных заголовков, заголовок 'Cache-control' с переданным значением 'no-store'.
 * Если есть, то возвращает true, иначе возвращает false.
 */
Nostore::is();

/**
 * Nostore::pub - Если отсутствует заголовок 'Cache-control' со значением 'no-store', то данный метод
 * устанавливает заголовок 'Cache-control' со значением max-age, которое определено в конфигурации системы.
 * По умолчанию данное значения равно 5 часам.
 */
Nostore::pub();

/**
 * Nostore::init -
 */
Nostore::init();