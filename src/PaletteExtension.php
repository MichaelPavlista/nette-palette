<?php declare(strict_types=1);

/**
 * This file is part of the Nette Palette (https://github.com/MichaelPavlista/nette-palette)
 * Copyright (c) 2016 Michael Pavlistal (https://www.pavlista.cz)
 */

namespace NettePalette;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\PhpGenerator\ClassType;
use Nette\Schema\Schema;
use stdClass;

/**
 * Registrace Palette do Nette.
 */
final class PaletteExtension extends CompilerExtension
{
    /** @var string služba - palette */
    private const SERVICE_PALETTE = 'service';

    /** @var string služba - palette server */
    private const SERVICE_PALETTE_SERVER = 'server';


    /**
     * Definice struktury konfigurace rozšíření.
     */
    public function getConfigSchema(): Schema
    {
        return PaletteConfig::getConfigSchema();
    }


    /**
     * Načtení konfigurace a registrace služeb.
     */
    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();

        // Sestavení DTO konfigurace.
        /** @var array<string, string|int|string[]|null>|stdClass $config */
        $config = $this->getConfig();
        $paletteConfig = new PaletteConfig($config);

        // Zaregistrování hlavní služby Palette.
        $paletteService = $builder
            ->addDefinition($this->prefix(id: self::SERVICE_PALETTE))
            ->setType(Palette::class)
            ->setArguments([
                'storagePath' => $paletteConfig->path,
                'storageUrl' => $paletteConfig->url,
                'basePath' => $paletteConfig->basepath,
                'signingKey' => $paletteConfig->signingKey,
                'fallbackImage' => $paletteConfig->fallbackImage,
                'templates' => $paletteConfig->template,
                'fallbackImages' => $paletteConfig->fallbackImages,
                'websiteUrl' => $paletteConfig->websiteUrl,
                'pictureLoader' => $paletteConfig->pictureLoader,
            ])
            ->addSetup(entity: 'setHandleExceptions', args: [$paletteConfig->handleException]);

        // Sestavíme uri na které je dostupný server.
        $serverUri = parse_url($paletteConfig->url, component: PHP_URL_PATH);

        // Zaregistrování služby Palette serveru (= služby generující miniatury).
        $builder
            ->addDefinition($this->prefix(id: self::SERVICE_PALETTE_SERVER))
            ->setType(PaletteServer::class)
            ->setArguments(['serverUri' => $serverUri]);

        // Zaregistrování Palette extension do Latte.
        $latteService = $builder->hasDefinition(name: 'nette.latteFactory')
            ? $builder->getDefinition(name: 'nette.latteFactory')
            : $builder->getDefinition(name: 'nette.latte');

        if ($latteService instanceof FactoryDefinition)
        {
            $latteService = $latteService->getResultDefinition();
        }

        assert(assertion: $latteService instanceof ServiceDefinition);

        $latteService->addSetup(
            entity: '?->addExtension(new NettePalette\Latte\LatteExtension(?))',
            args: ['@self', $paletteService],
        );
    }


    /**
     * Úprava konteineru po jeho zkompilování.
     */
    public function afterCompile(ClassType $class): void
    {
        parent::afterCompile($class);

        // Load container initialize method definition.
        $initialize = $class->getMethod(name: 'initialize');
        $initialize->setBody(
            code: '$this->getService(?)->handleRequest(); ' . PHP_EOL . $initialize->getBody(),
            args: [$this->prefix(id: self::SERVICE_PALETTE_SERVER)],
        );
    }
}
