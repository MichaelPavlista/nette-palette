<?php declare(strict_types=1);

define('NETTE_VERSION', '3.0');

require __DIR__ . '/vendor/autoload.php';

$container = require __DIR__ . '/../../app/bootstrap.php';

$container->getByType(Nette\Application\Application::class)
          ->run();
