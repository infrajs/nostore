<?php
namespace infrajs\nostore;
$conf=Nostore::$conf;
if ($conf['public']) {
	Nostore::pub(); //Администраторы вкурсе кэша
} else {
	Nostore::off(true); //Администраторы не знают как отключать кэш в браузере или для удобства
}