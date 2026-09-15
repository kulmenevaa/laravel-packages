<?php declare(strict_types=1);

namespace Illuminate\Foundation\Configuration;

use Closure;

/**
 * Stand-in for Laravel's real `Illuminate\Foundation\Configuration\Exceptions`.
 *
 * `ApiExceptionRenderer::register()` type-hints the real class, but this
 * package deliberately does not require illuminate/foundation (see the kit's
 * README "Границы пакета" note) — so it isn't installed here. This stub
 * exposes the one method `register()` actually calls, `render()`, and
 * records every callback so tests can invoke them directly against fixture
 * exceptions and assert on the resulting response.
 */
final class Exceptions
{
    /** @var list<Closure> */
    public array $renderers = [];

    public function render(Closure $using): static
    {
        $this->renderers[] = $using;

        return $this;
    }
}
