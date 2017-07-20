<?php

namespace DecodeLLC\ImageMagick;

use Countable;
use RuntimeException;

/**
 * {description}
 */
class Logger implements Countable
{

	/**
	 * {description}
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $messages = [];

	/**
	 * {description}
	 *
	 * @param   string   $message
	 * @param   array    $context
	 *
	 * @access  public
	 * @return  void
	 */
	public function add($message, array $context = [])
	{
		foreach ($context as $key => $value)
		{
			$message = str_replace("{{$key}}", $value, $message);
		}

		$this->messages[] = $message;
	}

	/**
	 * {description}
	 *
	 * @access  public
	 * @return  array
	 */
	public function all()
	{
		return $this->messages;
	}

	/**
	 * {description}
	 *
	 * @access  public
	 * @return  int
	 */
	public function count()
	{
		return count($this->messages);
	}

	/**
	 * {description}
	 *
	 * @access  public
	 * @return  void
	 *
	 * @throws  RuntimeException
	 */
	public function dump()
	{
		throw new RuntimeException(sprintf('[%s] Not implemented.', __METHOD__));
	}
}
