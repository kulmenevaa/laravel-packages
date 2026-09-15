<?php declare(strict_types=1);

namespace Local\LaravelApiKit;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Lang;
use Local\LaravelApiKit\Exceptions\ApiErrorCode;

/**
 * Builds the envelope every rendered API error shares:
 *
 *     { "errorCode": int|string, "message": string, "errors"?: object, "params"?: object }
 *
 * `message` is resolved from `$code->translationKey()` when that translation
 * exists, falling back to the caller-supplied message otherwise. Exposed as a
 * standalone helper (not just used internally by {@see ApiExceptionRenderer})
 * so a consuming app can build the exact same shape for its own render
 * callbacks — e.g. unwrapping a package-specific exception the kit doesn't
 * know about.
 */
final class ApiErrorResponse
{
    /**
     * @param  array<string, list<string>>  $errors
     * @param  array<string, scalar>  $params
     */
    public static function make(
        ApiErrorCode $code,
        string $fallbackMessage,
        int $status,
        array $errors = [],
        array $params = [],
    ): JsonResponse {
        $key = $code->translationKey();

        $payload = [
            'errorCode' => $code->value,
            'message' => Lang::has($key) ? (string) __($key) : $fallbackMessage,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        if ($params !== []) {
            $payload['params'] = $params;
        }

        return new JsonResponse($payload, $status);
    }
}
