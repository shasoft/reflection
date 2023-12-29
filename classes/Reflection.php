<?php

namespace Shasoft\Reflection;

class Reflection
{
    // Получить код сигнатуры метода
    public static function getSignatureMethod(\ReflectionMethod $refMethod): string
    {
        $ret = '';
        if ($refMethod->isStatic()) $ret .= ' static';
        if ($refMethod->isPrivate()) $ret .= ' private';
        if ($refMethod->isProtected()) $ret .= ' protected';
        if ($refMethod->isPublic()) $ret .= ' public';
        // Аргументы
        $args = [];
        foreach ($refMethod->getParameters() as $refArg) {
            $arg_s = '';
            $types = Types::getType($refArg->getType());
            if (!empty($types)) {
                $arg_s = implode('|', $types) . ' ';
            }
            if ($refArg->isVariadic()) {
                $arg_s .= ' ...';
            }
            $arg_s .= '$' . $refArg->getName();
            if ($refArg->isDefaultValueAvailable()) {
                if ($refArg->isDefaultValueConstant()) {
                    $arg_s .= ' = ' . $refArg->getDefaultValueConstantName();
                } else {
                    $arg_s .= ' = ' . var_export($refArg->getDefaultValue(), true);
                }
            }
            $args[] = $arg_s;
        }
        // Прототип
        $ret .= ' function ' . $refMethod->getName() . '(' . implode(', ', $args) . ')';
        // Возвращаемый тип
        if ($refMethod->hasReturnType()) {
            $ret .= ' : ' . implode('|', Types::getType($refMethod->getReturnType()));
        }
        return trim($ret);
    }
    // Получить свойство объекта
    static private array $cacheProperty = [];
    static public function getObjectProperty(object $object, string $name): ?\ReflectionProperty
    {
        $refRet = null;
        $refClass = new \ReflectionClass($object);
        while (is_null($refRet) && $refClass !== false) {
            // Свойство существует?
            if ($refClass->hasProperty($name)) {
                $refRet = $refClass->getProperty($name);
            } else {
                $refClass = $refClass->getParentClass();
            }
        }
        // Установить возможность доступа к защищенным и приватным свойствам
        $refRet->setAccessible(true);
        return $refRet;
    }
    // Получить метод объекта
    static public function getObjectMethod(object $object, string $name): ?\ReflectionMethod
    {
        $refRet = null;
        $refClass = new \ReflectionClass($object);
        while (is_null($refRet) && $refClass !== false) {
            // Свойство существует?
            if ($refClass->hasMethod($name)) {
                $refRet = $refClass->getMethod($name);
            } else {
                $refClass = $refClass->getParentClass();
            }
        }
        // Установить возможность доступа к защищенным и приватным методам
        if (is_null($refRet)) {
            s_dd($object, $refRet);
        }
        $refRet->setAccessible(true);
        //
        return $refRet;
    }
    // Получить значение защищенного/приватного свойства объекта
    static public function getObjectPropertyValue(object $object, string $name, mixed $default): mixed
    {
        //*
        $refProperty = null;
        $refClass = new \ReflectionClass($object);
        while (is_null($refProperty) && $refClass !== false) {
            // Свойство существует?
            if ($refClass->hasProperty($name)) {
                $refProperty = $refClass->getProperty($name);
            } else {
                $refClass = $refClass->getParentClass();
            }
        }
        //*/
        $refProperty = self::getObjectProperty($object, $name);
        if (is_null($refProperty)) {
            return $default;
        }
        return $refProperty->getValue($object);
    }
}
