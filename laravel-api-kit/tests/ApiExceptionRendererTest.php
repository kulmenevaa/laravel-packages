<?php declare(strict_types=1);

namespace Local\LaravelApiKit\Tests;

use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Auth\AuthenticationException;
use Local\LaravelApiKit\ApiExceptionRenderer;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Local\LaravelApiKit\Tests\Fixtures\FakeValidator;
use Local\LaravelApiKit\Tests\Fixtures\TestErrorCode;
use Local\LaravelApiKit\Tests\Fixtures\TestApiException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Local\LaravelApiKit\Tests\Fixtures\TestFallbackErrorCodeResolver;

final class ApiExceptionRendererTest extends TestCase
{
    private Exceptions $exceptions;

    protected function setUp(): void
    {
        parent::setUp();

        // ApiErrorResponse::make() consults the Lang facade for a
        // translation of the error code; these tests only care about the
        // envelope's status/errorCode/message pass-through, so pretend no
        // translation exists and always fall back to the given message.
        Lang::shouldReceive('has')->andReturn(false);

        $this->exceptions = new Exceptions();
        ApiExceptionRenderer::register($this->exceptions, new TestFallbackErrorCodeResolver());
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        Facade::clearResolvedInstances();

        parent::tearDown();
    }

    #[Test]
    public function apiExceptionIsRenderedAsItsOwnEnvelope(): void
    {
        $exception = new TestApiException(
            message: 'Boom.',
            errors: ['email' => ['is invalid']],
            params: ['field' => 'email'],
            errorCode: TestErrorCode::GENERIC,
            status: 422,
        );

        $response = ($this->exceptions->renderers[0])($exception, Request::create('/api/things'));

        self::assertNotNull($response);
        self::assertSame(422, $response->getStatusCode());
        self::assertSame(
            [
                'errorCode' => 1000,
                'message' => 'Boom.',
                'errors' => ['email' => ['is invalid']],
                'params' => ['field' => 'email'],
            ],
            json_decode($response->getContent(), true),
        );
    }

    #[Test]
    public function apiExceptionRendererDefersWhenRequestDoesNotWantJson(): void
    {
        $exception = new TestApiException('Boom.');

        $response = ($this->exceptions->renderers[0])($exception, Request::create('/web/things'));

        self::assertNull($response);
    }

    #[Test]
    public function validationExceptionUsesTheResolversCodeAndItsOwnStatusAndErrors(): void
    {
        $validator = new FakeValidator(['email' => ['The email field is required.']]);
        $exception = new ValidationException($validator);
        $exception->status = 422;

        $response = ($this->exceptions->renderers[1])($exception, Request::create('/api/things'));

        self::assertNotNull($response);
        self::assertSame(422, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        self::assertSame(1001, $payload['errorCode']);
        self::assertSame(['email' => ['The email field is required.']], $payload['errors']);
    }

    #[Test]
    public function authenticationExceptionUsesTheResolversCodeAnd401(): void
    {
        $response = ($this->exceptions->renderers[2])(new AuthenticationException('Custom message.'), Request::create('/api/things'));

        self::assertNotNull($response);
        self::assertSame(401, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        self::assertSame(1002, $payload['errorCode']);
        self::assertSame('Custom message.', $payload['message']);
    }

    #[Test]
    public function authenticationExceptionFallsBackToADefaultMessageWhenNoneIsGiven(): void
    {
        $response = ($this->exceptions->renderers[2])(new AuthenticationException(''), Request::create('/api/things'));

        self::assertNotNull($response);
        $payload = json_decode($response->getContent(), true);
        self::assertSame('Unauthenticated.', $payload['message']);
    }

    #[Test]
    public function genericHttpExceptionUsesTheResolversCodeAndItsOwnStatus(): void
    {
        $response = ($this->exceptions->renderers[3])(new NotFoundHttpException('No such thing.'), Request::create('/api/things'));

        self::assertNotNull($response);
        self::assertSame(404, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        self::assertSame(1003, $payload['errorCode']);
        self::assertSame('No such thing.', $payload['message']);
    }

    #[Test]
    public function genericHttpExceptionFallsBackToADefaultMessageWhenNoneIsGiven(): void
    {
        $response = ($this->exceptions->renderers[3])(new NotFoundHttpException(), Request::create('/api/things'));

        self::assertNotNull($response);
        $payload = json_decode($response->getContent(), true);
        self::assertSame('Error', $payload['message']);
    }

    #[Test]
    public function aCustomWantsJsonCallableOverridesTheDefaultDetection(): void
    {
        $exceptions = new Exceptions();
        ApiExceptionRenderer::register(
            $exceptions,
            new TestFallbackErrorCodeResolver(),
            wantsJson: static fn (Request $request): bool => $request->header('X-Force-Json') === '1',
        );

        // A path under /api/* would normally be treated as JSON by the
        // default detector — the custom callable must fully replace it.
        $request = Request::create('/api/things');
        $response = ($exceptions->renderers[0])(new TestApiException('Boom.'), $request);

        self::assertNull($response);

        $request->headers->set('X-Force-Json', '1');
        $response = ($exceptions->renderers[0])(new TestApiException('Boom.'), $request);

        self::assertNotNull($response);
    }
}
