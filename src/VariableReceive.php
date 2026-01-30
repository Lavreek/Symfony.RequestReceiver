<?php

namespace LAVREEK\Request\Library;

use ReflectionException;

/**
 * Класс взаимодействия с классом для распределения полученных параметров.
 */
abstract class VariableReceive extends ReflectionReceive
{
    /**
     * Инициализация распределения.
     * @param array ...$arguments Параметры полученные для распределения.
     * @throws ReflectionException
     */
    public function __construct(...$arguments)
    {
        foreach ($arguments as $argumentKey => $argumentValue) {
            if (property_exists($this, $argumentKey)) {
                $property = $this->getReflectionProperty($argumentKey);

                if (class_exists($property->getType()->getName())) {
                    $this->$argumentKey = new ($property->getType()->getName())(...$argumentValue);

                } else {
                    $this->$argumentKey = $argumentValue;
                }
            }
        }
    }

    /**
     * Получить свойства класса в виде массива данных.
     * @return array
     */
    public function toArray(): array
    {
        $array = [];

        foreach ($this->getReflectionProperties() as $property) {
            $propertyName = $property->getName();

            if (is_object($this->$propertyName)) {
                if (in_array(VariableReceive::class, class_parents($this->$propertyName))) {
                    $array[$propertyName] = $this->$propertyName->toArray();

                } else {
                    $array[$propertyName] = $this->$propertyName;
                }
            } else {
                $array[$propertyName] = $this->$propertyName;
            }
        }

        return $array;
    }
}
