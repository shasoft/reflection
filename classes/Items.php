<?php

namespace Shasoft\Reflection;

use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;


// Работа с элементами
class Items
{
    // Константы
    public const C_CLASS     = 1;
    public const C_INTERFACE = 2;
    public const C_TRAIT     = 4;
    public const C_ENUM      = 8;
    public const C_FUNCTION  = 16;
    public const C_ALL       = self::C_CLASS | self::C_INTERFACE | self::C_TRAIT | self::C_ENUM;
    public const C_ALL_FN    = self::C_ALL | self::C_FUNCTION;
    // Получить список классов
    protected static ?Parser $parser = null;
    // Получить список элементов файла
    public static function list(string $content, int $options = self::C_ALL, bool $fExistsCheck = false): array|false
    {
        $ret = [];
        //s_dump($content);
        // Если парсер не создан
        if (is_null(self::$parser)) {
            // то создать его   
            self::$parser = (new ParserFactory)->createForNewestSupportedVersion();
        }
        //s_dd(self::$parser);
        // Получить AST дерево
        try {
            $ast = self::$parser->parse($content);
        } catch (\Exception $e) {
            return false;
        }
        //s_dd($ast);
        // Создать объект для обхода
        $visitor = new class($options) extends NodeVisitorAbstract
        {
            public $namespace = '';
            public $items     = [];
            protected $options;
            // Конструктор
            public function __construct($options)
            {
                // Отключить типы, которые не поддерживаются в данной версии PHP
                if (!function_exists('enum_exists')) {
                    $options &= ~Items::C_ENUM;
                }
                //
                $this->options = $options;
            }
            //
            public function enterNode(Node $node)
            {
                if ($node instanceof Namespace_) {
                    // Clean out the function body
                    if (!is_null($node->name)) {
                        $this->namespace = $node->name->toString();
                    }
                } else {
                    $fn_exists = false;
                    if (($this->options & Items::C_CLASS) && $node instanceof Class_) {
                        $fn_exists = 'class_exists';
                    } else if (($this->options & Items::C_INTERFACE) && $node instanceof Interface_) {
                        $fn_exists = 'interface_exists';
                    } else if (($this->options & Items::C_TRAIT) && $node instanceof Trait_) {
                        $fn_exists = 'trait_exists';
                    } else if (($this->options & Items::C_ENUM) && $node instanceof Enum_) {
                        $fn_exists = 'enum_exists';
                    } else if (($this->options & Items::C_FUNCTION) && $node instanceof Function_) {
                        $fn_exists = 'function_exists';
                    }
                    // Это нужный нам узел?
                    if ($fn_exists !== false) {
                        // И имя определено?
                        if (!is_null($node->name)) {
                            $this->items[] = [
                                'name' => (string) $node->name, //->toString();
                                'fn_exists' => $fn_exists,
                            ];
                        }
                    }
                }
            }
        };
        //
        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $ast = $traverser->traverse($ast);
        //
        if (!empty($visitor->namespace)) {
            $visitor->namespace .= '\\';
        }
        // Перебрать все найденные классы
        foreach ($visitor->items as $item) {
            // Полное имя
            $classnameFull = $visitor->namespace . $item['name'];
            //
            if ($item['fn_exists'] == 'function_exists') {
                try {
                    //s_dd($item['fn_exists']($classnameFull), $classnameFull);
                    // Проверить существование (с использованием функции для текущего типа)
                    if ($item['fn_exists']($classnameFull) || $fExistsCheck) {
                        // Записать значение
                        $ret[$classnameFull] = $item['fn_exists'];
                    }
                } catch (\Exception $e) {
                    //var_dump('Exception', $e->getMessage());
                } catch (\Error $e) {
                    //var_dump('Error', $e->getMessage());
                }
            } else {
                //s_dump($classnameFull, $item['fn_exists']);
                //s_dump($item['fn_exists']($classnameFull, true));

                try {
                    // Проверить существование (с использованием функции для текущего типа)
                    if ($fExistsCheck || $item['fn_exists']($classnameFull, true)) {
                        // Записать значение
                        $ret[$classnameFull] = $item['fn_exists'];
                    }
                } catch (\Exception $e) {
                    //var_dump('Exception', $e->getMessage());
                } catch (\Error $e) {
                    //var_dump('Error', $e->getMessage());
                } catch (\Throwable $e) {
                    var_dump('Throwable', $e->getMessage());
                }
            }
        }
        // Вернуть список классов
        return $ret;
    }
    // Получить список классов файла
    public static function fileList(string $filepath, int $options = self::C_ALL, bool $fExistsCheck = false): array
    {
        $ret = [];
        // Файл существует?
        if (file_exists($filepath)) {
            // Читать список классов
            $ret = self::list(file_get_contents($filepath), $options, $fExistsCheck);
        }
        return $ret;
    }
    // Проверить наличие объекта
    public static function exists(string $name, string $fn_exists, bool $autoload = true): bool
    {
        if ($fn_exists == 'function_exists') {
            return function_exists($name);
        }
        return $fn_exists($name, $autoload);
    }
}
