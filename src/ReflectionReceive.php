<?php

namespace LAVREEK\Request\Library;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Класс взаимодействия со структурой класса.
 */
abstract class ReflectionReceive
{
    /**
     * Получить данные о свойстве.
     * @param string $property Заданное свойство
     * @return ReflectionProperty
     * @throws ReflectionException
     */
    protected function getReflectionProperty(string $property): ReflectionProperty
    {
        return $this->getReflection()->getProperty($property);
    }

    /**
     * Получить свойства класса.
     * @param int|null $filter Фильтр свойств.
     * @return array
     */
    protected function getReflectionProperties(?int $filter = ReflectionProperty::IS_PUBLIC): array
    {
        return $this->getReflection()->getProperties($filter);
    }

    /**
     * Получить инструмент взаимодействия с классом.
     * @return ReflectionClass
     */
    private function getReflection(): ReflectionClass
    {
        return new ReflectionClass($this);
    }
}
