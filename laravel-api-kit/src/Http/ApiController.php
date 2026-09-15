<?php declare(strict_types=1);

namespace Local\LaravelApiKit\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

/**
 * Base controller for a versioned JSON API: a single response helper and a
 * typed accessor for the authenticated user. Framework-agnostic beyond
 * Laravel/Symfony HTTP — no app model or domain coupling.
 */
abstract class ApiController
{
    /**
     * @param  JsonResource|ResourceCollection|Arrayable<string, mixed>|array<string, mixed>|null  $data
     */
    protected function respondJson(
        JsonResource|ResourceCollection|Arrayable|array|null $data = null,
        int $status = HttpStatus::HTTP_OK,
    ): JsonResponse {
        if ($data instanceof JsonResource) {
            return $data->response()->setStatusCode($status);
        }

        return new JsonResponse($data, $status);
    }

    protected function respondNoContent(): JsonResponse
    {
        // JsonResponse defaults a null body to `{}` (an empty ArrayObject),
        // which a 204 must not carry — force a truly empty body instead.
        return (new JsonResponse(status: HttpStatus::HTTP_NO_CONTENT))->setContent('');
    }

    /**
     * The authenticated user for the current request. Routes that call this
     * are expected to sit behind an auth middleware, so the guard should
     * never actually return null here — callers on a genuinely optional-auth
     * route should use `request()->user()` directly instead.
     *
     * A consuming app typically narrows this with a covariant override
     * returning its own user model, e.g. `protected function authUser(): User`.
     */
    protected function authUser(): Authenticatable
    {
        /** @var Authenticatable $user */
        $user = request()->user();

        return $user;
    }
}
