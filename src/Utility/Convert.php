<?php

namespace DecodeLLC\ImageMagick\Utility;

use DecodeLLC\ImageMagick\Utility as AbstractUtility;
use DecodeLLC\ImageMagick\Image;

/**
 * {description}
 */
class Convert extends AbstractUtility
{

	/**
	 * {description}
	 *
	 * @param   string   $format
	 *
	 * @access  public
	 * @return  bool
	 */
	public function flip($format = null)
	{
		$format = $format ?: $this->getDispatcher()->getImage()->getFormat();

		return $this->execute('{bin} "{format}:{image}" -flip "{output.format}:{image}"', ['output.format' => $format]);
	}

	/**
	 * {description}
	 *
	 * @param   string   $format
	 *
	 * @access  public
	 * @return  bool
	 */
	public function flop($format = null)
	{
		$format = $format ?: $this->getDispatcher()->getImage()->getFormat();

		return $this->execute('{bin} "{format}:{image}" -flop "{output.format}:{image}"', ['output.format' => $format]);
	}

	/**
	 * {description}
	 *
	 * @param   int      $width
	 * @param   int      $height
	 * @param   string   $format
	 *
	 * @access  public
	 * @return  bool
	 */
	public function resize($width, $height, $format = null)
	{
		$format = $format ?: $this->getDispatcher()->getImage()->getFormat();

		$context = [
			'new.width' => $width,
			'new.height' => $height,
			'output.format' => $format,
		];

		return $this->execute('{bin} "{format}:{image}" -resize {new.width}x{new.height} "{output.format}:{image}"', $context);
	}

	/**
	 * {description}
	 *
	 * @param   int      $width
	 * @param   int      $height
	 * @param   int      $xPosition
	 * @param   int      $yPosition
	 * @param   string   $format
	 *
	 * @access  public
	 * @return  bool
	 */
	public function crop($width, $height, $xPosition = 0, $yPosition = 0, $format = null)
	{
		$format = $format ?: $this->getDispatcher()->getImage()->getFormat();

		$context = [
			'new.width' => $width,
			'new.height' => $height,
			'x.position' => $xPosition,
			'y.position' => $yPosition,
			'output.format' => $format,
		];

		return $this->execute('{bin} "{format}:{image}" -crop {new.width}x{new.height}+{x.position}+{y.position} "{output.format}:{image}"', $context);
	}

	/**
	 * {description}
	 *
	 * @param   int      $radius
	 * @param   string   $background
	 * @param   string   $format
	 *
	 * @access  public
	 * @return  bool
	 */
	public function roundCorners($radius, $background = 'white', $format = null)
	{
		$format = $format ?: $this->getDispatcher()->getImage()->getFormat();

		$command[] = '{bin} -size {width}x{height} canvas:none -draw "roundRectangle 0,0,{width},{height},{radius},{radius}" "PNG:{image}.round"';

		switch ($format)
		{
			case 'GIF' :
			case 'PNG' :
				$command[] = '{bin} "{format}:{image}" -matte "PNG:{image}.round" -compose DstIn -composite "{output.format}:{image}"';
				break;

			default :
				$command[] = '{bin} "{format}:{image}" -matte "PNG:{image}.round" -compose DstIn -composite "{output.format}:{image}"';

				// $command[] = '{bin} "{format}:{image}" -matte "PNG:{image}.round" -compose DstIn -composite "PNG:{image}.round"';
				// $command[] = '{bin} "PNG:{image}.round" -background {background} -flatten "{output.format}:{image}"';
				break;
		}

		$command[] = 'rm -f "{image}.round"';

		$context = [
			'radius' => $radius,
			'background' => $background,
			'output.format' => $format,
		];

		return $this->execute(implode(' && ', $command), $context);
	}
}
