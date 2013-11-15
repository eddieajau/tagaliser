<?php
/**
 * Configuration service provider.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    MIT or LGPL.
 */

namespace Tagaliser\Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Registry\Registry;

/**
 * Registers the Configuration service provider.
 *
 * @since  1.2
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
	/**
	 * @var    string
	 * @since  1.3
	 */
	private $path;

	/**
	 * Class constructor.
	 *
	 * @param   string  $path  The full path and file name for the configuration file.
	 *
	 * @since   1.3
	 */
	public function __construct($path)
	{
		$this->path = $path;
	}

	/**
	 * Gets a configuration object.
	 *
	 * @param   Container  $c  A DI container.
	 *
	 * @return  Registry
	 *
	 * @since   1.3
	 * @throws  \LogicException if the configuration file does not exist.
	 * @throws  \UnexpectedValueException if the configuration file could not be parsed.
	 */
	public function getConfig(Container $c)
	{
		if (!file_exists($this->path))
		{
			throw new \LogicException('Configuration file does not exist.', 500);
		}

		$json = json_decode(file_get_contents($this->path));

		if (null === $json)
		{
			throw new \UnexpectedValueException('Configuration file could not be parsed.', 500);
		}

		$config = new Registry($json);

		// Automatically set the path for `/etc/`.
		$config->set('path.etc', dirname($this->path));

		return $config;
	}

	/**
	 * Registers the service provider within a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   1.2
	 */
	public function register(Container $container)
	{
		$that = $this;
		$container->share(
			'config',
			function ($c) use ($that)
			{
				return $that->getConfig($c);
			}
			, true
		);
	}
}
