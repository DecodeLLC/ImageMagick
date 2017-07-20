<?php

namespace DecodeLLC\ImageMagick;

use DecodeLLC\ImageMagick\Dispatcher;
use DecodeLLC\ImageMagick\Exception;
use DecodeLLC\ImageMagick\Image;

/**
 * {description}
 */
abstract class Utility
{

	/**
	 * {description}
	 *
	 * @var     string
	 * @access  protected
	 */
	protected $bin;

	/**
	 * {description}
	 *
	 * @var     Dispatcher
	 * @access  protected
	 */
	protected $dispatcher;

	/**
	 * {description}
	 *
	 * @param   Dispatcher   $dispatcher
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct(Dispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	/**
	 * {description}
	 *
	 * @param   string   $bin
	 *
	 * @access  public
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public function setBin($bin)
	{
		if (! is_string($bin))
		{
			$message = '[%s] The path to the executable file must be a string.';

			throw new Exception(sprintf($message, __METHOD__));
		}

		if (! is_executable($bin))
		{
			$message = '[%s] %s is not executable or does not exists.';

			throw new Exception(sprintf($message, __METHOD__, $bin));
		}

		$this->bin = $bin;
	}

	/**
	 * {description}
	 *
	 * @access  public
	 * @return  string
	 */
	public function getBin()
	{
		return $this->bin;
	}

	/**
	 * {description}
	 *
	 * @access  public
	 * @return  Dispatcher
	 */
	public function getDispatcher()
	{
		return $this->dispatcher;
	}

	/**
	 * {description}
	 *
	 * @param   string   $command
	 * @param   array    $context
	 *
	 * @access  public
	 * @return  bool
	 */
	public function execute($command, array $context = [], & $output = null, & $status = null)
	{
		$context['bin'] = $this->getBin();

		$context['image'] = $this->getDispatcher()->getImage()->getTemporaryPath();

		if ($this->getDispatcher()->getImage()->hasProperty(Image::PROPERTY_WIDTH))
		{
			$context['width'] = $this->getDispatcher()->getImage()->getProperty(Image::PROPERTY_WIDTH);
		}
		if ($this->getDispatcher()->getImage()->hasProperty(Image::PROPERTY_HEIGHT))
		{
			$context['height'] = $this->getDispatcher()->getImage()->getProperty(Image::PROPERTY_HEIGHT);
		}
		if ($this->getDispatcher()->getImage()->hasProperty(Image::PROPERTY_FORMAT))
		{
			$context['format'] = $this->getDispatcher()->getImage()->getProperty(Image::PROPERTY_FORMAT);
		}

		foreach ($context as $key => $value)
		{
			$command = str_replace("{{$key}}", $value, $command);
		}

		$imageHashBeforeCommandRunning = hash_file('sha1',
			$this->getDispatcher()->getImage()->getTemporaryPath()
		);

		exec($command, $output, $status);

		$imageHashAfterCommandRunning = hash_file('sha1',
			$this->getDispatcher()->getImage()->getTemporaryPath()
		);

		if (! (strcmp($imageHashBeforeCommandRunning, $imageHashAfterCommandRunning) === 0))
		{
			$this->getDispatcher()->getIdentifyInstance()->readImageProperties();

			return true;
		}

		return false;
	}
}
