<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Referencë e shkurtër për përdoruesin + log i plotë në server (pa ekspozuar detaje teknike në API).
 */
final class SupportReference
{
    /**
     * Regjistron gabimin dhe kthen kodin 8-shkronjësh për mbështetje.
     *
     * @param  array<string, mixed>  $context
     */
    public static function report(string $logLabel, Throwable $e, array $context = []): string
    {
        $ref = strtoupper(bin2hex(random_bytes(4)));

        Log::error($logLabel.' ['.$ref.'] '.$e->getMessage(), array_merge([
            'reference' => $ref,
            'exception_class' => $e::class,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ], $context));

        return $ref;
    }
}
