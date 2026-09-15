<?php declare(strict_types=1);

namespace Local\LaravelApiKit\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Local\LaravelApiKit\Tests\Fixtures\TestErrorCode;
use Local\LaravelApiKit\Tests\Fixtures\TestApiException;

final class ApiExceptionTest extends TestCase
{
    #[Test]
    public function exposesMessageErrorCodeStatusErrorsAndParams(): void
    {
        $exception = new TestApiException(
            message: 'Validation failed.',
            errors: ['email' => ['The email field is required.']],
            params: ['field' => 'email'],
            errorCode: TestErrorCode::VALIDATION_FAILED,
            status: 422,
        );

        self::assertSame('Validation failed.', $exception->getMessage());
        self::assertSame(TestErrorCode::VALIDATION_FAILED, $exception->errorCode());
        self::assertSame(422, $exception->getStatusCode());
        self::assertSame(['email' => ['The email field is required.']], $exception->errors());
        self::assertSame(['field' => 'email'], $exception->params());
    }

    #[Test]
    public function defaultsErrorsAndParamsToEmptyArrays(): void
    {
        $exception = new TestApiException('Something went wrong.');

        self::assertSame([], $exception->errors());
        self::assertSame([], $exception->params());
    }

    #[Test]
    public function getHeadersDefaultsToEmptyArray(): void
    {
        $exception = new TestApiException('Something went wrong.');

        self::assertSame([], $exception->getHeaders());
    }

    #[Test]
    public function isThrowableAsARegularException(): void
    {
        $this->expectException(TestApiException::class);
        $this->expectExceptionMessage('Boom.');

        throw new TestApiException('Boom.');
    }
}
