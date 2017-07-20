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
 * Library classes autoload
 */
require_once __DIR__ . '/../autoload.php';

// ---

try
{
	$image = __DIR__ . '/example.jpg';

	$dispatcher = new Dispatcher(new Image($image));

	var_dump($dispatcher->getConvertInstance()->execute('{bin} "{format}:{image}" -resize 100x100 "{format}:{image}"'));

	var_dump($dispatcher->getImage()->save(__DIR__ . '/example.resized.jpg'));
}

catch (ImageMagickException $e)
{
	echo $e->getMessage(), PHP_EOL;
}
