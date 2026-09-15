<?php declare(strict_types=1);

namespace Local\LaravelApiKit\Contracts;

use Local\LaravelApiKit\Exceptions\ApiErrorCode;

/**
 * The kit renders `ApiException` subclasses using the code each one already
 * carries. For framework-level exceptions the app never subclassed (a failed
 * validator, an unauthenticated request, a generic 404/429/...), the kit asks
 * the app — via this contract — which of the app's own {@see ApiErrorCode}
 * cases those map to.
 */
interface FallbackErrorCodeResolver
{
    public function unauthenticated(): ApiErrorCode;

    public function validationFailed(): ApiErrorCode;

    /**
     * Fallback for any other HTTP exception, keyed by status code
     * (404, 429, 403, generic default, ...).
     */
    public function forHttpStatus(int $status): ApiErrorCode;
}
