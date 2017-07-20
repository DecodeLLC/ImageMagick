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

		$context = ['new.width' => $width, 'new.height' => $height, 'output.format' => $format];

		return $this->execute('{bin} "{format}:{image}" -resize {new.width}x{new.height} "{output.format}:{image}"', $context);
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
	public function resizeWithFilterLanczos($width, $height, $format = null)
	{
		$format = $format ?: $this->getDispatcher()->getImage()->getFormat();

		$context = ['new.width' => $width, 'new.height' => $height, 'output.format' => $format];

		return $this->execute('{bin} "{format}:{image}" -filter Lanczos -resize {new.width}x{new.height} "{output.format}:{image}"', $context);
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
	public function resizeWithFilterLanczos2($width, $height, $format = null)
	{
		$format = $format ?: $this->getDispatcher()->getImage()->getFormat();

		$context = ['new.width' => $width, 'new.height' => $height, 'output.format' => $format];

		return $this->execute('{bin} "{format}:{image}" -filter Lanczos2 -resize {new.width}x{new.height} "{output.format}:{image}"', $context);
	}

	/**
	 * {description}
	 *
	 * @param   string   $profileCMYK
	 * @param   string   $profileSRGB
	 * @param   string   $outputFormat
	 *
	 * @access  public
	 * @return  bool
	 */
	public function toCMYK($profileCMYK, $profileSRGB = null, $outputFormat = null)
	{
		if (defined('DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_SRGB_ICC') && empty($profileSRGB))
		{
			$profileSRGB = DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_SRGB_ICC;
		}

		$outputFormat = $outputFormat ?: $this->getDispatcher()->getImage()->getFormat();

		if ($this->getDispatcher()->getImage()->isCMY() || $this->getDispatcher()->getImage()->isCMYK())
		{
			if (strcasecmp($this->getDispatcher()->getImage()->getFormat(), $outputFormat) === 0)
			{
				return true;
			}

			$command = '{bin} "{format}:{image}" "{output.format}:{image}"';

			return $this->execute($command, [
				'output.format' => $outputFormat,
			]);
		}

		if ($this->getDispatcher()->getImage()->hasProperty(Image::PROPERTY_ICC_PROFILE))
		{
			$command = '{bin} "{format}:{image}" -profile "icc:{profile.icc}" -intent relative -black-point-compensation -profile "icc:{profile.cmyk.icc}" "{output.format}:{image}"';

			return $this->execute($command, [
				'output.format' => $outputFormat,
				'profile.cmyk.icc' => $profileCMYK,
			]);
		}

		if ($this->getDispatcher()->getImage()->hasProperty(Image::PROPERTY_IPTC_PROFILE))
		{
			$command = '{bin} "{format}:{image}" -profile "iptc:{profile.iptc}" -intent relative -black-point-compensation -profile "icc:{profile.cmyk.icc}" "{output.format}:{image}"';

			return $this->execute($command, [
				'output.format' => $outputFormat,
				'profile.cmyk.icc' => $profileCMYK,
			]);
		}

		if (($this->getDispatcher()->getImage()->isRGB() || $this->getDispatcher()->getImage()->isSRGB()) && isset($profileSRGB))
		{
			$command = '{bin} "{format}:{image}" -profile "icc:{profile.srgb.icc}" -intent relative -black-point-compensation -profile "icc:{profile.cmyk.icc}" "{output.format}:{image}"';

			return $this->execute($command, [
				'output.format' => $outputFormat,
				'profile.srgb.icc' => $profileSRGB,
				'profile.cmyk.icc' => $profileCMYK,
			]);
		}

		$command = '{bin} "{format}:{image}" -intent relative -black-point-compensation -profile "icc:{profile.cmyk.icc}" "{output.format}:{image}"';

		return $this->execute($command, [
			'output.format' => $outputFormat,
			'profile.cmyk.icc' => $profileCMYK,
		]);
	}

	/**
	 * {description}
	 *
	 * @param   string   $profileSRGB
	 * @param   string   $profileCMYK
	 * @param   string   $outputFormat
	 *
	 * @access  public
	 * @return  bool
	 */
	public function toRGB($profileSRGB, $profileCMYK = null, $outputFormat = null)
	{
		if (defined('DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC') && empty($profileCMYK))
		{
			$profileCMYK = DECODELLC_IMAGEMAGICK_PATH_DEFAULT_PROFILE_CMYK_ICC;
		}

		$outputFormat = $outputFormat ?: $this->getDispatcher()->getImage()->getFormat();

		if ($this->getDispatcher()->getImage()->isRGB() || $this->getDispatcher()->getImage()->isSRGB())
		{
			if (strcasecmp($this->getDispatcher()->getImage()->getFormat(), $outputFormat) === 0)
			{
				return true;
			}

			$command = '{bin} "{format}:{image}" "{output.format}:{image}"';

			return $this->execute($command, [
				'output.format' => $outputFormat,
			]);
		}

		if ($this->getDispatcher()->getImage()->hasProperty(Image::PROPERTY_ICC_PROFILE))
		{
			$command = '{bin} "{format}:{image}" -profile "icc:{profile.icc}" -profile "icc:{profile.srgb.icc}" "{output.format}:{image}"';

			return $this->execute($command, [
				'output.format' => $outputFormat,
				'profile.srgb.icc' => $profileSRGB,
			]);
		}

		if ($this->getDispatcher()->getImage()->hasProperty(Image::PROPERTY_IPTC_PROFILE))
		{
			$command = '{bin} "{format}:{image}" -profile "iptc:{profile.iptc}" -profile "icc:{profile.srgb.icc}" "{output.format}:{image}"';

			return $this->execute($command, [
				'output.format' => $outputFormat,
				'profile.srgb.icc' => $profileSRGB,
			]);
		}

		if (($this->getDispatcher()->getImage()->isCMY() || $this->getDispatcher()->getImage()->isCMYK()) && isset($profileCMYK))
		{
			$command = '{bin} "{format}:{image}" -profile "icc:{profile.cmyk.icc}" -profile "icc:{profile.srgb.icc}" "{output.format}:{image}"';

			return $this->execute($command, [
				'output.format' => $outputFormat,
				'profile.cmyk.icc' => $profileCMYK,
				'profile.srgb.icc' => $profileSRGB,
			]);
		}

		$command = '{bin} "{format}:{image}" -profile "icc:{profile.srgb.icc}" "{output.format}:{image}"';

		return $this->execute($command, [
			'output.format' => $outputFormat,
			'profile.srgb.icc' => $profileSRGB,
		]);
	}
}
