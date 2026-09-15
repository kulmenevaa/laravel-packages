<?php declare(strict_types=1);

namespace Local\LaravelApiKit\Tests\Fixtures;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Support\Arrayable;
use Local\LaravelApiKit\Http\ApiController;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Exposes ApiController's protected API as public methods so tests can call
 * it directly, without pulling in routing to dispatch a real controller.
 */
final class TestApiController extends ApiController
{
    /**
     * @param  JsonResource|ResourceCollection|Arrayable<string, mixed>|array<string, mixed>|null  $data
     */
    public function jsonResponse(
        JsonResource|ResourceCollection|Arrayable|array|null $data = null,
        int $status = 200,
    ): JsonResponse {
        return $this->respondJson($data, $status);
    }

    public function noContentResponse(): JsonResponse
    {
        return $this->respondNoContent();
    }

    public function currentUser(): Authenticatable
    {
        return $this->authUser();
    }
}
