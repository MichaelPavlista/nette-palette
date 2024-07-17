<?php declare(strict_types=1);

use Nette\Bridges\ApplicationDI\LatteExtension;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\DI\Compiler;
use Nette\DI\Container;
use NettePalette\PaletteExtension;
use Tracy\Debugger;

require __DIR__ . '/../../vendor/autoload.php';

Debugger::enable(mode: Debugger::Development);

$loader = new Nette\DI\ContainerLoader(tempDirectory: __DIR__ . '/../temp', autoRebuild: Debugger::isEnabled());

$class = $loader->load(function(Compiler $compiler): void {
    // Zaregistrování Latte přes nette/aplication.
    $compiler->addExtension('latte', new LatteExtension(tempDir: __DIR__ . '/../temp', debugMode: Debugger::isEnabled()));
    $compiler->addExtension('palette', new PaletteExtension());
    $compiler->loadConfig(file: __DIR__ . '/../config.neon');
});

/** @var Container $container */
$container = new $class;
$container->initialize();

/** @var LatteFactory $latteFactory */
$latteFactory = $container->getByType(type: LatteFactory::class);
$latteFactory->create()->render(
    name: __DIR__ . '/../demo.latte',
);
