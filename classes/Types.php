<?php

namespace Shasoft\Reflection;

// Типы данных
class Types
{
    // Получить тип данных
    // Это стандартный тип?
    static private array $standardTypes = [
        'array' => 1,
        'callable' => 1,
        'bool' => 1,
        'int' => 1,
        'float' => 1,
        'string' => 1,
        'iterable' => 1,
        'void' => 1,
        'object' => 1,
        'null' => 1,
        'false' => 1,
        'mixed' => 1,
        'never' => 1,
        'true' => 1,
    ];
    public static function getType(\ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType|null $type): array
    {
        if (is_null($type)) {
            $ret = [];
        } else {
            $hasNull = false;
            $ret = array_map(function (string $typeItem) use (&$hasNull) {
                if (substr($typeItem, 0, 1) == '?') {
                    $typeItem = substr($typeItem, 1);
                    $hasNull = true;
                }
                if (!array_key_exists($typeItem, self::$standardTypes)) {
                    // это класс
                    $typeItem = '\\' . $typeItem;
                }
                return $typeItem;
            }, explode('|', (string)$type));
            if ($hasNull) {
                $ret[] = 'null';
            }
        }
        return $ret;
    }
    // Типы JSON?
    static private array $jsonTypes = [
        'array' => [],
        'bool' => true,
        'int' => 0,
        'float' => 0,
        'string' => '',
        'void' => '',
        'null' => null,
        'false' => false,
        'true' => true,
    ];
    static public function hasJson(array $types): bool
    {
        foreach ($types as $type) {
            if (!array_key_exists($type, self::$jsonTypes)) {
                return false;
            }
        }
        return true;
    }
    static public function jsonValue(string $type): mixed
    {
        return self::$jsonTypes[$type] ?? null;
    }
    // Тип пустой?
    public static function hasEmptyType(\ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType|null $type): bool
    {
        // Есть возвращаемый результат?
        $ret = true;
        if (!is_null($type)) {
            $typeString = (string)$type;
            if ($typeString == 'void') {
                $ret = false;
            }
        }
        return $ret;
    }
    // Получить список типов для каждого аргумента
    public static function getArgsTypes(\Closure $func): array
    {
        $ret = [];
        // Определим тип колонки
        if ($func instanceof \Closure) {
            $ref = new \ReflectionFunction($func);
        }
        $args = $ref->getParameters();
        foreach ($args as $arg) {
            $ret[] = self::getType($arg->getType());
        }
        // Вернуть типы
        return $ret;
    }
}
