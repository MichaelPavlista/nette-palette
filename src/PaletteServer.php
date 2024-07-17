<?php declare(strict_types=1);

/**
 * This file is part of the Nette Palette (https://github.com/MichaelPavlista/nette-palette)
 * Copyright (c) 2016 Michael Pavlistal (https://www.pavlista.cz)
 */

namespace NettePalette;

use Throwable;

/**
 * Server Palette zajišťující generování miniatur.
 */
final readonly class PaletteServer
{
    /**
     * PaletteServer constructor.
     */
    public function __construct(
        private Palette $palette,
        private string $serverUri,
    )
    {
    }


    /**
     * Vyhodnotí požadavek a rozhodne, zda se má předat na Palette.
     * @throws Throwable
     */
    public function handleRequest(): void
    {
        // Jedná se o pažadavek na Palette?
        if (str_starts_with(haystack: $_SERVER['REQUEST_URI'] ?? '', needle: $this->serverUri))
        {
            $this->palette->serverResponse();
        }
    }
}
