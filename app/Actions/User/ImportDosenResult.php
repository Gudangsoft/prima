<?php

declare(strict_types=1);

namespace App\Actions\User;

/** Ringkasan hasil impor dosen dari file export SINTA. */
final readonly class ImportDosenResult
{
    /** @param  list<string>  $skipped  Baris yang dilewati beserta alasannya. */
    public function __construct(
        public int $created,
        public int $updated,
        public array $skipped,
    ) {}
}
