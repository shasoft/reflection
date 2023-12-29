<?php

namespace Shasoft\Reflection;

class FriendProxy
{
    // Рефлексия объекта
    protected \ReflectionClass $refClass;
    // Конструктор
    public function __construct(protected Object $obj)
    {
        $this->refClass = new \ReflectionClass($obj);
    }
    // Получить свойство
    private function ___getProperty(string $name): \ReflectionProperty
    {
        // Получить свойство
        $refProperty = null;
        $refClass = $this->refClass;
        while (is_null($refProperty) && !is_null($refClass)) {
            // Свойство существует?
            if ($refClass->hasProperty($name)) {
                $refProperty = $refClass->getProperty($name);
            } else {
                $refClass = $refClass->getParentClass();
            }
        }
        // Если свойство не нашлось
        if (empty($refProperty)) {
            // то кинуть исключение
            throw new \Exception("В классе " . $this->refClass->getName() . " не существует атрибута " . $name);
        }
        // Обеспечить доступ к защищённому или закрытому свойству
        $refProperty->setAccessible(true);
        // Вернуть свойство
        return $refProperty;
    }
    // Получить метод
    private function ___getMethod(string $name): \ReflectionMethod
    {
        // Получить свойство
        $refMethod = null;
        $refClass = $this->refClass;
        while (is_null($refMethod) && !empty($refClass)) {
            // Метод существует?
            if ($refClass->hasMethod($name)) {
                $refMethod = $refClass->getMethod($name);
            } else {
                $refClass = $refClass->getParentClass();
            }
        }
        // Если метод не нашёлся
        if (empty($refMethod)) {
            // то кинуть исключение
            throw new \Exception("В классе " . $this->refClass->getName() . " не существует метода " . $name);
        }
        // Обеспечить доступ к защищённому или закрытому свойству
        $refMethod->setAccessible(true);
        // Вернуть свойство
        return $refMethod;
    }
    // Получить объект
    public function getProxyObject(): Object
    {
        return $this->obj;
    }
    // Получить значение свойства
    public function __get(string $name): mixed
    {
        // Вернуть значение
        return $this->___getProperty($name)->getValue($this->obj);
    }
    // Установить значение свойства
    public function __set(string $name, mixed $value): void
    {
        // Установить значение
        $this->___getProperty($name)->setValue($this->obj, $value);
    }
    // Проверить наличие свойства
    public function __isset(string $name): bool
    {
        $ret = false;
        try {
            // Получить свойство
            $refProperty = $this->___getProperty($name);
            // Получить значение
            $val = $refProperty->getValue($this->obj);
            // Вызвать функцию
            $ret = empty($val);
        } catch (\Exception $e) {
        }
        return $ret;
    }
    // Вызвать метод объекта
    public function __call(string $name, array $arguments): mixed
    {
        // Установить значение
        return $this->___getMethod($name)->invoke($this->obj, ...$arguments);
    }
};

class MyObject
{
    function __construct(private int $prop)
    {
    }
}
