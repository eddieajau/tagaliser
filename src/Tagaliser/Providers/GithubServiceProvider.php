<?php
/**
 * Github service provider.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    MIT or LGPL.
 */

namespace Tagaliser\Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Github\Github;

/**
 * Registers the Github service provider.
 *
 * @since  1.1
 */
class GithubServiceProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider within a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   1.1
	 */
	public function register(Container $container)
	{
		$container->share('github', function(Container $c) {

			$github = new Github;

			return $github;
		}, true);
	}
}
