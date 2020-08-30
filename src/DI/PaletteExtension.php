<?php declare(strict_types=1);

namespace Pavlista\NettePalette\DI;

use Nette;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\ServiceCreationException;
use Pavlista\NettePalette\Filters\PaletteFilter;
use Pavlista\NettePalette\Palette;
use Pavlista\NettePalette\PaletteServer;

/**
 * Class PaletteExtension
 * @package Pavlista\NettePalette\DI
 */
final class PaletteExtension extends CompilerExtension
{
    /** @var string service name - nette application */
    private const SERVICE_NETTE_APPLICATION = 'application.application';

    /** @var string service name - palette */
    private const SERVICE_PALETTE = 'service';

    /** @var string service name - palette server */
    private const SERVICE_PALETTE_SERVER = 'server';


    /**
     * Processes extension configuration.
     * @return void
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

        $builder = $this->getContainerBuilder();

        // Register palette services.
        $builder
            ->addDefinition($this->prefix(self::SERVICE_PALETTE))
            ->setType(Palette::class)
            ->setArguments([

                $config['path'],
                $config['url'],
                empty($config['basepath']) ? NULL : $config['basepath'],
                $config['signingKey'],
                empty($config['fallbackImage']) ? NULL : $config['fallbackImage'],
                empty($config['template']) ? NULL : $config['template'],
                empty($config['websiteUrl']) ? NULL : $config['websiteUrl'],
                empty($config['pictureLoader']) ? NULL : $config['pictureLoader'],
            ])
            ->addSetup('setHandleExceptions', [

                $config['handleException'] ?? TRUE,
            ]);

        $builder
            ->addDefinition($this->prefix(self::SERVICE_PALETTE_SERVER))
            ->setType(PaletteServer::class);

        // Register latte filter service
        $builder->addDefinition($this->prefix('filter'))
                ->setType(PaletteFilter::class)
                ->setArguments([$this->prefix('@service')]);

        // Register latte filter
        $latteService = $this->getLatteService();

        if($latteService instanceof FactoryDefinition)
        {
            $latteService = $latteService->getResultDefinition();
        }

        $latteService->addSetup('addFilter', ['palette', $this->prefix('@filter')]);
    }


    /**
     * Get Latte service definition
     * @return ServiceDefinition|FactoryDefinition
     * @todo Is it still needed?
     */
    protected function getLatteService()
    {
        $builder = $this->getContainerBuilder();

        return $builder->hasDefinition('nette.latteFactory')
            ? $builder->getDefinition('nette.latteFactory')
            : $builder->getDefinition('nette.latte');
    }


    /**
     * Adjusts DI container compiled to PHP class.
     * @param Nette\PhpGenerator\ClassType $class
     */
    public function afterCompile(Nette\PhpGenerator\ClassType $class): void
    {
        parent::afterCompile($class);

        // Load container initialize method definition.
        $initialize = $class->getMethod('initialize');

        // Register palette server backend handler.
        $initialize->addBody(
            '$this->getService(?)->onStartup[] = [$this->getService(?), ?];',
            [
                self::SERVICE_NETTE_APPLICATION,
                $this->prefix(self::SERVICE_PALETTE_SERVER),
                'handlePaletteRequest'
            ],
        );
    }
}
