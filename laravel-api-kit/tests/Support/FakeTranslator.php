<?php declare(strict_types=1);

namespace Local\LaravelApiKit\Tests\Support;

/**
 * Backs the test-only `__()` shim (see tests/bootstrap.php). `ApiErrorResponse`
 * only cares whether a translation line exists for a key and, if so, what it
 * resolves to — this stands in for the real translator without needing
 * illuminate/translation wired into a container.
 */
final class FakeTranslator
{
    /** @var array<string, string> */
    private static array $lines = [];

    public static function put(string $key, string $line): void
    {
        self::$lines[$key] = $line;
    }

    public static function line(?string $key): ?string
    {
        return $key !== null ? (self::$lines[$key] ?? null) : null;
    }

    public static function reset(): void
    {
        self::$lines = [];
    }
}
