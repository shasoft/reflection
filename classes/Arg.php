<?php

namespace Shasoft\Reflection;

use Shasoft\Support\Php;

// Аргумент функции/метода
class Arg
{
    // Имя
    protected string $name;
    // Все типы
    protected array $types;
    //
    protected string $default;
    protected bool $isDefaultValueAvailable;
    protected bool $isDefaultValueConstant;
    protected ?string $defaultValueConstantName;
    // Конструктор
    public function __construct(\ReflectionParameter $param)
    {
        $this->name = $param->name;
        $this->types = Types::getType($param->getType());
        $this->isDefaultValueAvailable = $param->isDefaultValueAvailable();
        if ($this->isDefaultValueAvailable) {
            $this->default = var_export($param->getDefaultValue(), true);
            $this->isDefaultValueConstant = $param->isDefaultValueConstant();
            if ($this->isDefaultValueConstant) {
                $this->defaultValueConstantName = $param->getDefaultValueConstantName();
            }
        }
    }
    // Имя
    public function name()
    {
        return $this->name;
    }
    // Типы значений
    public function types(): array
    {
        return $this->types;
    }
    // Тип
    public function type(string $delim = '|'): string
    {
        return implode($delim, $this->types());
    }
    // Есть значение по умолчанию?
    public function hasDefault(): bool
    {
        return $this->isDefaultValueAvailable;
    }
    // Значение по умолчанию
    public function default(): string
    {
        return $this->default;
    }
    // Значение по умолчанию для PHP
    public function defaultPhp(): string
    {
        // Значение задано PHP константой?
        if ($this->isDefaultValueConstant) {
            return '\\' . $this->defaultValueConstantName;
        }
        return $this->default();
    }
    // Типы JSON?
    public function hasJson(): bool
    {
        return Types::hasJson($this->types());
    }
}
