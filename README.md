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