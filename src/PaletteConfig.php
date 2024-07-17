<?php declare(strict_types=1);

/**
 * This file is part of the Nette Palette (https://github.com/MichaelPavlista/nette-palette)
 * Copyright (c) 2016 Michael Pavlistal (https://www.pavlista.cz)
 */

namespace NettePalette;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * Konfigurace Palette.
 */
final readonly class PaletteConfig
{
    /** @var string relativní nebo absolutní cesta ke složce do které se mají vygenerované miniatury a obrázky ukládat */
    public string $path;

    /** @var string absolutní url adresa s lomítkem na konci na které je složka s miniatury veřejně dostupná */
    public string $url;

    /** @var string náhodný řetězec, kterým se podepisují (http) požadavky na generování miniatur */
    public string $signingKey;

    /** @var string|null absolutní cesta k document rootu webu */
    public ?string $basepath;

    /** @var string|null absolutní cesta k obrázku, který se použije v případě, že požadovaný obrázek neexistuje */
    public ?string $fallbackImage;

    /**
     * @var array<string, string> definice pojmenovaných výchozích obrázků ve tvaru
     * název obrázku => absolutní cesta k obrázku, které je možné v Palette query použít pomocí FallbackImg
     */
    public array $fallbackImages;

    /**
     * @var array<string, string> pole šablon ve tvaru název šablony => paletteQuery.
     * Šablony je možné používat v palette query přes . např.: .template
     */
    public array $template;

    /** @var string|null adresa aplikace s lomítkem na konci pro generování absolutních url adres k obrázkům v cli */
    public ?string $websiteUrl;

    /**
     * @var string|null služba implementující interface IPictureLoader,
     * přes kterou je možné upravit logiku načítání a generování obrázků přes Palette
     */
    public ?string $pictureLoader;

    /**
     * @var bool|string Jak se má pracovat s výjímkami při generování obrázků?
     * true (default) - výjímky se logují přes tracy,
     * false - výjímky se vyhazují,
     * string - výjímky se logují do souboru přes tracy.
     */
    public bool|string $handleException;


    /**
     * PaletteConfig constructor.
     * @param array<string, string|int|string[]|null>|stdClass $config
     */
    public function __construct(array|stdClass $config)
    {
        if ($config instanceof stdClass)
        {
            $config = (array) $config;
        }

        $this->path = $config['path'];
        $this->url = $config['url'];
        $this->signingKey = $config['signingKey'];
        $this->basepath = $config['basepath'];
        $this->fallbackImage = $config['fallbackImage'];
        $this->fallbackImages = $config['fallbackImages'];
        $this->template = $config['template'];
        $this->websiteUrl = $config['websiteUrl'];
        $this->pictureLoader = $config['pictureLoader'];
        $this->handleException = $config['handleException'];
    }


    /**
     * Definice struktury konfigurace rozšíření.
     */
    public static function getConfigSchema(): Schema
    {
        return Expect::structure([
            'path' => Expect::string()->required(),
            'url' => Expect::string()->required(),
            'signingKey' => Expect::string()->required(),
            'basepath' => Expect::string()->nullable(),
            'fallbackImage' => Expect::string()->nullable(),
            'fallbackImages' => Expect::arrayOf(
                valueType: Expect::string()->required(),
                keyType: Expect::string()->required(),
            ),
            'template' => Expect::arrayOf(
                valueType: Expect::string()->required(),
                keyType: Expect::string()->required(),
            ),
            'websiteUrl' => Expect::string()->nullable(),
            'pictureLoader' => Expect::string()->nullable(),
            'handleException' => Expect::anyOf(Expect::string(), Expect::bool())->default(true),
        ]);
    }
}
