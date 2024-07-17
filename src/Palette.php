<?php declare(strict_types=1);

/**
 * This file is part of the Nette Palette (https://github.com/MichaelPavlista/nette-palette)
 * Copyright (c) 2016 Michael Pavlistal (https://www.pavlista.cz)
 */

namespace NettePalette;

use Nette\Application\BadRequestException;
use Nette\Utils\Strings;
use Palette\Exception;
use Palette\Generator\IPictureLoader;
use Palette\Generator\Server;
use Palette\Picture;
use Palette\SecurityException;
use Throwable;
use Tracy\Debugger;

/**
 * Palette Nette služba.
 */
class Palette
{
    protected Server $generator;

    /** @var bool is used relative urls for images? */
    protected bool $isUrlRelative;

    /** @var bool|string generator exceptions handling
     * false = exceptions are thrown
     * true = exceptions are begin detailed logged via Tracy\Debugger
     * string = only exception messages are begin logged to specified log file via Tracy\Debugger
     */
    protected string|bool $handleExceptions = true;


    /**
     * Palette constructor.
     * @param string $storagePath absolute or relative path to generated thumbs (and pictures) directory
     * @param string $storageUrl absolute live url to generated thumbs (and pictures) directory
     * @param string|null $basePath absolute path to website root directory
     * @param string|null $fallbackImage absolute or relative path to default image.
     * @param array<string, string>|null $templates palette image query templates
     * @param array<string, string> $fallbackImages
     * @throws Exception
     */
    public function __construct(
        string $storagePath,
        string $storageUrl,
        ?string $basePath,
        string $signingKey,
        ?string $fallbackImage = null,
        ?array $templates = null,
        array $fallbackImages = [],
        protected ?string $websiteUrl = null,
        ?IPictureLoader $pictureLoader = null,
    )
    {
        // Setup image generator instance
        $this->generator = new Server($storagePath, $storageUrl, $basePath, $signingKey);

        // Register fallback image
        if($fallbackImage)
        {
            $this->generator->setFallbackImage($fallbackImage);
        }

        // Register and validate named default images.
        $this->generator->setNamedFallbackImages($fallbackImages);

        // Control fallback image definitions.
        if (
            $fallbackImages
            && !$fallbackImage
        )
        {
            throw new Exception('Parameter `fallbackImage` is mandatory when parameter `fallbackImages` is filled.');
        }

        // Register defined image query templates
        if ($templates)
        {
            foreach ($templates as $templateName => $templateQuery)
            {
                $this->generator->setTemplateQuery($templateName, $templateQuery);
            }
        }

        // Is used relative urls for images?
        $this->isUrlRelative = !str_starts_with($storageUrl, '//')
            && !str_starts_with($storageUrl, 'http://')
            && !str_starts_with($storageUrl, 'https://');

        // Set custom picture loader
        if ($pictureLoader)
        {
            $this->generator->setPictureLoader($pictureLoader);
        }
    }


    /**
     * Set generator exceptions handling (image generation via url link)
     * FALSE = exceptions are thrown
     * TRUE = exceptions are begin detailed logged via Tracy\Debugger
     * string = only exception messages are begin logged to specified log file via Tracy\Debugger
     */
    public function setHandleExceptions(bool|string $handleExceptions): void
    {
        $this->handleExceptions = $handleExceptions;
    }


    /**
     * Get url to image with specified image query string
     * Supports absolute picture url when is relative generator url set
     * @throws Exception
     */
    public function getUrl(string $image, ?string $imageQuery = null, ?Picture &$picture = null): ?string
    {
        // Experimental support for absolute picture url when is relative generator url set
        if($imageQuery && str_starts_with($imageQuery, '//'))
        {
            $imageQuery = Strings::substring($imageQuery, 2);
            $imageUrl = $this->getPictureGeneratorUrl($image, $imageQuery, $picture);

            if($this->isUrlRelative)
            {
                if($this->websiteUrl)
                {
                    return $this->websiteUrl . $imageUrl;
                }

                return '//' . $_SERVER['SERVER_ADDR'] . $imageUrl;
            }

            return $imageUrl;
        }

        return $this->getPictureGeneratorUrl($image, $imageQuery, $picture);
    }


    /**
     * Get url to image with specified image query string from generator
     * @throws Exception
     */
    protected function getPictureGeneratorUrl(
        string $image,
        ?string $imageQuery = null,
        ?Picture &$picture = null,
    ): ?string
    {
        if($imageQuery !== null)
        {
            $image .= '@' . $imageQuery;
        }

        $picture = $this->generator->loadPicture($image);

        return $picture->getUrl();
    }


    /**
     * Get Palette picture instance
     * @throws Exception
     */
    public function getPicture(string $image): Picture
    {
        return $this->generator->loadPicture($image);
    }


    /**
     * Get Palette generator instance
     */
    public function getGenerator(): Server
    {
        return $this->generator;
    }


    /**
     * Execute palette service generator backend
     * @throws Throwable
     */
    public function serverResponse(): void
    {
        $requestImageQuery = '';

        try
        {
            // Get image query from url.
            $requestImageQuery = $this->generator->getRequestImageQuery();

            // Process server response.
            $this->generator->serverResponse();
        }
        catch(Throwable $exception)
        {
            // Handle server generating image response exception
            if($this->handleExceptions)
            {
                if ($exception instanceof SecurityException)
                {
                    Debugger::log($exception->getMessage(), 'palette.security');

                    throw new BadRequestException("Image doesn't exist");
                }

                if (is_string($this->handleExceptions))
                {
                    Debugger::log($exception->getMessage(), $this->handleExceptions);
                }
                else
                {
                    Debugger::log($exception, 'palette');
                }
            }
            else
            {
                throw $exception;
            }

            // Return fallback image on exception if fallback image is configured.
            if($this->generator->getFallbackImage())
            {
                /** @var string $paletteQuery */
                $paletteQuery = preg_replace('/.*@(.*)/', '@$1', $requestImageQuery);

                $picture = $this->generator->loadPicture($paletteQuery);
                $savePath = $this->generator->getPath($picture);

                if(!file_exists($savePath))
                {
                    $picture->save($savePath);
                }

                $picture->output();
            }

            throw new BadRequestException("Image doesn't exist");
        }
    }


    /**
     * Get absolute url to image with specified image query string
     * @throws Exception
     */
    public function __invoke(string $image): ?string
    {
        return $this->generator->loadPicture($image)->getUrl();
    }
}
