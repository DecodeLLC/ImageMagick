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

	$canvas->getConvertInstance()->execute('{bin} "{format}:{image}" -resize {resize.width}x{resize.height}\! -fill "#FFFFFF00" -opaque "#FFFFFF00" "{format}:{image}"', [
		'resize.width' => $width,
		'resize.height' => $height,
	]);

	return $canvas;
}

/**
 * Convert the image to CMYK JPEG
 *
 * @param   \DecodeLLC\ImageMagick\Dispatcher   $dispatcher
 * @param   int                                 $dotsPerInch
 *
 * @return  bool
 */
function CMYKJPEG(ImageMagickDispatcher $dispatcher, int $dotsPerInch = 72) : bool
{
	$context = [
		'cmyk.profile' => realpath(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC),
		'srgb.profile' => realpath(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_SRGB_ICC),
	];

	$context['dotsPerInch'] = $dotsPerInch;

	if (strcmp($dispatcher->getImage()->getFormat(), 'PNG') === 0)
	{
		$dispatcher->getLogger()->add('Removed a transparent background of PNG image.');

		$command = '{bin} "{format}:{image}" -fill "#FFFFFF00" -opaque "#FFFFFF00" "{format}:{image}"';

		$dispatcher->getConvertInstance()->execute($command);
	}

	if ($dispatcher->getImage()->hasProperty(ImageMagickImage::PROPERTY_ICC_PROFILE))
	{
		$dispatcher->getLogger()->add(sprintf('From own ICC profile to %s.',
			basename(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC)));

		$command = '{bin} "{format}:{image}" -units PixelsPerInch -density {dotsPerInch} +profile icm -profile "icc:{profile.icc}" -profile "icc:{cmyk.profile}" -set profile "icc:{cmyk.profile}" "jpeg:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	if ($dispatcher->getImage()->hasProperty(ImageMagickImage::PROPERTY_IPTC_PROFILE))
	{
		$dispatcher->getLogger()->add(sprintf('From own IPTC profile to %s.',
			basename(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC)));

		$command = '{bin} "{format}:{image}" -units PixelsPerInch -density {dotsPerInch} +profile icm -profile "iptc:{profile.iptc}" -profile "icc:{cmyk.profile}" -set profile "icc:{cmyk.profile}" "jpeg:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	if ($dispatcher->getImage()->isRGB() || $dispatcher->getImage()->isSRGB())
	{
		$dispatcher->getLogger()->add(sprintf('From %s to %s.',
			basename(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_SRGB_ICC),
			basename(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC)));

		$command = '{bin} "{format}:{image}" -units PixelsPerInch -density {dotsPerInch} +profile icm -profile "icc:{srgb.profile}" -profile "icc:{cmyk.profile}" -set profile "icc:{cmyk.profile}" "jpeg:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	$dispatcher->getLogger()->add(sprintf('To %s.',
		basename(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC)));

	$command = '{bin} "{format}:{image}" -units PixelsPerInch -density {dotsPerInch} -colorspace CMYK +profile icm -profile "icc:{cmyk.profile}" -set profile "icc:{cmyk.profile}" "jpeg:{image}"';

	return $dispatcher->getConvertInstance()->execute($command, $context);
}

/**
 * Convert the image to CMYK PDF
 *
 * @param   \DecodeLLC\ImageMagick\Dispatcher   $dispatcher
 * @param   int                                 $dotsPerInch
 *
 * @return  bool
 */
function CMYKPDF(ImageMagickDispatcher $dispatcher, int $dotsPerInch = 72) : bool
{
	//
	// @see http://www.imagemagick.org/Usage/files/#massive
	//
	// ```
	// env MAGICK_TMPDIR=/tmp nice -5 \
	// {bin} -limit memory 32 -limit map 32 \
	// "{format}:{image}" -units PixelsPerInch -resample {dotsPerInch} "pdf:{image}"
	// ```
	//

	$context = [
		'cmyk.profile' => realpath(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC),
		'srgb.profile' => realpath(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_SRGB_ICC),
	];

	$context['dotsPerInch'] = $dotsPerInch;

	if (strcmp($dispatcher->getImage()->getFormat(), 'PNG') === 0)
	{
		$dispatcher->getLogger()->add('Removed a transparent background of PNG image.');

		$command = '{bin} "{format}:{image}" -fill "#FFFFFF00" -opaque "#FFFFFF00" "{format}:{image}"';

		$dispatcher->getConvertInstance()->execute($command);
	}

	if ($dispatcher->getImage()->hasProperty(ImageMagickImage::PROPERTY_ICC_PROFILE))
	{
		$dispatcher->getLogger()->add(sprintf('From own ICC profile to %s.',
			basename(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC)));

		$command = '{bin} "{format}:{image}" -units PixelsPerInch -density {dotsPerInch} -compress zip -quality 100 -intent relative -black-point-compensation +profile icm -profile "icc:{profile.icc}" -profile "icc:{cmyk.profile}" -set profile "icc:{cmyk.profile}" "pdf:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	if ($dispatcher->getImage()->hasProperty(ImageMagickImage::PROPERTY_IPTC_PROFILE))
	{
		$dispatcher->getLogger()->add(sprintf('From own IPTC profile to %s.',
			basename(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC)));

		$command = '{bin} "{format}:{image}" -units PixelsPerInch -density {dotsPerInch} -compress zip -quality 100 -intent relative -black-point-compensation +profile icm -profile "iptc:{profile.iptc}" -profile "icc:{cmyk.profile}" -set profile "icc:{cmyk.profile}" "pdf:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	if ($dispatcher->getImage()->isRGB() || $dispatcher->getImage()->isSRGB())
	{
		$dispatcher->getLogger()->add(sprintf('From %s to %s.',
			basename(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_SRGB_ICC),
			basename(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC)));

		$command = '{bin} "{format}:{image}" -units PixelsPerInch -density {dotsPerInch} -compress zip -quality 100 -intent relative -black-point-compensation +profile icm -profile "icc:{srgb.profile}" -profile "icc:{cmyk.profile}" -set profile "icc:{cmyk.profile}" "pdf:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	$dispatcher->getLogger()->add(sprintf('To %s.',
		basename(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC)));

	$command = '{bin} "{format}:{image}" -units PixelsPerInch -density {dotsPerInch} -compress zip -quality 100 -intent relative -black-point-compensation -colorspace CMYK +profile icm -profile "icc:{cmyk.profile}" -set profile "icc:{cmyk.profile}" "pdf:{image}"';

	return $dispatcher->getConvertInstance()->execute($command, $context);
}

/**
 * Convert the image to sRGB PNG
 *
 * @param   \DecodeLLC\ImageMagick\Dispatcher   $dispatcher
 * @param   int                                 $dotsPerInch
 *
 * @return  bool
 */
function SRGBPNG(ImageMagickDispatcher $dispatcher, int $dotsPerInch = 72) : bool
{
	$context = [
		'cmyk.profile' => realpath(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC),
		'srgb.profile' => realpath(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_SRGB_ICC),
	];

	$context['dotsPerInch'] = $dotsPerInch;

	if ($dispatcher->getImage()->hasProperty(ImageMagickImage::PROPERTY_ICC_PROFILE))
	{
		$dispatcher->getLogger()->add(sprintf('From own ICC profile to %s.',
			basename(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_SRGB_ICC)));

		$command = '{bin} "{format}:{image}" -units PixelsPerInch -density {dotsPerInch} +profile icm -profile "icc:{profile.icc}" -profile "icc:{srgb.profile}" -set profile "icc:{srgb.profile}" "png:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	if ($dispatcher->getImage()->hasProperty(ImageMagickImage::PROPERTY_IPTC_PROFILE))
	{
		$dispatcher->getLogger()->add(sprintf('From own IPTC profile to %s.',
			basename(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_SRGB_ICC)));

		$command = '{bin} "{format}:{image}" -units PixelsPerInch -density {dotsPerInch} +profile icm -profile "iptc:{profile.iptc}" -profile "icc:{srgb.profile}" -set profile "icc:{srgb.profile}" "png:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	if ($dispatcher->getImage()->isCMY() || $dispatcher->getImage()->isCMYK())
	{
		$dispatcher->getLogger()->add(sprintf('From %s to %s.',
			basename(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC),
			basename(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_SRGB_ICC)));

		$command = '{bin} "{format}:{image}" -units PixelsPerInch -density {dotsPerInch} +profile icm -profile "icc:{cmyk.profile}" -profile "icc:{srgb.profile}" -set profile "icc:{srgb.profile}" "png:{image}"';

		return $dispatcher->getConvertInstance()->execute($command, $context);
	}

	$dispatcher->getLogger()->add(sprintf('To %s.',
		basename(DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_SRGB_ICC)));

	$command = '{bin} "{format}:{image}" -units PixelsPerInch -density {dotsPerInch} -colorspace sRGB +profile icm -profile "icc:{srgb.profile}" -set profile "icc:{srgb.profile}" "png:{image}"';

	return $dispatcher->getConvertInstance()->execute($command, $context);
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

// ---

// echo PHP_EOL, str_repeat('-', 50), PHP_EOL.PHP_EOL;

// $start = microtime(true);

// echo 'Creating canvas...', PHP_EOL;
// $dispatcher = createCanvas(216, 216);
// echo 'Complete.', PHP_EOL, PHP_EOL;

// echo 'Drawing...', PHP_EOL;
// $dispatcher->getConvertInstance()->execute('{bin} "{format}:{image}" -fill black -draw "circle {x0},{y0} {x1},{y1}" "{format}:{image}"', [
// 	'x0' => 100,
// 	'y0' => 100,
// 	'x1' => ceil(100 + 16),
// 	'y1' => ceil(100 + 16),
// ]);
// echo 'Complete.', PHP_EOL, PHP_EOL;

// echo 'Saving the canvas on disk as PNG file...', PHP_EOL;
// $dispatcher->getImage()->save(__DIR__ . '/example.drawing.png');
// echo 'Complete.', PHP_EOL, PHP_EOL;

// echo 'Total time: ' . (microtime(true) - $start) . ' seconds.', PHP_EOL.PHP_EOL;

// return;

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

// $image = 'https://canvath-production.s3.amazonaws.com/uploads/order_details/327522/prints/89461/jpg20170522-11877-180lws6.jpg';

// echo 'Create document with resolution of 72 DPI...', PHP_EOL;
// $dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image));
// CMYKPDF($dispatcher, 72);
// $dispatcher->getImage()->save(__DIR__ . '/test.72dpi.pdf');
// $dispatcher->getImage()->destroy();
// echo 'Complete.', PHP_EOL, PHP_EOL;

// echo 'Create document with resolution of 150 DPI...', PHP_EOL;
// $dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image));
// CMYKPDF($dispatcher, 150);
// $dispatcher->getImage()->save(__DIR__ . '/test.150dpi.pdf');
// $dispatcher->getImage()->destroy();
// echo 'Complete.', PHP_EOL, PHP_EOL;

// echo 'Create document with resolution of 300 DPI...', PHP_EOL;
// $dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image));
// CMYKPDF($dispatcher, 300);
// $dispatcher->getImage()->save(__DIR__ . '/test.300dpi.pdf');
// $dispatcher->getImage()->destroy();
// echo 'Complete.', PHP_EOL, PHP_EOL;

// echo 'Create document with resolution of 360 DPI...', PHP_EOL;
// $dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image));
// CMYKPDF($dispatcher, 360);
// $dispatcher->getImage()->save(__DIR__ . '/test.360dpi.pdf');
// $dispatcher->getImage()->destroy();
// echo 'Complete.', PHP_EOL, PHP_EOL;

// return;

try
{
	$start = microtime(true);

	echo 'Creating canvas...', PHP_EOL;
	$canvasDispatcher = createCanvas(1300, 1300);
	CMYKJPEG($canvasDispatcher);
	echo 'Complete.', PHP_EOL, PHP_EOL;

	echo 'Overlaying image on canvas...', PHP_EOL;
	$image0Dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image['local']));
	$image0Dispatcher->getConvertInstance()->resize(300, 300);
	CMYKJPEG($image0Dispatcher);
	// SRGBPNG($image0Dispatcher);
	$canvasDispatcher->getConvertInstance()->execute('{bin} "{format}:{image}" "{overlay.format}:{overlay.image}" -geometry +100+100 -composite "{format}:{image}"', [
		'overlay.format' => $image0Dispatcher->getImage()->getFormat(),
		'overlay.image' => $image0Dispatcher->getImage()->getTemporaryPath(),
	]);
	print_r($image0Dispatcher->getLogger()->all());
	echo 'Complete.', PHP_EOL, PHP_EOL;

	echo 'Overlaying image on canvas...', PHP_EOL;
	$image1Dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image['alpha']));
	$image1Dispatcher->getConvertInstance()->resize(300, 300);
	CMYKJPEG($image1Dispatcher);
	// SRGBPNG($image1Dispatcher);
	$canvasDispatcher->getConvertInstance()->execute('{bin} "{format}:{image}" "{overlay.format}:{overlay.image}" -geometry +100+100 -composite "{format}:{image}"', [
		'overlay.format' => $image1Dispatcher->getImage()->getFormat(),
		'overlay.image' => $image1Dispatcher->getImage()->getTemporaryPath(),
	]);
	print_r($image1Dispatcher->getLogger()->all());
	echo 'Complete.', PHP_EOL, PHP_EOL;

	echo 'Overlaying image on canvas...', PHP_EOL;
	$image2Dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image['srgb.with.icc']));
	$image2Dispatcher->getConvertInstance()->resize(300, 300);
	CMYKJPEG($image2Dispatcher);
	// SRGBPNG($image2Dispatcher);
	$canvasDispatcher->getConvertInstance()->execute('{bin} "{format}:{image}" "{overlay.format}:{overlay.image}" -geometry +100+500 -composite "{format}:{image}"', [
		'overlay.format' => $image2Dispatcher->getImage()->getFormat(),
		'overlay.image' => $image2Dispatcher->getImage()->getTemporaryPath(),
	]);
	print_r($image2Dispatcher->getLogger()->all());
	echo 'Complete.', PHP_EOL, PHP_EOL;

	echo 'Overlaying image on canvas...', PHP_EOL;
	$image3Dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image['srgb.without.icc']));
	$image3Dispatcher->getConvertInstance()->resize(300, 300);
	CMYKJPEG($image3Dispatcher);
	// SRGBPNG($image3Dispatcher);
	$canvasDispatcher->getConvertInstance()->execute('{bin} "{format}:{image}" "{overlay.format}:{overlay.image}" -geometry +100+900 -composite "{format}:{image}"', [
		'overlay.format' => $image3Dispatcher->getImage()->getFormat(),
		'overlay.image' => $image3Dispatcher->getImage()->getTemporaryPath(),
	]);
	print_r($image3Dispatcher->getLogger()->all());
	echo 'Complete.', PHP_EOL, PHP_EOL;

	echo 'Overlaying image on canvas...', PHP_EOL;
	$image4Dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image['cmyk.with.icc']));
	$image4Dispatcher->getConvertInstance()->resize(300, 300);
	CMYKJPEG($image4Dispatcher);
	// SRGBPNG($image4Dispatcher);
	$canvasDispatcher->getConvertInstance()->execute('{bin} "{format}:{image}" "{overlay.format}:{overlay.image}" -geometry +500+100 -composite "{format}:{image}"', [
		'overlay.format' => $image4Dispatcher->getImage()->getFormat(),
		'overlay.image' => $image4Dispatcher->getImage()->getTemporaryPath(),
	]);
	print_r($image4Dispatcher->getLogger()->all());
	echo 'Complete.', PHP_EOL, PHP_EOL;

	echo 'Overlaying image on canvas...', PHP_EOL;
	$image5Dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image['cmyk.without.icc']));
	$image5Dispatcher->getConvertInstance()->resize(300, 300);
	CMYKJPEG($image5Dispatcher);
	// SRGBPNG($image5Dispatcher);
	$canvasDispatcher->getConvertInstance()->execute('{bin} "{format}:{image}" "{overlay.format}:{overlay.image}" -geometry +500+500 -composite "{format}:{image}"', [
		'overlay.format' => $image5Dispatcher->getImage()->getFormat(),
		'overlay.image' => $image5Dispatcher->getImage()->getTemporaryPath(),
	]);
	print_r($image5Dispatcher->getLogger()->all());
	echo 'Complete.', PHP_EOL, PHP_EOL;

	echo 'Overlaying image on canvas...', PHP_EOL;
	$image6Dispatcher = new ImageMagickDispatcher(new ImageMagickImage($image['gray']));
	$image6Dispatcher->getConvertInstance()->resize(300, 300);
	CMYKJPEG($image6Dispatcher);
	// SRGBPNG($image6Dispatcher);
	$canvasDispatcher->getConvertInstance()->execute('{bin} "{format}:{image}" "{overlay.format}:{overlay.image}" -geometry +500+900 -composite "{format}:{image}"', [
		'overlay.format' => $image6Dispatcher->getImage()->getFormat(),
		'overlay.image' => $image6Dispatcher->getImage()->getTemporaryPath(),
	]);
	print_r($image6Dispatcher->getLogger()->all());
	echo 'Complete.', PHP_EOL, PHP_EOL;

	echo 'Saving the canvas on disk as PDF file...', PHP_EOL;
	CMYKPDF($canvasDispatcher, 360);
	$canvasDispatcher->getImage()->save(__DIR__ . '/example.canvas.pdf');
	echo 'Complete.', PHP_EOL, PHP_EOL;

	echo 'Saving the canvas on disk as PNG file...', PHP_EOL;
	SRGBPNG($canvasDispatcher);
	$canvasDispatcher->getImage()->save(__DIR__ . '/example.canvas.png');
	echo 'Complete.', PHP_EOL, PHP_EOL;

	echo 'Saving the canvas on disk as JPEG file...', PHP_EOL;
	CMYKJPEG($canvasDispatcher);
	$canvasDispatcher->getImage()->save(__DIR__ . '/example.canvas.jpg');
	echo 'Complete.', PHP_EOL, PHP_EOL;

	print_r($canvasDispatcher->getLogger()->all());
	echo 'Total time: ' . (microtime(true) - $start) . ' seconds.', PHP_EOL.PHP_EOL;
}

catch (ImageMagickException $e)
{
	echo $e->getMessage(), PHP_EOL;
}
