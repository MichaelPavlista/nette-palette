<?php declare(strict_types=1);

namespace Pavlista\NettePalette;

use Nette\Http\IRequest;
use Nette\Utils\Strings;

/**
 * Třída zajišťující
 * Class PaletteServer
 * @package Pavlista\NettePalette
 */
class PaletteServer
{
    /** @var Palette */
    private Palette $palette;

    /** @var IRequest */
    private IRequest $httpRequest;


    /**
     * PaletteServer constructor.
     * @param Palette $palette
     * @param IRequest $httpRequest
     */
    public function __construct(Palette $palette, IRequest $httpRequest)
    {
        $this->palette = $palette;
        $this->httpRequest = $httpRequest;
    }


    /**
     * Handle request to palette backend.
     * @throws
     */
    public function handlePaletteRequest(): void
    {
        // V CLI je palette listener nedostupný (jelikož neznáme URL).
        if (PHP_SAPI === 'cli')
        {
            return;
        }

        // Načteme aktuální URL adresu bez query stringu.
        $url = $this->httpRequest->getUrl()->path;

        // Předáme aktuální URL palette k vyhodnocení.
        $this->palette->processUrl($url);
    }
}
