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

use Nette\Utils\Strings;
use Palette\Exception;
use Palette\Generator\IPictureLoader;
use Palette\Picture;
use Palette\Generator\Server;
use Palette\SecurityException;
use Tracy\Debugger;
use Nette\Application\BadRequestException;

/**
 * Palette service implementation for Nette Framework
 * Class Palette
 * @package NettePalette
 */
class Palette
{
    /** @var Server */
    protected $generator;

    /** @var string|null */
    protected $websiteUrl;

    /** @var bool is used relative urls for images? */
    protected $isUrlRelative;

    /** @var bool|string generator exceptions handling
     * FALSE = exceptions are thrown
     * TRUE = exceptions are begin detailed logged via Tracy\Debugger
     * string = only exception messages are begin logged to specified log file via Tracy\Debugger
     */
    protected $handleExceptions = TRUE;


    /**
     * Palette constructor.
     * @param string $storagePath absolute or relative path to generated thumbs (and pictures) directory
     * @param string $storageUrl absolute live url to generated thumbs (and pictures) directory
     * @param null|string $basePath absolute path to website root directory
     * @param string $signingKey
     * @param null|string $fallbackImage absolute or relative path to default image.
     * @param null $templates palette image query templates
     * @param null|string $websiteUrl
     * @param IPictureLoader|NULL $pictureLoader
     * @throws
     */
    public function __construct($storagePath,
                                $storageUrl,
                                $basePath,
                                $signingKey,
                                $fallbackImage = NULL,
                                $templates = NULL,
                                $websiteUrl = NULL,
                                IPictureLoader $pictureLoader = NULL)
    {
        // Setup image generator instance
        $this->generator = new Server($storagePath, $storageUrl, $basePath, $signingKey);

        // Register fallback image
        if($fallbackImage)
        {
            $this->generator->setFallbackImage($fallbackImage);
        }

        // Register defined image query templates
        if($templates && is_array($templates))
        {
            foreach ($templates as $templateName => $templateQuery)
            {
                $this->generator->setTemplateQuery($templateName, $templateQuery);
            }
        }

        // Set website url (optional)
        $this->websiteUrl = $websiteUrl;

        // Is used relative urls for images?
        $this->isUrlRelative =
            !Strings::startsWith($storageUrl, '//') &&
            !Strings::startsWith($storageUrl, 'http://') &&
            !Strings::startsWith($storageUrl, 'https://');

        // Set custom picture loader
        if($pictureLoader)
        {
            $this->generator->setPictureLoader($pictureLoader);
        }
    }


    /**
     * Set generator exceptions handling (image generation via url link)
     * FALSE = exceptions are thrown
     * TRUE = exceptions are begin detailed logged via Tracy\Debugger
     * string = only exception messages are begin logged to specified log file via Tracy\Debugger
     * @param $handleExceptions
     * @throws Exception
     */
    public function setHandleExceptions($handleExceptions)
    {
        if(is_bool($handleExceptions) || is_string($handleExceptions))
        {
            $this->handleExceptions = $handleExceptions;
        }
        else
        {
            throw new Exception('Invalid value for handleExceptions in configuration');
        }
    }


    /**
     * Get absolute url to image with specified image query string
     * @param $image
     * @return null|string
     * @throws
     */
    public function __invoke($image)
    {
        return $this->generator->loadPicture($image)->getUrl();
    }


    /**
     * Get url to image with specified image query string
     * Supports absolute picture url when is relative generator url set
     * @param $image
     * @param null $imageQuery
     * @return null|string
     */
    public function getUrl($image, $imageQuery = NULL)
    {
        // Experimental support for absolute picture url when is relative generator url set
        if($imageQuery && Strings::startsWith($imageQuery, '//'))
        {
            $imageQuery = Strings::substring($imageQuery, 2);
            $imageUrl   = $this->getPictureGeneratorUrl($image, $imageQuery);

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

        return $this->getPictureGeneratorUrl($image, $imageQuery);
    }


    /**
     * Get url to image with specified image query string from generator
     * @param $image
     * @param null $imageQuery
     * @return null|string
     * @throws
     */
    protected function getPictureGeneratorUrl($image, $imageQuery = NULL)
    {
        if($imageQuery !== NULL)
        {
            $image .= '@' . $imageQuery;
        }

        return $this->generator->loadPicture($image)->getUrl();
    }


    /**
     * Get Palette picture instance
     * @param $image
     * @return Picture
     * @throws
     */
    public function getPicture($image)
    {
        return $this->generator->loadPicture($image);
    }


    /**
     * Get Palette generator instance
     * @return Server
     */
    public function getGenerator()
    {
        return $this->generator;
    }


    /**
     * Execute palette service generator backend
     * @throws
     */
    public function serverResponse()
    {
        $requestImageQuery = '';

        try
        {
            // Get image query from url.
            $requestImageQuery = $this->generator->getRequestImageQuery();

            // Process server response.
            $this->generator->serverResponse();
        }
        catch(\Exception $exception)
        {
            // Handle server generating image response exception
            if($this->handleExceptions)
            {
                if ($exception instanceof SecurityException)
                {
                    Debugger::log($exception->getMessage(), 'palette.security');

                    throw new BadRequestException("Image doesn't exist");
                }
                elseif(is_string($this->handleExceptions))
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

            // Return fallback image on exception if fallback image is configured
            $fallbackImage = $this->generator->getFallbackImage();

            if($fallbackImage)
            {
                $paletteQuery = preg_replace('/.*@(.*)/', $fallbackImage . '@$1', $requestImageQuery);

                $picture  = $this->generator->loadPicture($paletteQuery);
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
}
