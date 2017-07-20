<?php

/**
 * Import classes
 */
use DecodeLLC\ImageMagick\Exception as ImageMagickException;
use DecodeLLC\ImageMagick\Image;
use DecodeLLC\ImageMagick\Dispatcher;

/**
 * Registration required constants
 */
const DECODELLC_IMAGEMAGICK_PATH_BIN_CONVERT = '/usr/local/bin/convert';
const DECODELLC_IMAGEMAGICK_PATH_BIN_IDENTIFY = '/usr/local/bin/identify';

/**
 * Registration recommended constants
 */
const DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC = __DIR__ . '/../resources/profiles/Adobe/CMYK/JapanColor2001Uncoated.icc';
const DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_SRGB_ICC = __DIR__ . '/../resources/profiles/color.org/sRGB2014.icc';

/**
 * Library classes autoload
 */
require_once __DIR__ . '/../autoload.php';

// ---

try
{
	$image = __DIR__ . '/example.jpg';

	$dispatcher = new Dispatcher(new Image($image));

	$dispatcher->getConvertInstance()->resize(512, 512);

	$dispatcher->getImage()->save(__DIR__ . '/example.processed.png');
}

catch (ImageMagickException $e)
{
	echo $e->getMessage(), PHP_EOL;
}
