<?php

use infrajs\nostore\Nostore;
use infrajs\ans\Ans;

//depricated 
//TODO Улучшить работу с кэшем
Nostore::pubStat();

$ports = Nostore::$conf['port'];
$port = Ans::GET('port');
if (!$port) return Ans::html('Требутеся параметр ?port=... возможные значения регистриуются в конфиге. <pre>'.print_r($ports,true).'</pre>');
if (empty($ports[$port])) return Ans::html('Указанный port='.$port.' не зарегистрирован в конфиге. <pre>'.print_r($ports,true).'</pre>');


$text = file_get_contents($ports[$port]);

return Ans::js($text);