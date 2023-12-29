# Расширенные функции для рефлексии в PHP

## Доступ к защищенным и приватным свойствам

```php
class MyObject{
    function __construct(private int $prop) {

    }
}
$obj = new MyObject(123);
// Создать прокси объект
$proxyObj = new FriendProxy($obj);
// Вывести приватное свойство
echo $proxyObj->prop;
```
*Вывод*
123


## Получить список элементов (сущностей) файла

```php
class TestClass1
{
}
class TestClass2
{
    public static function print() : void {
        $items = Items::list(__FILE__);
        var_dump($items);
    }
}
TestClass2::print();
```
*Вывод*
```php
[
    'TestClass1',
    'TestClass2',
]
```
