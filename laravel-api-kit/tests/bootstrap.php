<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

/**
 * Test-only stand-ins for the global helpers this package calls
 * (`request()` in ApiController, `__()` in ApiErrorResponse, `response()`
 * pulled in transitively by `JsonResource::response()`) that actually live
 * in illuminate/foundation and illuminate/routing — packages this library
 * deliberately does not require (see the docblocks on
 * ApiController::authUser() and ApiErrorResponse). A real consuming app
 * supplies the genuine Laravel helpers; these shims exist only so the
 * package's own logic can be exercised in isolation, without bootstrapping a
 * full application.
 */

if (!function_exists('request')) {
    function request(): \Illuminate\Http\Request
    {
        return \Local\LaravelApiKit\Tests\Support\FakeRequest::current();
    }
}

if (!function_exists('__')) {
    /**
     * @param  array<string, string>  $replace
     */
    function __(?string $key = null, array $replace = [], ?string $locale = null): string
    {
        return \Local\LaravelApiKit\Tests\Support\FakeTranslator::line($key) ?? (string) $key;
    }
}

if (!function_exists('response')) {
    /**
     * `JsonResource::response()` calls this with no arguments to get a
     * factory exposing `json()` — enough of
     * `Illuminate\Contracts\Routing\ResponseFactory` for that one call site.
     */
    function response(): object
    {
        return new class {
            public function json(mixed $data = [], int $status = 200, array $headers = []): \Illuminate\Http\JsonResponse
            {
                return new \Illuminate\Http\JsonResponse($data, $status, $headers);
            }
        };
    }
}
