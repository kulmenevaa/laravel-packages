<?php declare(strict_types=1);

namespace Local\LaravelApiKit\Tests\Fixtures;

use Local\LaravelApiKit\Exceptions\ApiErrorCode;
use Local\LaravelApiKit\Exceptions\ApiException;

final class TestApiException extends ApiException
{
    /**
     * @param  array<string, list<string>>  $errors
     * @param  array<string, scalar>  $params
     */
    public function __construct(
        string $message = 'Something went wrong.',
        array $errors = [],
        array $params = [],
        private readonly ApiErrorCode $errorCode = TestErrorCode::GENERIC,
        private readonly int $status = 422,
    ) {
        parent::__construct($message, $errors, $params);
    }

    public function errorCode(): ApiErrorCode
    {
        return $this->errorCode;
    }

    protected function statusCode(): int
    {
        return $this->status;
    }
}
