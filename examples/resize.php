<?php

/**
 * Import classes
 */
use DecodeLLC\ImageMagick\Dispatcher as ImageMagickDispatcher;
use DecodeLLC\ImageMagick\Exception as ImageMagickException;
use DecodeLLC\ImageMagick\Image as ImageMagickImage;

/**
 * Registration of required constants
 */
const DECODELLC_IMAGEMAGICK_PATH_BIN_CONVERT = '/usr/local/bin/convert';
const DECODELLC_IMAGEMAGICK_PATH_BIN_IDENTIFY = '/usr/local/bin/identify';

/**
 * Registration of recommended constants
 */
const DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC = __DIR__ . '/../resources/profiles/Adobe/CMYK/JapanColor2001Coated.icc';
// const DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC = __DIR__ . '/../resources/profiles/Adobe/CMYK/JapanColor2001Uncoated.icc';
const DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_SRGB_ICC = __DIR__ . '/../resources/profiles/color.org/sRGB2014.icc';

/**
 * Library classes autoload
 */
require_once __DIR__ . '/../autoload.php';

/**
 * Create canvas
 *
 * @param   int   $width
 * @param   int   $height
 *
 * @return  \DecodeLLC\ImageMagick\Dispatcher
 */
function createCanvas(int $width, int $height) : ImageMagickDispatcher
{
	$canvas = new ImageMagickDispatcher(
		new ImageMagickImage(__DIR__ . '/../resources/images/blank.png')
	);

	$canvas->getConvertInstance()->resize($width, $height);

	$canvas->getConvertInstance()->execute('{bin} "{format}:{image}" -fill "#FFFFFF00" -opaque "#FFFFFF00" "{format}:{image}"');

	return $canvas;
}

/**
 * Resize the image by 25 percent
 *
 * @param   \DecodeLLC\ImageMagick\Dispatcher $dispatcher
 *
 * @return  bool
 */
function resizeTheImageByOneQuart(ImageMagickDispatcher $dispatcher) : bool
{
	$resizeWidth = round(($dispatcher->getImage()->getWidth() - (($dispatcher->getImage()->getWidth() / 100) * 25)), 0, PHP_ROUND_HALF_DOWN);
	$resizeHeight = round(($dispatcher->getImage()->getHeight() - (($dispatcher->getImage()->getHeight() / 100) * 25)), 0, PHP_ROUND_HALF_DOWN);

	return $dispatcher->getConvertInstance()->resize($resizeWidth, $resizeHeight);
}

/**
 * Crop the image by 25 percent
 *
 * @param   \DecodeLLC\ImageMagick\Dispatcher $dispatcher
 *
 * @return  bool
 */
function cropTheImageByOneQuart(ImageMagickDispatcher $dispatcher) : bool
{
	$cropWidth = round(($dispatcher->getImage()->getWidth() - (($dispatcher->getImage()->getWidth() / 100) * 25)), 0, PHP_ROUND_HALF_DOWN);
	$cropHeight = round(($dispatcher->getImage()->getHeight() - (($dispatcher->getImage()->getHeight() / 100) * 25)), 0, PHP_ROUND_HALF_DOWN);

	$cropPositionX = round((($dispatcher->getImage()->getWidth() - $cropWidth) / 2), 0, PHP_ROUND_HALF_DOWN);
	$cropPositionY = round((($dispatcher->getImage()->getHeight() - $cropHeight) / 2), 0, PHP_ROUND_HALF_DOWN);

	return $dispatcher->getConvertInstance()->crop($cropWidth, $cropHeight, $cropPositionX, $cropPositionY);
}

/**
 * Convert the image to CMYK JPEG
 *
 * @param   \DecodeLLC\ImageMagick\Dispatcher   $dispatcher
 *
 * @return  bool
 */
