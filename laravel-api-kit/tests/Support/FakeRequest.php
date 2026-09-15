<?php declare(strict_types=1);

namespace Local\LaravelApiKit\Tests\Support;

use Illuminate\Http\Request;

/**
 * Backs the test-only `request()` shim (see tests/bootstrap.php) so each
 * test can control what `request()->user()` returns without a real
 * container or HTTP kernel.
 */
final class FakeRequest
{
    private static ?Request $current = null;

    public static function set(Request $request): void
    {
        self::$current = $request;
    }

    public static function current(): Request
    {
        return self::$current ??= Request::create('/');
    }

    public static function reset(): void
    {
        self::$current = null;
    }
}
