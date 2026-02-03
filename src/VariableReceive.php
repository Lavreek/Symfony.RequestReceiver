<?php

namespace LAVREEK\Library\Request;

use Symfony\Component\Validator\Validation;

/**
 * Класс взаимодействия с классом для распределения полученных параметров.
 */
abstract class VariableReceive extends ReflectionReceive
{
    /**
     * Инициализация распределения.
     * @param array ...$arguments Параметры полученные для распределения.
     * @throws \ReflectionException
     * @throws \Exception
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

        $this->validate();
    }

    /**
     * Произвести валидацию свойств.
     * @throws \Exception
     */
    private function validate(): void
    {
        array_map(
        /**
         * @param \ReflectionProperty $property Свойство класса.
         * @throws \Exception
         */
        function ($property) {
            try {
                $this->{$property->getName()}; // Проверка инициализации свойства.
            } catch (\Error $e) {
                throw new \Exception("Property \"{$property->getName()}\" is not initialized.");
            }

            // Атрибуты свойств класса.
            //if (!$this->checkAttributeRequirements($property)) {
            //    throw new \Exception(sprintf('Ошибка в валидации параметра: "%s"', $property->getName()));
            //}
        }, $this->getReflectionProperties());
    }

    /**
     * Проверка атрибута свойства дочернего класса.
     * @param \ReflectionProperty $attribute Заданный атрибут.
     * @return bool
     * @deprecated Проверка атрибутов свойств утратила работоспособность (До лучших времён).
     */
    private function checkAttributeRequirements(\ReflectionProperty $attribute): bool
    {
        $attributeName = $attribute->getName();
        $attributeValue = $this->$attributeName;

        $attributeRequirements = $attribute->getAttributes();

        if (empty($attributeRequirements)) {
            return true;

        } else {
            foreach ($attributeRequirements as $requirement) {
                $requirementName = $requirement->getName();

                $violations = Validation::createValidator()->validate($attributeValue, new $requirementName());
                if (count($violations) > 0) {
                    return false;
                }
            }
        }

        return true;
    }

    /** @inheritDoc */
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
