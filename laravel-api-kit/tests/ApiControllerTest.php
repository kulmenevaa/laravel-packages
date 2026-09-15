<?php declare(strict_types=1);

namespace Local\LaravelApiKit\Tests;

use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Illuminate\Container\Container;
use PHPUnit\Framework\Attributes\Test;
use Local\LaravelApiKit\Tests\Fixtures\TestUser;
use Local\LaravelApiKit\Tests\Support\FakeRequest;
use Local\LaravelApiKit\Tests\Fixtures\TestResource;
use Local\LaravelApiKit\Tests\Fixtures\TestApiController;

final class ApiControllerTest extends TestCase
{
    private TestApiController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new TestApiController();
    }

    protected function tearDown(): void
    {
        FakeRequest::reset();
        Container::setInstance(null);

        parent::tearDown();
    }

    #[Test]
    public function jsonResponseWrapsArrayDataWithDefaultStatus(): void
    {
        $response = $this->controller->jsonResponse(['id' => 1]);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(['id' => 1], json_decode($response->getContent(), true));
    }

    #[Test]
    public function jsonResponseHonorsGivenStatus(): void
    {
        $response = $this->controller->jsonResponse(['id' => 1], 201);

        self::assertSame(201, $response->getStatusCode());
    }

    #[Test]
    public function jsonResponseWithNullDataYieldsEmptyJsonObject(): void
    {
        $response = $this->controller->jsonResponse();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('{}', $response->getContent());
    }

    #[Test]
    public function jsonResponseUnwrapsAJsonResourceAndAppliesStatus(): void
    {
        $container = new Container();
        $container->instance('request', Request::create('/'));
        Container::setInstance($container);

        $response = $this->controller->jsonResponse(new TestResource(['id' => 5]), 201);

        self::assertSame(201, $response->getStatusCode());
        // JsonResource wraps a single resource in a top-level "data" key by
        // default (`JsonResource::$wrap`) — this is Laravel's own behavior,
        // not something respondJson() adds or strips.
        self::assertSame(['data' => ['id' => 5]], json_decode($response->getContent(), true));
    }

    #[Test]
    public function noContentResponseIsA204WithATrulyEmptyBody(): void
    {
        $response = $this->controller->noContentResponse();

        self::assertSame(204, $response->getStatusCode());
        self::assertSame('', $response->getContent());
    }

    #[Test]
    public function authUserReturnsTheRequestUsersAuthenticatable(): void
    {
        $user = new TestUser(42);
        $request = Request::create('/');
        $request->setUserResolver(fn () => $user);
        FakeRequest::set($request);

        self::assertSame($user, $this->controller->currentUser());
    }

    #[Test]
    public function authUserFailsLoudlyWhenTheRequestHasNoUser(): void
    {
        // authUser()'s return type is non-nullable Authenticatable: per its
        // own docblock, a route without an auth guard in front of it is a
        // misconfiguration, and this should blow up rather than silently
        // hand back null.
        FakeRequest::set(Request::create('/'));

        $this->expectException(\TypeError::class);

        $this->controller->currentUser();
    }
}
