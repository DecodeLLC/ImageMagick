<?php

namespace DecodeLLC\ImageMagick\Utility;

use DecodeLLC\ImageMagick\Utility as AbstractUtility;
use DecodeLLC\ImageMagick\Exception;
use DecodeLLC\ImageMagick\Image;

/**
 * {description}
 */
class Identify extends AbstractUtility
{

	/**
	 * {description}
	 *
	 * @access  public
	 * @return  void
	 */
	public function readImageProperties(& $output = null, & $status = null)
	{
		$this->execute('{bin} -verbose "{image}"', [], $output, $status);

		if ($status !== 0)
		{
			$message = '[%s] Failed to read image properties.';

			throw new Exception(sprintf($message, __METHOD__));
		}

		$profiles = [];

		foreach ($output as $property)
		{
			if (preg_match('/^\s*Geometry:\s*(?<width>\d+)x(?<height>\d+)/i', $property, $matches))
			{
				$this->getDispatcher()->getImage()->setProperty(Image::PROPERTY_WIDTH, (int) $matches['width']);
				$this->getDispatcher()->getImage()->setProperty(Image::PROPERTY_HEIGHT, (int) $matches['height']);
			}

			if (preg_match('/^\s*Format:\s*(?<value>[^\s]+)/i', $property, $matches))
			{
				$this->getDispatcher()->getImage()->setProperty(Image::PROPERTY_FORMAT, $matches['value']);
			}

			if (preg_match('/^\s*Mime\s*type:\s*(?<value>[^\s]+)/i', $property, $matches))
			{
				$this->getDispatcher()->getImage()->setProperty(Image::PROPERTY_MIME_TYPE, $matches['value']);
			}

			if (preg_match('/^\s*Units:\s*(?<value>[^\s]+)/i', $property, $matches))
			{
				$this->getDispatcher()->getImage()->setProperty(Image::PROPERTY_UNITS, $matches['value']);
			}

			if (preg_match('/^\s*Resolution:\s*(?<value>[^\s]+)/i', $property, $matches))
			{
				$this->getDispatcher()->getImage()->setProperty(Image::PROPERTY_RESOLUTION, $matches['value']);
			}

			if (preg_match('/^\s*Depth:\s*(?<value>[^\s]+)/i', $property, $matches))
			{
				$this->getDispatcher()->getImage()->setProperty(Image::PROPERTY_DEPTH, $matches['value']);
			}

			if (preg_match('/^\s*Colorspace:\s*(?<value>[^\s]+)/i', $property, $matches))
			{
				$this->getDispatcher()->getImage()->setProperty(Image::PROPERTY_COLORSPACE, $matches['value']);
			}

			if (preg_match('/^\s*Compression:\s*(?<value>[^\s]+)/i', $property, $matches))
			{
				$this->getDispatcher()->getImage()->setProperty(Image::PROPERTY_COMPRESSION, $matches['value']);
			}

			if (preg_match('/^\s*Quality:\s*(?<value>\d+)/i', $property, $matches))
			{
				$this->getDispatcher()->getImage()->setProperty(Image::PROPERTY_QUALITY, (int) $matches['value']);
			}

			if (preg_match('/^\s*Profile-(?<value>[\w\d]+):/i', $property, $matches))
			{
				$profiles[] = $matches['value'];
			}
		}

		if (count($profiles) > 0)
		{
			$this->getDispatcher()->getImage()->setProperty(Image::PROPERTY_PROFILES, $profiles);
		}
	}
}
