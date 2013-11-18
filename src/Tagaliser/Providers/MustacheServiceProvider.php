<?php
/**
 * Mustache service provider.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    http://opensource.org/licenses/MIT  MIT
 */

namespace Tagaliser\Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Registers the Mustache service provider.
 *
 * @since  __DEPLOY_VERSION__
 */
class MustacheServiceProvider implements ServiceProviderInterface
{
	/**
	 * Get a Mustache object.
	 *
	 * @param   Container  $c  A DI container.
	 *
	 * @return  \Mustache_Engine
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getMustache(Container $c)
	{
		$config = $c->get('config');

		$mustache = new \Mustache_Engine(array(
			'loader' => new \Mustache_Loader_FilesystemLoader(
				$config->get('mustache.views', __DIR__ . '/../templates'),
				array(
					'extension' => $config->get('mustache.ext', '.md'),
				)
			),
		));

		$mustache->addHelper(
			'number',
			array(
				'1f' => function ($value)
				{
					return sprintf('%.1f', $value);
				},
			)
		);

		return $mustache;
	}

	/**
	 * Registers the service provider within a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function register(Container $container)
	{
		$that = $this;
		$container->set(
			'mustache',
			function ($c) use ($that)
			{
				return $that->getMustache($c);
			}
		);
	}
}
