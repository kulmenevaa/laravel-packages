<?php declare(strict_types=1);

namespace Local\LaravelApiKit\Tests;

use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\Attributes\Test;
use Local\LaravelApiKit\ApiErrorResponse;
use Local\LaravelApiKit\Tests\Fixtures\TestErrorCode;
use Local\LaravelApiKit\Tests\Support\FakeTranslator;

final class ApiErrorResponseTest extends TestCase
{
    protected function tearDown(): void
    {
        FakeTranslator::reset();
        \Mockery::close();
        // Drop the Lang facade's resolved Mockery spy — otherwise it (and
        // every prior test's `shouldReceive` expectations) survives into the
        // next test method instead of being rebuilt per test.
        Facade::clearResolvedInstances();

        parent::tearDown();
    }

    #[Test]
    public function usesFallbackMessageWhenNoTranslationExists(): void
    {
        Lang::shouldReceive('has')
            ->once()
            ->with(TestErrorCode::GENERIC->translationKey())
            ->andReturn(false);

        $response = ApiErrorResponse::make(TestErrorCode::GENERIC, 'Fallback message.', 400);

        self::assertSame(400, $response->getStatusCode());
        self::assertSame(
            ['errorCode' => 1000, 'message' => 'Fallback message.'],
            json_decode($response->getContent(), true),
        );
    }

    #[Test]
    public function prefersTranslationOverFallbackMessageWhenItExists(): void
    {
        $key = TestErrorCode::VALIDATION_FAILED->translationKey();
        FakeTranslator::put($key, 'Translated message.');

        Lang::shouldReceive('has')->once()->with($key)->andReturn(true);

        $response = ApiErrorResponse::make(TestErrorCode::VALIDATION_FAILED, 'Fallback message.', 422);

        self::assertSame(
            ['errorCode' => 1001, 'message' => 'Translated message.'],
            json_decode($response->getContent(), true),
        );
    }

    #[Test]
    public function omitsErrorsAndParamsWhenEmpty(): void
    {
        Lang::shouldReceive('has')->once()->andReturn(false);

        $response = ApiErrorResponse::make(TestErrorCode::GENERIC, 'Message.', 400);

        $payload = json_decode($response->getContent(), true);

        self::assertArrayNotHasKey('errors', $payload);
        self::assertArrayNotHasKey('params', $payload);
    }

    #[Test]
    public function includesErrorsAndParamsWhenPresent(): void
    {
        Lang::shouldReceive('has')->once()->andReturn(false);

        $response = ApiErrorResponse::make(
            TestErrorCode::VALIDATION_FAILED,
            'Fallback message.',
            422,
            errors: ['email' => ['The email field is required.']],
            params: ['field' => 'email'],
        );

        self::assertSame(
            [
                'errorCode' => 1001,
                'message' => 'Fallback message.',
                'errors' => ['email' => ['The email field is required.']],
                'params' => ['field' => 'email'],
            ],
            json_decode($response->getContent(), true),
        );
    }

    #[Test]
    public function carriesTheGivenStatusCode(): void
    {
        Lang::shouldReceive('has')->once()->andReturn(false);

        $response = ApiErrorResponse::make(TestErrorCode::NOT_FOUND, 'Not found.', 404);

        self::assertSame(404, $response->getStatusCode());
    }
}
