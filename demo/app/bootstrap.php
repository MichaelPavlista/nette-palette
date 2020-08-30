<?php declare(strict_types=1);

// Check if Nette required version is defined.
if (!defined('NETTE_VERSION'))
{
    die('NETTE_VERSION is not defined.');
}

// Create required directories for required Nette version.
$createDir = static function(string $directoryPath, int $chmod = 0775): string
{
    if(!file_exists($directoryPath) && !mkdir($directoryPath, $chmod) && !is_dir($directoryPath))
    {
        die(sprintf('Directory "%s" was not created.', $directoryPath));
    }

    return $directoryPath;
};

$logsDir = $createDir(__DIR__ . '/../log/' . NETTE_VERSION);
$tempDir = $createDir(__DIR__ . '/../temp/' . NETTE_VERSION);

// Create and configure nette container.
$configurator = new Nette\Configurator;
$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->setTempDirectory($tempDir);
$configurator->setDebugMode(TRUE); // Enable debug mode.
$configurator->enableDebugger($logsDir);

// Configure robot loader.
$robotLoader = $configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->addDirectory(__DIR__ . '/../../src') // nette-palette from source.
    ->addDirectory(__DIR__ . '/../../vendor/pavlista/palette/src') // palette from package vendor folder.
    ->register();

return $configurator->createContainer();
