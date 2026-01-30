<?php

namespace LAVREEK\Request\Library;

use Exception;
use ReflectionException;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

/**
 * Класс взаимодействия с параметрами Symfony Request.
 */
abstract class RequestReceive extends VariableReceive
{
    /** @var true Константа стандартной валидации параметров. */
    const DEFAULT_VALIDATION = true;

    /** @var Request|null Экземпляр Request. */
    private ?Request $request = null;

    /**
     * Инициализация распределения параметров полученных через Request.
     * @throws ReflectionException
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct(...$this->getRequestArray());

        if ($this->isValid()) {
            $this->validate(); // Провести стандартную валидацию данных.
        }
    }

    /**
     * Получить и инициализировать экземпляр Request.
     * @return Request|null
     */
    public function getRequest(): ?Request
    {
        if ($this->request === null) {
            $this->request = Request::createFromGlobals();
        }

        return $this->request;
    }

    /**
     * Получить заголовки экземпляра Request.
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->getRequest()->headers->all();
    }

    /**
     * Передача дополнительной мета информации.
     * @return InputBag
     */
    public function getPayload(): InputBag
    {
        return $this->getRequest()->getPayload();
    }

    /**
     * Произвести валидацию параметров дочернего класса с параметрами полученными из экземпляра Request.
     * @throws Exception
     */
    private function validate(): void
    {
        $attributes = $this->getReflectionProperties();

        foreach ($attributes as $attribute) {
            if (!$this->checkAttributeRequirements($attribute)) {
                throw new Exception(sprintf('Ошибка в валидации параметра: "%s"', $attribute->getName()));
            }
        }
    }

    /**
     * Проверка атрибута свойства дочернего класса.
     * @param ReflectionProperty $attribute Заданный атрибут.
     * @return bool
     */
    private function checkAttributeRequirements(ReflectionProperty $attribute): bool
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

    /**
     * Получить данные полученные созданием экземпляра Request.
     * @return array
     */
    private function getRequestArray(): array
    {
        return $this->getRequest()->toArray();
    }

    /**
     * Необходимость проверки валидации.
     * @return bool
     */
    private function isValid(): bool
    {
        return self::DEFAULT_VALIDATION;
    }
}
