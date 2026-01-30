# Распределение параметров запроса.

> Работает через [Reflection](https://www.php.net/manual/ru/book.reflection.php)

Легковесное создание распределение параметров запроса в существующие DTO.

## Установка

* Добавить в `composer.json` текущий репозиторий.

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:Lavreek/Symfony.RequestReceiver.git"
    }
  ] 
}
```

* Вызвать команду `composer require lavreek/request-receiver:dev-master`

## Использование

Использование тестировалось только в фреймворке Symfony 6, 7.

### Создание Request DTO 

Создание объекта передачи данных.

Пример запроса:

```json
{
  "token": "eyJ.eyJ.ZDQ"
}
```

Передаваемые параметры поддерживают древо при условии, что все элементы наследуются от одинакового родителя.

TestRequest.php:

```php
<?php

namespace App\DTO;

use LAVREEK\Request\Library\RequestReceive;

/**
 * Запрос параметров необходимых для валидации токена.
 */
class TestRequest extends RequestReceive
{
    /** @var string Проверяемый токен. */
    public string $token;
}
```

```php
<?php

final class ApiController extends AbstractController
{
    #[Route('/jwt/validate', name: '_jwt_validate', methods: ['POST'])]
    public function validate(TestRequest $request): JsonResponse
    {
        // $request->token; // Использование доступного свойства класса.
        // $request->toArray(); // Получение всех параметров объекта в виде массива.
    }
}
```

В результате данного запроса, параметр запроса `token` попадёт в `ПУБЛИЧНОЕ` свойство класса `TestRequest` -> `token`.

> На текущем этапе разработки `public` является обязательным условием использования.

#### Древовидная структура данных.

Использование древовидной структуры не совсем очевидно для рядового пользователя т.к. оно использует иное распределение.
`Request DTO` используется для создания `Request` из глобального состояния. 

```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjIsImlhdCI6MTc2OTczMDE2MX0.ZDQ0YmZiODYzYThhYjI0NjlmOTRiYTJjZTU4YjZjYmIxYTljZThhMTE3MTVhYWQ1MGJkOTUyYzNiZGJjYTU5Yw",
    "meta": {
        "name": "name",
        "address": {
            "legal_address": "address"
        }
    }
}
```

При текущей структуре все последующие объекты в `RequestReceive` должны будут иметь зависимость от `VariableReceive`.

Повтор TestRequest.php:

```php
<?php

namespace App\DTO;

use LAVREEK\Request\Library\RequestReceive;

/**
 * Запрос параметров необходимых для валидации токена.
 */
class TestRequest extends RequestReceive
{
    /** @var string Проверяемый токен. */
    public string $token;

    /** @var MetaRequest Метаданные пользователя. */
    public MetaRequest $meta;
}

```

MetaRequest.php:

```php
<?php

namespace App\DTO;

use LAVREEK\Request\Library\VariableReceive;

/**
 * Запрос параметров необходимых для валидации токена.
 */
class MetaRequest extends VariableReceive
{
    /** @var string Имя. */
    public string $name;

    /** @var null|string Фамилия. */
    public ?string $surname = null;

    /** @var AddressRequest Адрес пользователя */
    public AddressRequest $address;
}

```

AddressRequest.php:

```php
<?php

namespace App\DTO;

use LAVREEK\Request\Library\VariableReceive;

/**
 * Запрос параметров необходимых для валидации адреса.
 */
class AddressRequest extends VariableReceive
{
    /** @var string Адрес проживания. */
    public string $legal_address;

    /** @var null|string Фактический адрес. */
    public ?string $fact_address = null;
}

```

#### Использование метода toArray()

При использовании метода toArray от экземпяра `RequestReceive` можно получить весь объект в виде массива.

Пример ответа dd():

```php
array:2 [
  "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjIsImlhdCI6MTc2OTczMDE2MX0.ZDQ0YmZiODYzYThhYjI0NjlmOTRiYTJjZTU4YjZjYmIxYTljZThhMTE3MTVhYWQ1MGJkOTUyYzNiZGJjYTU5Yw"
  "meta" => array:3 [
    "name" => "name"
    "surname" => null
    "address" => array:2 [
      "legal_address" => "address"
      "fact_address" => null
    ]
  ]
]
```