<?php declare(strict_types=1);

namespace Local\LaravelApiKit\Tests\Fixtures;

/**
 * Duck-typed stand-in for `Illuminate\Contracts\Validation\Validator`,
 * exposing only what `Illuminate\Validation\ValidationException` calls
 * (`errors()`, consumed via {@see FakeMessageBag}) — lets tests build a real
 * `ValidationException` without illuminate/translation wired into a
 * container just to construct one.
 */
final class FakeValidator
{
    /**
     * @param  array<string, list<string>>  $messages
     */
    public function __construct(
        private readonly array $messages,
    ) {}

    public function errors(): FakeMessageBag
    {
        return new FakeMessageBag($this->messages);
    }
}
