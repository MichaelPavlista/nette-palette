<?php declare(strict_types=1);

namespace Pavlista\NettePalette;

use Nette\Http\IRequest;

/**
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
        $this->palette->serverResponse();
    }
}
