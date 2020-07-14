<?php declare(strict_types=1);

/**
 * This file is part of the Nette Palette (https://github.com/MichaelPavlista/nette-palette)
 * Copyright (c) 2016 Michael Pavlista (http://www.pavlista.cz/)
 *
 * @author Michael Pavlista
 * @email  michael@pavlista.cz
 * @link   http://pavlista.cz/
 * @link   https://www.facebook.com/MichaelPavlista
 * @copyright 2016
 */

namespace NettePalette;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Definition;
use Nette\DI\ServiceCreationException;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\FactoryDefinition;

/**
 * Palette Extension for Nette Framework
 * Class PaletteExtension
 * @package Palette
 */
class PaletteExtension extends CompilerExtension
{
    /**
     * Processes configuration data.
     * @return void
     * @throws ServiceCreationException
     */
    public function loadConfiguration(): void
    {
        // Load extension configuration
        $config = $this->getConfig();

        if(!isset($config['path']))
        {
            throw new ServiceCreationException('Missing required path parameter in PaletteExtension configuration');
        }

        if(!isset($config['url']))
        {
            throw new ServiceCreationException('Missing required url parameter in PaletteExtension configuration');
        }

        if(!isset($config['signingKey']))
        {
            throw new ServiceCreationException('Missing required parameter signingKey in PaletteExtension configuration');
        }

        // Register extension services
        $builder = $this->getContainerBuilder();

        // Register palette service
        $builder->addDefinition($this->prefix('service'))
                ->setType(Palette::class)
                ->setArguments([

                    $config['path'],
                    $config['url'],
                    empty($config['basepath']) ? NULL : $config['basepath'],
                    $config['signingKey'],
                    empty($config['baseUrl']) ? NULL : $config['baseUrl'],
                    empty($config['fallbackImage']) ? NULL : $config['fallbackImage'],
                    empty($config['template']) ? NULL : $config['template'],
                    empty($config['websiteUrl']) ? NULL : $config['websiteUrl'],
                    empty($config['pictureLoader']) ? NULL : $config['pictureLoader'],
                ])
                ->addSetup('setHandleExceptions', [

                    $config['handleException'] ?? TRUE,
                ]);

        // Register latte filter service
        $builder->addDefinition($this->prefix('filter'))
                ->setType(LatteFilter::class)
                ->setArguments([$this->prefix('@service')]);

        // Register latte filter
        $latteService = $this->getLatteService();

        if($latteService instanceof FactoryDefinition)
        {
            $latteService = $latteService->getResultDefinition();
        }

        $latteService->addSetup('addFilter', ['palette', $this->prefix('@filter')]);

        // Register extension presenter
        $builder->getDefinition('nette.presenterFactory')
                ->addSetup('setMapping', [['Palette' => 'NettePalette\*Presenter']]);
    }


    /**
     * Get Latte service definition
     * @return ServiceDefinition|FactoryDefinition
     */
    protected function getLatteService(): Definition
    {
        $builder = $this->getContainerBuilder();

        return $builder->hasDefinition('nette.latteFactory')
            ? $builder->getDefinition('nette.latteFactory')
            : $builder->getDefinition('nette.latte');
    }
}
