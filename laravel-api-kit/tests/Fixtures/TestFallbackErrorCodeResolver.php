<?php declare(strict_types=1);

namespace Local\LaravelApiKit\Tests\Fixtures;

use Local\LaravelApiKit\Exceptions\ApiErrorCode;
use Local\LaravelApiKit\Contracts\FallbackErrorCodeResolver;

final class TestFallbackErrorCodeResolver implements FallbackErrorCodeResolver
{
    public function unauthenticated(): ApiErrorCode
    {
        return TestErrorCode::UNAUTHENTICATED;
    }

    public function validationFailed(): ApiErrorCode
    {
        return TestErrorCode::VALIDATION_FAILED;
    }

    public function forHttpStatus(int $status): ApiErrorCode
    {
        return TestErrorCode::NOT_FOUND;
    }
}
