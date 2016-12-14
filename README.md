[![Latest Stable Version](https://poser.pugx.org/infrajs/nostore/v/stable)](https://packagist.org/packages/infrajs/nostore) [![Total Downloads](https://poser.pugx.org/infrajs/nostore/downloads)](https://packagist.org/packages/infrajs/nostore)

# Управление кэшем браузера

## Установка через composer
```json
{
	"require":{
		"infrajs/nostore":"~1"
	}
}
```
## Ипользование
```php
Nostore::init();
```
Будут выставлены заголовко Cache-Controll согласно конфига ```php Nostore::$conf```
```
public static $conf=array(
		"max-age" => 86400, //24 часа, время кэша, когда public:true, для динамики
		"max-age-stat" => 2419200, //4 недели, время кэша когда public:true и вызван Nostore::pubStat() для статики
		
		//План обновлений сайта программистом. В Expires будет подставляться следующая непрошедшая дата
		"expires-year" => [ //dd.mm
			'05.03','10.03',
			'25.01','01.01','18.01',
			'18.02','25.02'],
		"expires-month" => [],//Дата месяца 1,20
		"expires-str" => [], //'next monday'
		"public" => true, //expires работает только с этим ключём
		"port" => array( //указанные файлы можно загрузить по адресу vendor/infrajs/nostore/?port=watch
			"watch" => "https://mc.yandex.ru/metrika/watch.js",
			"twitter" => "http://platform.twitter.com/widgets.js"
		)
	);
```
## Тестирование
После установки откройте в браузере vendor/infrajs/nostore/tester.php
## Описание
В Конфиге указываются даты плановых обновлений сайта.
Например, перед 8 марта меняется статический баннер, заменой файла, по этому указана дата 05.03 и у посетителя, который зайдёт 1 марта на сайт кэш установится не на 4 недели, а только на 4 дня, до 5 марта.

```json
{
	"expires-year": ["05.03","10.03","25.01","01.01","18.01","18.02","25.02"],
	"expires-month": [1],
	"expires-str": ["next friday"]
}
```

**expires-str** работает по правилам функции [strtotime](http://php.net/manual/ru/function.strtotime.php).
Указано **next friday** значит, что кэш всегда устанавливается до слеюущей пятницы, вместо 2х недель по умолчанию.

Динамикой Php файл считается по умолчанию, когда вызван Nostore::init() для того чтобы установился кэш как для статики нужно отедльно вызвать Nostore::pubStat().
Например, [imager/index.php](https://github.com/infrajs/imager) скрипт меняющий картинки ведёт себя также как и статика,
отправляя соответсвующие заголовки.

Все изменения, которые делает администора сайта обычно относятся к изменениям динамических данных, которые применяются в течении 24 часов. 

Действия администрора по замене одного файла на другой с сохранением имени попадает под изменение статики, **если на сайте используется прямая ссылка на файл**, 
Изменение статики не применится в течении 4х недель или согласно какой-то более близкой даты из настрое конфига expires...


# Управление кэшем браузера

3 режима

- **no-store**. Содержимое, может быть изменено в непредсказуемый момент. Содержимое меняется с изменениями пользователя. Новые данные из базы данных. Работа с сессией. Содержимое зависит от isAdmin() isDebug() isTest(). Значит такое содержимое нельзя кэшировать и использовать кэш для разных пользователей.
- **no-cache**. Содержимое, может быть изменено администратором и администратору нужно сразу увидеть результат. Данные кэшируем, но всегда проверяем. Содержимое, может быть изменено в предсказуемый момент. Кэш по дате изменения файла.
- **public + Expires** - Кэш в браузере разрешён. По умолчанию этот режим включён Nostore::$conf['public'] = true;


```php
Nostore::on(); //no-store
Nostore::off(); //no-cache
Nostore::pub(); //public
Nostore::is(); //true - если заголовок Cache-Controll no-store и кэширование полностью запрещено
Nostore::isPub(); //true - если заголовок Cache-Controll public
```

## Cache-Control

По умолчанию ответ web-сервера Apache, обычно, не содержит заголовков управляющих кэшем. 

```php
Nostore::init(); //Выставляются заголовки по умолчанию согласно конфига
```

- 'public':true - кэширование public включено.
- 'public':false - обновления на сайте слишком частые и посетители возвращаются на сайт очень часто за новым содержанием, барузер будет всегда отправлять запрос к серверу за новым содержимым.

Для public true необходимо добавить аналогичную запись и в .htaccess для активации такого же уровня кэша для статики и файлов, которые идут в обход php.
```
#2419200 4 недели
<FilesMatch "\.(woff|jpeg|jpg|png|gif|ico|js|tpl|json|html)$">
	Header set Cache-Control "max-age=2419200, public"
</FilesMatch>
```

Для статики генерируемой php нужно добавять вызов ```Nostore::pubStat()```
С точки зрения скрипта установлено no-cache или public не важно. Оба эти варианта разрешают кэшировать. Но кэш no-cache управляется загловками If-Modified, а с Public запросы совсем не приходят, как будто If-Modified заранее известно, что будет false.
И только no-store действительно запрещает кэш и содержимое будет собираться снова без условий. 
Nostore::check и Nostore::is нужны, чтобы позволить всплыть заголовку no-store и запретить кэш всем кто зависит от no-store содержимого, которое может попасть внутрь public содержимого и заменить соответственно public на no-store.

### Nostore::is()
Проверяет, есть ли в списке отправленных заголовков, заголовок 'Cache-control' с переданным значением 'no-store'. Если есть, то возвращает true, иначе возвращает false.

```
header('Cache-Control: public');
$res = Nostore::is();
assert(false === $res);
header('Cache-Control: no-store');
$res = Nostore::is();
assert(true === $res);
```

### Nostore::pub()
Если отсутствует заголовок 'Cache-control' со значением 'no-store', то данный метод устанавливает заголовок 'Cache-control' со значением Expire - max-age, которое определено в конфиге.

```php
Nostore::pub();
```

### Nostore::pubStat()
Если отсутствует заголовок 'Cache-control' со значением 'no-store', то данный метод устанавливает заголовок 'Cache-control' со значением Expire - max-age-stat, которое определено в конфиге.

```php
Nostore::pub();
```

### Nostore::init()
Если config.public = true, метод установит заголовок Cache-control: public и Expire: conf.max-age
Если свойство public будет равно false, то метод установит значение заголовка Cache-control: no-cache.

> Nostore::init();


```php
Nostore::$conf['public'] = true;
Nostore::init();
//Response Headers
//Cache-Control: max-age=18000, public
Nostore::$conf['public'] = false;
Nostore::init();
//Response Headers
//Cache-Control: no-cache
```


### Nostore::isPub()
Метод ищет в списке заголовков заголовок 'Cache-control' со значением 'public'.
Если такое значение будет найдено, то метод вернет false, иначе метод вернет true.


```php
header('Cache-Control: max-age=18000, public');
Nostore::isPub(); //true
header('Cache-Control: no-cache');
Nostore::isPub(); //false
```

### Nostore::on()
Метод устанавливает заголовку 'Cache-control' значение 'no-store'
```php
header('Cache-Control: max-age=18000, public');
Nostore::on()
//Response Headers
//Cache-Control: no-store
```

### Nostore::off()
no-cache или public выбирается только общим конфигом. И не рекомендуеся вызывать off() отдельно.
если conf.public=true вызвается Nostore::pub();
если conf.public=false Устанавливает заголовку 'Cache-control' значение 'no-cache'

```php
Nostore::off();
```

### Nostore::check($callback)
Метод проверяет, запрещается ли кэш в выполняемой функции $callback заголовком no-store. Считается, что заголовки no-cache и public разрешают кэширование также как и отсутствие заголовка Cache-Controll.

```php
header('Cache-control: no-cache');
$is = Nostore::check( function (){
	header('Cache-Control: no-cache');
}); //false
```

```php
header('Cache-control: no-cache');
$is = Nostore::check( function (){
	header('Cache-Control: no-store');
}); //true
```

```php
header('Cache-control: no-store');
$is = Nostore::check( function (){
	header('Cache-Control: no-cache');
}); //false
```

```php
header('Cache-control: no-store');
$is = Nostore::check( function (){
	header('Cache-Control: no-store');
}); //true
```

## Кэширование файлов из интерета config.port
Для случая когда удалённый сервер не выставляет заголовков кэширования и доступа к этому серверу нет.
В конфиге config.port регистрируются файлы, который можно будет загрузить по адресу **/vendor/infrajs/nostore/?port=watch**
Если настроен [infrajs/router](https://github.com/infrajs/router) **-nostore/?port=watch** по новому адресу будут выставлены заголовки кэширования как для статики.

```json
{
	"port":{
		"watch": "https://mc.yandex.ru/metrika/watch.js",
		"twitter": "http://platform.twitter.com/widgets.js"
	}
}
```


## Аргумент ?-nostore=true
Если в скрипте используется Nostore::init() в infrajs вместе с router этот вызов происходит всегда. При наличии аргумента -nostore=true ответ будут с заголовком запрещающим кэширование. Таким образом любой адрес можно открыть без public кэша по адресу. Это максимально лёгкий способ игнорировать локальный кэш и только. Запрос придёт на сервер и серверный кэш сработает по своим обычной логике. 

> Аргумент ```?-nostore=true``` гарантирует что запрос дойдёт до сервера.

Если кэширование в бразуере не используется, то итак все запросы доходят до сервера. Актуально для сайта работающего на продакшиен с полной оптимизацией. Это ключ несмотря на полное кэширование сайта позволяет вносить изменение и сразу видеть изменения. Используется с [infrajs/env](https://github.com/infrajs/env)
