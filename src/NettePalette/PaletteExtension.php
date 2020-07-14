<?php

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
use Nette\DI\ServiceCreationException;

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
    public function loadConfiguration()
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
                ->setClass('NettePalette\Palette', array(

                    $config['path'],
                    $config['url'],
                    empty($config['basepath']) ? NULL : $config['basepath'],
                    $config['signingKey'],
                    empty($config['fallbackImage']) ? NULL : $config['fallbackImage'],
                    empty($config['baseUrl']) ? NULL : $config['baseUrl'],
                    empty($config['template']) ? NULL : $config['template'],
                    empty($config['websiteUrl']) ? NULL : $config['websiteUrl'],
                    empty($config['pictureLoader']) ? NULL : $config['pictureLoader'],
                ))
                ->addSetup('setHandleExceptions', [

                    !isset($config['handleException']) ? TRUE : $config['handleException'],
                ]);

        // Register latte filter service
        $builder->addDefinition($this->prefix('filter'))
                ->setClass('NettePalette\LatteFilter', [$this->prefix('@service')]);

        // Register latte filter
        $this->getLatteService()
             ->addSetup('addFilter', ['palette', $this->prefix('@filter')]);

        // Register extension presenter
        $builder->getDefinition('nette.presenterFactory')
                ->addSetup('setMapping', [['Palette' => 'NettePalette\*Presenter']]);
    }


    /**
     * Get Latte service definition
     * @return \Nette\DI\ServiceDefinition
     */
    protected function getLatteService()
    {
        $builder = $this->getContainerBuilder();

        return $builder->hasDefinition('nette.latteFactory')
            ? $builder->getDefinition('nette.latteFactory')
            : $builder->getDefinition('nette.latte');
    }
}
