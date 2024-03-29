<?php declare(strict_types=1);

namespace Palette\DemoApp\Presenters;

use Nette\Application\UI\Presenter;
use NettePalette\Palette;

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
    }
}
