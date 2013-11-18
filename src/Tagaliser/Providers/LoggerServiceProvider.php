<?php
/**
 * Logger service provider.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    MIT or LGPL.
 */

namespace Tagaliser\Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Registers the Logger service provider.
 *
 * @since  1.2
 */
class LoggerServiceProvider implements ServiceProviderInterface
{
	/**
	 * Gets a Logger object.
	 *
	 * @param   Container  $c  A DI container.
	 *
	 * @return  Logger
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getLogger(Container $c)
	{
		$logger = new Logger('Tagaliser');

		$logger->pushHandler(new StreamHandler('php://stdout'));

		return $logger;
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
			'logger',
			function(Container $c) use ($that)
			{
				return $that->getLogger($c);
			},
			true
		);
	}
}
