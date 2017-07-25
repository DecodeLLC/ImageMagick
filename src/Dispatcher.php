<?php

namespace DecodeLLC\ImageMagick;

use DecodeLLC\ImageMagick\Image;
use DecodeLLC\ImageMagick\Logger;
use DecodeLLC\ImageMagick\Utility\Convert;
use DecodeLLC\ImageMagick\Utility\Identify;

/**
 * {description}
 */
class Dispatcher
{

	/**
	 * {description}
	 *
	 * @var     Image
	 * @access  protected
	 */
	protected $image;

	/**
	 * {description}
	 *
	 * @var     Logger
	 * @access  protected
	 */
	protected $logger;

	/**
	 * {description}
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $utilitiesInstances;

	/**
	 * {description}
	 *
	 * @param   Image    $image
	 * @param   Logger   $logger
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct(Image $image, Logger $logger = null)
	{
		$this->image = $image;

		$this->logger = $logger ?: new Logger();

		$this->getIdentifyInstance()->readImageProperties();
	}

	/**
	 * {description}
	 *
	 * @access  public
	 * @return  string
	 */
	public function getRoot()
	{
		return realpath(__DIR__ . '/../');
	}

	/**
	 * {description}
	 *
	 * @access  public
	 * @return  Image
	 */
	public function getImage()
	{
		return $this->image;
	}

	/**
	 * {description}
	 *
	 * @access  public
	 * @return  Logger
	 */
	public function getLogger()
	{
		return $this->logger;
	}

	/**
	 * {description}
	 *
	 * @access  public
	 * @return  Convert
	 */
	public function getConvertInstance()
	{
		if (empty($this->utilitiesInstances['convert']))
		{
			$this->utilitiesInstances['convert'] = new Convert($this);

			if (defined('DECODELLC_IMAGEMAGICK_PATH_BIN_CONVERT'))
			{
				$this->utilitiesInstances['convert']->setBin(DECODELLC_IMAGEMAGICK_PATH_BIN_CONVERT);
			}
		}

		return $this->utilitiesInstances['convert'];
	}

	/**
	 * {description}
	 *
	 * @access  public
	 * @return  Identify
	 */
	public function getIdentifyInstance()
	{
		if (empty($this->utilitiesInstances['identify']))
		{
			$this->utilitiesInstances['identify'] = new Identify($this);

			if (defined('DECODELLC_IMAGEMAGICK_PATH_BIN_IDENTIFY'))
			{
				$this->utilitiesInstances['identify']->setBin(DECODELLC_IMAGEMAGICK_PATH_BIN_IDENTIFY);
			}
		}

		return $this->utilitiesInstances['identify'];
	}
}
