<?php declare(strict_types=1);

namespace Local\LaravelApiKit;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Local\LaravelApiKit\Exceptions\ApiException;
use Illuminate\Foundation\Configuration\Exceptions;
use Local\LaravelApiKit\Contracts\FallbackErrorCodeResolver;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Registers the render callbacks that give every `/api/*` failure the same
 * envelope (see {@see ApiErrorResponse}), for the exceptions Laravel itself
 * raises. App-specific exceptions unrelated to core Laravel/HTTP — e.g. a
 * particular auth package's own exception type — are the app's own render
 * callback to add alongside this one; see {@see ApiErrorResponse::make()}.
 */
final class ApiExceptionRenderer
{
    /**
     * @param  (callable(Request): bool)|null  $wantsJson  Defaults to
     *                                                     `$request->is('api/*') || $request->expectsJson()`.
     */
    public static function register(
        Exceptions $exceptions,
        FallbackErrorCodeResolver $resolver,
        ?callable $wantsJson = null,
    ): void {
        $wantsJson ??= static fn (Request $request): bool => $request->is('api/*') || $request->expectsJson();

        $exceptions->render(static function (ApiException $e, Request $request) use ($wantsJson): ?JsonResponse {
            if (!$wantsJson($request)) {
                return null;
            }

            return ApiErrorResponse::make($e->errorCode(), $e->getMessage(), $e->getStatusCode(), $e->errors(), $e->params());
        });

        $exceptions->render(static function (ValidationException $e, Request $request) use ($wantsJson, $resolver): ?JsonResponse {
            if (!$wantsJson($request)) {
                return null;
            }

            return ApiErrorResponse::make($resolver->validationFailed(), $e->getMessage(), $e->status, $e->errors());
        });

        $exceptions->render(static function (AuthenticationException $e, Request $request) use ($wantsJson, $resolver): ?JsonResponse {
            if (!$wantsJson($request)) {
                return null;
            }

            return ApiErrorResponse::make(
                $resolver->unauthenticated(),
                $e->getMessage() ?: 'Unauthenticated.',
                401,
            );
        });

        $exceptions->render(static function (HttpExceptionInterface $e, Request $request) use ($wantsJson, $resolver): ?JsonResponse {
            if (!$wantsJson($request)) {
                return null;
            }

            $status = $e->getStatusCode();
            $message = $e->getMessage() ?: 'Error';

            return ApiErrorResponse::make($resolver->forHttpStatus($status), $message, $status);
        });
    }
}
