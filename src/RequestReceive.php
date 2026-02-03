<?php

namespace LAVREEK\Library\Request;

use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Класс взаимодействия с параметрами Symfony Request.
 */
abstract class RequestReceive extends VariableReceive
{
    /** @var Request|null Экземпляр Request. */
    private ?Request $request = null;

    /**
     * Инициализация распределения параметров полученных через Request.
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct(...$this->getRequestArray());
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
     * Получить данные полученные созданием экземпляра Request.
     * @return array
     */
    private function getRequestArray(): array
    {
        return $this->getRequest()->toArray();
    }
}