function CMYKJPEG(ImageMagickDispatcher $dispatcher) : bool
{
	$context = [
		'cmyk.profile' => realpath(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC),
		'srgb.profile' => realpath(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_SRGB_ICC),
	];

	if (strcmp($dispatcher->getImage()->getFormat(), 'PNG') === 0)
	{
		$command = '{bin} "{format}:{image}" -fill "#FFFFFF00" -opaque "#FFFFFF00" "{format}:{image}"';

		$dispatcher->getConvertInstance()->execute($command);
	}

	if ($dispatcher->getImage()->hasProperty(ImageMagickImage::PROPERTY_ICC_PROFILE))
	{
		$command = '{bin} "{format}:{image}" +profile icm -profile "icc:{profile.icc}" -profile "icc:{cmyk.profile}" "jpeg:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	if ($dispatcher->getImage()->hasProperty(ImageMagickImage::PROPERTY_IPTC_PROFILE))
	{
		$command = '{bin} "{format}:{image}" +profile icm -profile "iptc:{profile.iptc}" -profile "icc:{cmyk.profile}" "jpeg:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	if ($dispatcher->getImage()->isRGB() || $dispatcher->getImage()->isSRGB())
	{
		$command = '{bin} "{format}:{image}" +profile icm -profile "icc:{srgb.profile}" -profile "icc:{cmyk.profile}" "jpeg:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	if ($dispatcher->getImage()->isCMY() || $dispatcher->getImage()->isCMYK())
	{
		$command = '{bin} "{format}:{image}" +profile icm -profile "icc:{cmyk.profile}" "jpeg:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	$command = '{bin} "{format}:{image}" +profile icm -colorspace CMYK -profile "icc:{cmyk.profile}" "jpeg:{image}"';

	return $dispatcher->getConvertInstance()->execute($command, $context);
}

/**
 * Convert the image to CMYK PDF
 *
 * @param   \DecodeLLC\ImageMagick\Dispatcher   $dispatcher
 * @param   int                                 $dpi
 *
 * @return  bool
 */
function CMYKPDF(ImageMagickDispatcher $dispatcher, int $dpi = 360) : bool
{
	$context = [
		'dpi' => $dpi,
		'cmyk.profile' => realpath(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC),
		'srgb.profile' => realpath(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_SRGB_ICC),
	];

	if (strcmp($dispatcher->getImage()->getFormat(), 'PNG') === 0)
	{
		$command = '{bin} "{format}:{image}" -fill "#FFFFFF00" -opaque "#FFFFFF00" "{format}:{image}"';

		$dispatcher->getConvertInstance()->execute($command);
	}

	if ($dispatcher->getImage()->hasProperty(ImageMagickImage::PROPERTY_ICC_PROFILE))
	{
		$command = '{bin} "{format}:{image}" +profile icm -profile "icc:{profile.icc}" -compress zip -quality 100 -units PixelsPerInch -density {dpi} -intent relative -black-point-compensation -profile "icc:{cmyk.profile}" -set profile "icc:{cmyk.profile}" "pdf:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	if ($dispatcher->getImage()->hasProperty(ImageMagickImage::PROPERTY_IPTC_PROFILE))
	{
		$command = '{bin} "{format}:{image}" +profile icm -profile "iptc:{profile.iptc}" -compress zip -quality 100 -units PixelsPerInch -density {dpi} -intent relative -black-point-compensation -profile "icc:{cmyk.profile}" -set profile "icc:{cmyk.profile}" "pdf:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	if ($dispatcher->getImage()->isRGB() || $dispatcher->getImage()->isSRGB())
	{
		$command = '{bin} "{format}:{image}" +profile icm -profile "icc:{srgb.profile}" -compress zip -quality 100 -units PixelsPerInch -density {dpi} -intent relative -black-point-compensation -profile "icc:{cmyk.profile}" -set profile "icc:{cmyk.profile}" "pdf:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	if ($dispatcher->getImage()->isCMY() || $dispatcher->getImage()->isCMYK())
	{
		$command = '{bin} "{format}:{image}" +profile icm -compress zip -quality 100 -units PixelsPerInch -density {dpi} -intent relative -black-point-compensation -profile "icc:{cmyk.profile}" -set profile "icc:{cmyk.profile}" "pdf:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	$command = '{bin} "{format}:{image}" +profile icm -compress zip -quality 100 -units PixelsPerInch -density {dpi} -intent relative -black-point-compensation -colorspace CMYK -profile "icc:{cmyk.profile}" -set profile "icc:{cmyk.profile}" "pdf:{image}"';

	return $dispatcher->getConvertInstance()->execute($command, $context);
}

/**
 * Convert the image to sRGB PNG
 *
 * @param   \DecodeLLC\ImageMagick\Dispatcher   $dispatcher
 *
 * @return  bool
 */
function SRGBPNG(ImageMagickDispatcher $dispatcher) : bool
{
	$context = [
		'cmyk.profile' => realpath(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC),
		'srgb.profile' => realpath(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_SRGB_ICC),
	];

	if ($dispatcher->getImage()->hasProperty(ImageMagickImage::PROPERTY_ICC_PROFILE))
	{
		$command = '{bin} "{format}:{image}" -profile "icc:{profile.icc}" -profile "icc:{srgb.profile}" -set profile "icc:{srgb.profile}" "png:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	if ($dispatcher->getImage()->hasProperty(ImageMagickImage::PROPERTY_IPTC_PROFILE))
	{
		$command = '{bin} "{format}:{image}" -profile "iptc:{profile.iptc}" -profile "icc:{srgb.profile}" -set profile "icc:{srgb.profile}" "png:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	if ($dispatcher->getImage()->isRGB() || $dispatcher->getImage()->isSRGB())
	{
		$command = '{bin} "{format}:{image}" -profile "icc:{srgb.profile}" -set profile "icc:{srgb.profile}" "png:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	if ($dispatcher->getImage()->isCMY() || $dispatcher->getImage()->isCMYK())
	{
		$command = '{bin} "{format}:{image}" -profile "icc:{cmyk.profile}" -profile "icc:{srgb.profile}" -set profile "icc:{srgb.profile}" "png:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	$command = '{bin} "{format}:{image}" -colorspace sRGB -profile "icc:{srgb.profile}" -set profile "icc:{srgb.profile}" "png:{image}"';

	return $dispatcher->getConvertInstance()->execute($command, $context);
}

// ---

// Default local image
$image['local'] = __DIR__ . '/example.jpg';

// Gray (pseudo)
$image['gray'] = 'https://canvath-production.s3.amazonaws.com/uploads/order_details/325110/prints/87060/jpg20170713-23760-4kzo4g.jpg';

// Alpha (with alpha chanel)
$image['alpha'] = 'https://s3-ap-northeast-1.amazonaws.com/case-store-image-sample/printing_images/01/0068/0145/01_0068_0145_c07_ip7_m04.png';

// sRGB with profile
$image['srgb.with.icc'] = 'https://espnfivethirtyeight.files.wordpress.com/2015/06/5708_901_mk0450_v0034r_cmyk.jpg';

// sRGB without profile
$image['srgb.without.icc'] = 'https://images-na.ssl-images-amazon.com/images/S/cmx-images-prod/Item/179501/Previews/94a6f7ea156f0d8b0556fc38704f4c4b._SX1280_QL80_TTD_.jpg';

// CMYK with profile
$image['cmyk.with.icc'] = 'http://www.pthomeandgarden.com/wp-content/uploads/133841244_grass_lines_CMYK.jpg';

// CMYK without profile
$image['cmyk.without.icc'] = 'https://s3-ap-northeast-1.amazonaws.com/case-store-image/printing_images/01/0070/0439/01_0070_0439_c06_ip6s_m01.jpg';

try
{
	$canvasDispatcher = createCanvas(1300, 1300);
	CMYKJPEG($canvasDispatcher);

	$image0Dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image['local']));
	$image0Dispatcher->getConvertInstance()->resize(300, 300);
	CMYKJPEG($image0Dispatcher);
	$canvasDispatcher->getConvertInstance()->execute('{bin} "{format}:{image}" "{overlay.format}:{overlay.image}" -geometry +100+100 -composite "{format}:{image}"', [
		'overlay.format' => $image0Dispatcher->getImage()->getFormat(),
		'overlay.image' => $image0Dispatcher->getImage()->getTemporaryPath(),
	]);

	$image1Dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image['alpha']));
	$image1Dispatcher->getConvertInstance()->resize(300, 300);
	CMYKJPEG($image1Dispatcher);
	$canvasDispatcher->getConvertInstance()->execute('{bin} "{format}:{image}" "{overlay.format}:{overlay.image}" -geometry +100+100 -composite "{format}:{image}"', [
		'overlay.format' => $image1Dispatcher->getImage()->getFormat(),
		'overlay.image' => $image1Dispatcher->getImage()->getTemporaryPath(),
	]);

	$image2Dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image['srgb.with.icc']));
	$image2Dispatcher->getConvertInstance()->resize(300, 300);
	CMYKJPEG($image2Dispatcher);
	$canvasDispatcher->getConvertInstance()->execute('{bin} "{format}:{image}" "{overlay.format}:{overlay.image}" -geometry +100+500 -composite "{format}:{image}"', [
		'overlay.format' => $image2Dispatcher->getImage()->getFormat(),
		'overlay.image' => $image2Dispatcher->getImage()->getTemporaryPath(),
	]);

	$image3Dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image['srgb.without.icc']));
	$image3Dispatcher->getConvertInstance()->resize(300, 300);
	CMYKJPEG($image3Dispatcher);
	$canvasDispatcher->getConvertInstance()->execute('{bin} "{format}:{image}" "{overlay.format}:{overlay.image}" -geometry +100+900 -composite "{format}:{image}"', [
		'overlay.format' => $image3Dispatcher->getImage()->getFormat(),
		'overlay.image' => $image3Dispatcher->getImage()->getTemporaryPath(),
	]);

	$image4Dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image['cmyk.with.icc']));
	$image4Dispatcher->getConvertInstance()->resize(300, 300);
	CMYKJPEG($image4Dispatcher);
	$canvasDispatcher->getConvertInstance()->execute('{bin} "{format}:{image}" "{overlay.format}:{overlay.image}" -geometry +500+100 -composite "{format}:{image}"', [
		'overlay.format' => $image4Dispatcher->getImage()->getFormat(),
		'overlay.image' => $image4Dispatcher->getImage()->getTemporaryPath(),
	]);

	$image5Dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image['cmyk.without.icc']));
	$image5Dispatcher->getConvertInstance()->resize(300, 300);
	CMYKJPEG($image5Dispatcher);
	$canvasDispatcher->getConvertInstance()->execute('{bin} "{format}:{image}" "{overlay.format}:{overlay.image}" -geometry +500+500 -composite "{format}:{image}"', [
		'overlay.format' => $image5Dispatcher->getImage()->getFormat(),
		'overlay.image' => $image5Dispatcher->getImage()->getTemporaryPath(),
	]);

	$image6Dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image['gray']));
	$image6Dispatcher->getConvertInstance()->resize(300, 300);
	CMYKJPEG($image6Dispatcher);
	$canvasDispatcher->getConvertInstance()->execute('{bin} "{format}:{image}" "{overlay.format}:{overlay.image}" -geometry +500+900 -composite "{format}:{image}"', [
		'overlay.format' => $image6Dispatcher->getImage()->getFormat(),
		'overlay.image' => $image6Dispatcher->getImage()->getTemporaryPath(),
	]);

	$canvasDispatcher->getImage()->save(__DIR__ . '/example.canvas.jpg');

	CMYKPDF($canvasDispatcher, 72);
	$canvasDispatcher->getImage()->save(__DIR__ . '/example.canvas.72PDI.pdf');

	CMYKPDF($canvasDispatcher, 360);
	$canvasDispatcher->getImage()->save(__DIR__ . '/example.canvas.360PDI.pdf');
}

catch (ImageMagickException $e)
{
	echo $e->getMessage(), PHP_EOL;
}
