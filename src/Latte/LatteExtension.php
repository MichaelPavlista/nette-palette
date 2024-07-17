<?php declare(strict_types=1);

/**
 * This file is part of the Nette Palette (https://github.com/MichaelPavlista/nette-palette)
 * Copyright (c) 2016 Michael Pavlistal (https://www.pavlista.cz)
 */

namespace NettePalette\Latte;

use Latte\Extension;
use NettePalette\Latte\Node\PaletteSrcNode;
use NettePalette\Palette;

/**
 * Rozšíření Palette do Latte.
 */
final class LatteExtension extends Extension
{
    /**
     * LatteExtension constructor.
     */
    public function __construct(
        private readonly Palette $palette,
    )
    {
    }


    /**
     * Zaregistrování Palette providerů do Latte.
     * @return Palette[]
     */
    public function getProviders(): array
    {
        return ['palette' => $this->palette];
    }


    /**
     * Zaregistrování Palette Latte filtrů.
     * @return array<string, callable>
     */
    public function getFilters(): array
    {
        return [
            'palette' => fn (string $imagePath, string $imageQuery): ?string
                => $this->palette->getUrl($imagePath, $imageQuery),
        ];
    }


    /**
     * Registrace Palette Latte tagů.
     * @return array<string, callable>
     */
    public function getTags(): array
    {
        return [
            'n:palette-src' => PaletteSrcNode::create(...),
        ];
    }
}
