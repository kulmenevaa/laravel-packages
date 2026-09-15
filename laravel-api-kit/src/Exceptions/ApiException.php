<?php declare(strict_types=1);

namespace Local\LaravelApiKit\Exceptions;

use RuntimeException;
use Local\LaravelApiKit\ApiExceptionRenderer;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Base class for every API-layer exception in a consuming app. Guarantees a
 * stable error envelope once rendered by {@see ApiExceptionRenderer}:
 *
 *     { "errorCode": int|string, "message": string, "errors"?: object, "params"?: object }
 */
abstract class ApiException extends RuntimeException implements HttpExceptionInterface
{
    /**
     * @param  array<string, list<string>>  $errors
     * @param  array<string, scalar>  $params
     */
    public function __construct(
        string $message,
        private readonly array $errors = [],
        private readonly array $params = [],
    ) {
        parent::__construct($message);
    }

    /**
     * Stable identifier for this failure — a case of the app's own
     * {@see ApiErrorCode} enum.
     */
    abstract public function errorCode(): ApiErrorCode;

    public function getStatusCode(): int
    {
        return $this->statusCode();
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [];
    }

    /**
     * @return array<string, list<string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @return array<string, scalar>
     */
    public function params(): array
    {
        return $this->params;
    }

    abstract protected function statusCode(): int;
}
