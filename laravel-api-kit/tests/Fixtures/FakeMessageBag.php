<?php declare(strict_types=1);

namespace Local\LaravelApiKit\Tests\Fixtures;

/**
 * Just enough of `Illuminate\Support\MessageBag` for
 * `Illuminate\Validation\ValidationException` to summarize and expose
 * errors, without depending on illuminate/translation to build a real
 * validator (see {@see FakeValidator}).
 */
final class FakeMessageBag
{
    /**
     * @param  array<string, list<string>>  $messages
     */
    public function __construct(
        private readonly array $messages,
    ) {}

    /**
     * @return list<string>
     */
    public function all(): array
    {
        return array_merge(...array_values($this->messages ?: [[]]));
    }

    /**
     * @return array<string, list<string>>
     */
    public function messages(): array
    {
        return $this->messages;
    }
}
