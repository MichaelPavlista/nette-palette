<?php declare(strict_types=1);

namespace Pavlista\NettePalette\Filters;

use Pavlista\NettePalette\Palette;

/**
 * Palette Latte filter.
 * Class PaletteFilter
 * @package Pavlista\NettePalette\Filters
 */
class PaletteFilter
{
    /** @var Palette */
    private Palette $palette;


    /**
     * PaletteFilter constructor.
     * @param Palette $palette
     */
    public function __construct(Palette $palette)
    {
        $this->palette = $palette;
    }

    /**
     * Return url to required image variant.
     * @param string $imagePath filesystem image patch
     * @param string|null $imageQuery
     * @return string|null
     */
    public function __invoke(string $imagePath, ?string $imageQuery = NULL): ?string
    {
        return $this->palette->getUrl($imagePath, $imageQuery);
    }
}
