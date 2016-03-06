[![Latest Stable Version](https://poser.pugx.org/infrajs/nostore/v/stable)](https://packagist.org/packages/infrajs/nostore) [![Total Downloads](https://poser.pugx.org/infrajs/nostore/downloads)](https://packagist.org/packages/infrajs/nostore)

# Managing the browser cache

3 modes

- **no-store**. Content can be changed in an unpredictable time. Content varies with changes in user. New data from the database. Work with the session. The content depends on isAdmin() isDebug() isTest(). Means such content cannot be cached and use the cache for different users.
- **no-cache**. Content can be changed by the administrator and the administrator need to immediately see the result. We cache the data, but always check the. Content can be changed in a predictable time. A cache by modified date of the file.
- **public** - Content may change only when a new version of the site. And you can wait 5 hours. 
The administrator knows that you can refresh the cache in the browser.

```php
Nostore::on(); //no-store
Nostore::off(); //no-cache
Nostore::pub(); //public
```

```php
(bool) Nostore::is(); //true - so there is a header no-store because it was caused on()
```

```php
(bool) Nostore::check(function(){
	//Black box. But we know if inside the call Nostore::on();
}); 
```

# Cache-Control


By default, the response from the web server Apache does not contain the header cache Manager. Expanding Access sets the value on() no-store if you may have some rights or the site is in debug mode.

Since the administrator is given access to the folder data, any script working with data in the folder data must add call Nostore::pub() or Nostore::on(). It is assumed on the website "easy" administration or not is determined by the configuration and the default value. The default value can be Nostore::pub(); or Nostore::off() public or no-cache. Because the script can be used on websites, as administrator and without administrator the default value passed in config.

When you connect the set infrajs Nostore work begins in the mode specified in the config
- 'public':true - public caching is enabled. The administrator of the cache need to be reset to see the changes. Mode Nostore::pub();
- 'public':false - the site has an administrator who does not know how to update your browser cache or refresh the site too often and visitors return to the site often for new content.
True for public, it is desirable to add a similar entry in .htaccess to activate the same level of the cache for static data and the files that go into the crawl php.

The config by default
```
"max-age": 28000,
"public": true
```

When developing you want to override the value taken by default. Is either set to on() to prevent caching of values pub() when cache is clearly possible, usually for the static generated php.

Method off() although is declared but cannot cause it. According to the described procedure cache.

From the point of view of a script to set no-cache or public doesn't matter. Both of these options allow to cache. the no-cache cache managed by the headers If-Modified, and Public requests never come, as if the If-Modified is known in advance that is false. And only no-store truly disables the cache and the contents will be collected again without conditions. Nostore::check and Nostore::is needed to enable the header to float no-store and cache ban everyone who depends on no-store content that can get inside public content and should be replaced public no-store.

### Nostore::is
Checks whether the send list header, the header 'Cache-control' with the value 'no-store'. If there is, it returns true, otherwise returns false.

> Nostore::is();

```
header('Cache-Control: public');
$res = Nostore::is();
assert(false === $res);
header('Cache-Control: no-store');
$res = Nostore::is();
assert(true === $res);
```

### Nostore::pub
If there is no header 'Cache-control' with the value 'no-store', this method sets the header 'Cache-control' to max-age, which is defined in the system configuration. By default, this value is 5 hours.

> Nostore::pub();

```
header('Cache-Control: no-store');
Nostore::pub();

***
Response Headers
Cache-Control: no-store
***

header('Cache-Control: no-cache');
Nostore::pub();

***
Response Headers
Cache-Control: max-age=18000, public
***
```

### Nostore::init
If the system configuration property public set to true and if there is no header 'Cache-control' with the value 'no-store', the method will set the header 'Cache-control' to max-age, which is defined in the system configuration. By default, this value is 5 hours. 
If a public property is equal to false, the method will set the value of the header 'Cache-control' = 'no-cache'.

> Nostore::init();

```
Nostore::$conf['public'] = true;
header('Cache-Control: no-cache');
Nostore::init();

***
Response Headers
Cache-Control: max-age=18000, public
***

header('Cache-Control: no-store');
Nostore::init();

***
Response Headers
Cache-Control: no-store
***

Nostore::$conf['public'] = false;
Nostore::init();

***
Response Headers
Cache-Control: no-cache
***
```

### Nostore::isPub
The method searches in the list header the header 'Cache-control' with the value 'public'.
If such a value is found, then the method will return false, otherwise the method will return true.

> Nostore::isPub();

```
header('Cache-Control: max-age=18000, public');
Nostore::isPub();

***
true
***

header('Cache-Control: no-cache');
Nostore::isPub();

***
false
***
```

### Nostore::on
The method searches in the list header the header 'Cache-control' with the value 'public'.

>Nostore::on();

```
header('Cache-Control: max-age=18000, public');
Nostore::on()

***
Response Headers
Cache-Control: no-store
***
```

### Nostore::off
Method is not recommended to use separately.
no-cache or public select a common config. And not rekomenduetsya to call off() separately.
Sets the header 'Cache-control' value 'no-cache'

> Nostore::off();

### Nostore::check
Method checks, whether the cache involved in the function $callback.

> Nostore::check($callback);

```
header('Cache-control: no-cache');
Nostore::check( function (){
    header('Cache-Control: no-cache');
});

***
false
***

header('Cache-control: no-cache');
Nostore::check( function (){
    header('Cache-Control: no-store');
});

***
true
***

header('Cache-control: no-store');
Nostore::check( function (){
    header('Cache-Control: no-cache');
});

***
false
***

header('Cache-control: no-store');
Nostore::check( function (){
    header('Cache-Control: no-store');
});

***
true
***
```

# Управление кэшем браузера

3 режима

- **no-store**. Содержимое, может быть изменено в непредсказуемый момент. Содержимое меняется с изменениями пользователя. Новые данные из базы данных. Работа с сессией. Содержимое зависит от isAdmin() isDebug() isTest(). Значит такое содержимое нельзя кэшировать и использовать кэш для разных пользователей.
- **no-cache**. Содержимое, может быть изменено администратором и администратору нужно сразу увидеть результат. Данные кэшируем, но всегда проверяем. Содержимое, может быть изменено в предсказуемый момент. Кэш по дате изменения файла.
- **public** - Содержимое, может измениться только при выходе новой версии сайта. И можно подождать 5 часов. Администратор знает что можно обновить кэш в браузере.

```php
Nostore::on(); //no-store
Nostore::off(); //no-cache
Nostore::pub(); //public
```

```php
(bool) Nostore::is(); //true - значит есть заголовок no-store потому что был вызвао on()
```

```php
(bool) Nostore::check(function(){
	//Чёрный ящик. Но мы узнаем был ли внутри вызов Nostore::on();
}); 
```

# Cache-Control


По умолчанию ответ web-сервера Apache не содержит заголовок управляющий кэшем. Расширение Access выставляет значение on() no-store если пользователь может обладать какими-то правами или сайт работает в отладочном режиме.

Так как администратору даётся на доступ папка data, то любой скрипт работающий с данными в папке data должен сам добавить вызов Nostore::pub() или Nostore::on(). Предполагается на сайте "простое" администрирование или нет определяется конфигом и значением по умолчанию. Значение по умолчанию может быть Nostore::pub(); или Nostore::off() public или no-cace. Так как скрипт может использоваться на сайтах, как с администратором, так и без администратора значение по умолчанию вынесено в конфиг.

При подключении в комплекте infrajs Nostore работать начинает в режиме указанном в конфиге 
- 'public':true - кэширование public включено. Администратору кэш нужно сбрасывать чтобы увидеть изменнеия. Режим Nostore::pub();
- 'public':false - на сайте есть администратор, который не умеет обновлять кэш браузера или обновления на сайте слишком частые и посетители возвращаются на сайт очень часто за новым содержанием.
Для public true желательно добавить аналогичную запись и в .htaccess для активации такого же уровня кэша для статики и файлов, которые идут в обход php.

Конфиг по умолчанию
```
"max-age": 28000,
"public": true
```

При разработке нужно переопределять значение взятое по умолчанию. Используется либо значение on() для запрета кэширования либо значения pub() когда кэшировать явно можно, обычно для статики генерируемой php. 

Метод off() хотя и декларируется но вызывать его нельзя. Согласно описываемого порядка работы с кэшем.

С точки зрения скрипта установлено no-cache или public не важно. Оба эти варианта разрешают кэшировать. кэш no-cache управляется загловками If-Modified, а с Public запросы совсем не приходят, как будто If-Modified заранее известно что будет false. И только no-store действительно запрещает кэш и содержимое будет собираться снова без условий. Nostore::check и Nostore::is нужны чтобы позволить всплыть заголовку no-store и запретить кэш всем кто зависит от no-store содержимого, которое может попасть внутрь public содержимого и заменить соответственно public на no-store.

### Nostore::is
Проверяет, есть ли в списке отправленных заголовков, заголовок 'Cache-control' с переданным значением 'no-store'. Если есть, то возвращает true, иначе возвращает false.

> Nostore::is();

```
header('Cache-Control: public');
$res = Nostore::is();
assert(false === $res);
header('Cache-Control: no-store');
$res = Nostore::is();
assert(true === $res);
```

### Nostore::pub
Если отсутствует заголовок 'Cache-control' со значением 'no-store', то данный метод устанавливает заголовок 'Cache-control' со значением max-age, которое определено в конфигурации системы. По умолчанию данное значения равно 5 часам.

> Nostore::pub();

```
header('Cache-Control: no-store');
Nostore::pub();

***
Response Headers
Cache-Control: no-store
***

header('Cache-Control: no-cache');
Nostore::pub();

***
Response Headers
Cache-Control: max-age=18000, public
***
```

### Nostore::init
Если в конфигурации системы свойство public установлено в значение true и если отсутствует заголовок 'Cache-control' со значением 'no-store', метод установит заголовок 'Cache-control' со значением max-age, которое определено в конфигурации системы. По умолчанию данное значения равно 5 часам. 
Если свойство public будет равно false, то метод установит значение заголовка 'Cache-control' равное 'no-cache'.

> Nostore::init();

```
Nostore::$conf['public'] = true;
header('Cache-Control: no-cache');
Nostore::init();

***
Response Headers
Cache-Control: max-age=18000, public
***

header('Cache-Control: no-store');
Nostore::init();

***
Response Headers
Cache-Control: no-store
***

Nostore::$conf['public'] = false;
Nostore::init();

***
Response Headers
Cache-Control: no-cache
***
```

### Nostore::isPub
Метод ищет в списке заголовков заголовок 'Cache-control' со значением 'public'.
Если такое значение будет найдено, то метод вернет false, иначе метод вернет true.

> Nostore::isPub();

```
header('Cache-Control: max-age=18000, public');
Nostore::isPub();

***
true
***

header('Cache-Control: no-cache');
Nostore::isPub();

***
false
***
```

### Nostore::on
Метод устанавливает заголовку 'Cache-control' значение 'no-store'

>Nostore::on();

```
header('Cache-Control: max-age=18000, public');
Nostore::on()

***
Response Headers
Cache-Control: no-store
***
```

### Nostore::off
Метод не рекомендуется использовать отдельно.
no-cache или public выбирается только общим конфигом. И не рекомендуеся вызывать off() отдельно.
Устанавливает заголовку 'Cache-control' значение 'no-cache'

> Nostore::off();

### Nostore::check
Метод проверяет, включился ли кэш в выполняемой функции $callback.

> Nostore::check($callback);

```
header('Cache-control: no-cache');
Nostore::check( function (){
    header('Cache-Control: no-cache');
});

***
false
***

header('Cache-control: no-cache');
Nostore::check( function (){
    header('Cache-Control: no-store');
});

***
true
***

header('Cache-control: no-store');
Nostore::check( function (){
    header('Cache-Control: no-cache');
});

***
false
***

header('Cache-control: no-store');
Nostore::check( function (){
    header('Cache-Control: no-store');
});

***
true
***
```
