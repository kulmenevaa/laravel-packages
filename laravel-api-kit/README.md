# laravel-packages/laravel-api-kit

Переиспользуемый набор для JSON API на Laravel: базовый `ApiController` и
единый envelope ошибок с числовым `errorCode`. Извлечён из этого проекта —
ничего доменного (модели, enum'ы приложения) внутри пакета нет, поэтому он
подключается в любой Laravel 10–13 проект как локальный `path`-пакет.

## Что внутри

- `Local\LaravelApiKit\Http\ApiController` — тонкий базовый контроллер:
  `respondJson()`, `respondNoContent()`, типизированный `authUser()`.
- `Local\LaravelApiKit\Exceptions\ApiException` — абстрактный класс для
  доменных HTTP-исключений (`errorCode()`, `errors()`, `params()`,
  `getStatusCode()`).
- `Local\LaravelApiKit\Exceptions\ApiErrorCode` — интерфейс
  (`extends \BackedEnum`), под который заводится числовой enum ошибок
  конкретного приложения.
- `Local\LaravelApiKit\ApiErrorResponse` — статический билдер единого
  envelope: `{ errorCode, message, errors?, params? }`.
- `Local\LaravelApiKit\ApiExceptionRenderer` — регистрирует `render()`-колбэки
  для `ApiException`, `ValidationException`, `AuthenticationException` и
  прочих `HttpExceptionInterface` в `bootstrap/app.php`.
- `Local\LaravelApiKit\Contracts\FallbackErrorCodeResolver` — интерфейс,
  которым приложение сообщает пакету, в какие свои коды маппить исключения
  фреймворка, которые пакет не оборачивал сам (`Unauthenticated`,
  `ValidationFailed`, статусы 403/404/429/...).

## Установка в проект

В `composer.json` приложения:

```json
{
    "repositories": [
        { "type": "path", "url": "packages/local/laravel-api-kit" }
    ],
    "require": {
        "local/laravel-api-kit": "@dev"
    }
}
```

```shell
composer require local/laravel-api-kit:@dev
```

Composer создаст симлинк `vendor/local/laravel-api-kit` → `packages/local/laravel-api-kit`,
так что правки пакета сразу видны приложению без переустановки.

## Использование

1. Заведите числовой enum ошибок приложения, реализующий `ApiErrorCode`:

```php
enum ErrorCodeEnum: int implements ApiErrorCode
{
    case VALIDATION_FAILED = 1004;
    // ...

    public function translationKey(): string
    {
        return 'api.errors.' . mb_strtolower($this->name);
    }
}
```

2. Базовые исключения приложения — через `extends ApiException`, с
   `errorCode(): ApiErrorCode` и `protected function statusCode(): int`.

3. Базовый контроллер API — через `extends ApiController` пакета (можно
   оставить тонкую прослойку в `app/Http/Controllers/Api/ApiController.php`,
   если нужен доп. хелпер уровня приложения).

4. В `bootstrap/app.php`:

```php
->withExceptions(function (Exceptions $exceptions): void {
    ApiExceptionRenderer::register($exceptions, new AppFallbackErrorCodeResolver());

    // Специфичные для приложения/сторонних пакетов исключения — до
    // регистрации пакетом общего HttpExceptionInterface-колбэка, если они
    // тоже реализуют HttpExceptionInterface (колбэки уходят по порядку
    // регистрации, первый non-null результат побеждает).
})
```

где `AppFallbackErrorCodeResolver implements FallbackErrorCodeResolver`
маппит `unauthenticated()` / `validationFailed()` / `forHttpStatus()` на
конкретные кейсы `ErrorCodeEnum` приложения.

## Границы пакета

Пакет не знает о домене приложения (нет `User`, нет конкретных кодов ошибок,
нет OAuth/Passport). Всё специфичное — в самом приложении, пакет даёт только
механизм: интерфейсы + рендер envelope. Это осознанно, чтобы пакет
переносился между проектами без правок.
