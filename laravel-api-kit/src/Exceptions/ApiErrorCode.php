<?php declare(strict_types=1);

namespace Local\LaravelApiKit\Exceptions;

use BackedEnum;

/**
 * Contract a consuming app's own error-code enum implements so the kit's
 * exception base class and renderer can work with it without depending on
 * any concrete app.
 *
 * `BackedEnum` already gives callers `->value` (int|string) and `::cases()`;
 * this only adds the one thing the renderer needs beyond that.
 */
interface ApiErrorCode extends BackedEnum
{
    /**
     * Translation key looked up for the human-readable `message`
     * (e.g. `api.errors.invalid_credentials`). No key needs to exist for a
     * given code — the renderer falls back to the exception's own message.
     */
    public function translationKey(): string;
}
