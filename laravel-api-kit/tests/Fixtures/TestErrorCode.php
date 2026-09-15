<?php declare(strict_types=1);

namespace Local\LaravelApiKit\Tests\Fixtures;

use Local\LaravelApiKit\Exceptions\ApiErrorCode;

enum TestErrorCode: int implements ApiErrorCode
{
    case GENERIC = 1000;
    case VALIDATION_FAILED = 1001;
    case UNAUTHENTICATED = 1002;
    case NOT_FOUND = 1003;

    public function translationKey(): string
    {
        return 'test.errors.' . mb_strtolower($this->name);
    }
}
