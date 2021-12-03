<?php declare(strict_types=1);

namespace Palette\DemoApp\Presenters;

use Nette\Application\UI\Presenter;
use Pavlista\NettePalette\Palette;

/**
 * Class PalettePresenter
 * @package Palette\DemoApp\Presenters
 */
final class PalettePresenter extends Presenter
{
    /** @var Palette @inject */
    public $paletteService;


    /**
     * Demo action.
     */
    public function actionDefault(): void
    {
        $url = $this->paletteService->getUrl(__DIR__ . '/../../www/images/portrait.jpg', '//100;100');

        echo $url;
    }
}
