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
use Joomla\Registry\Registry;

/**
 * Registers the Github service provider.
 *
 * @since  1.1
 */
class GithubServiceProvider implements ServiceProviderInterface
{
	/**
	 * Gets a Github object.
	 *
	 * @param   Container  $c  A DI container.
	 *
	 * @return  Github
	 *
	 * @since   2.0
	 */
	public function getGithub(Container $c)
	{
		/* @var $config Registry */
		$config = $c->get('config');

		/* @var $input Joomla\Input\Input */
		$input = $c->get('input');

		$options = new Registry;
		$options->set('headers.Accept', 'application/vnd.github.html+json');
		$options->set('api.username', $input->get('username', $config->get('api.username')));
		$options->set('api.password', $input->get('password', $config->get('api.password')));
		$options->set('api.url', $config->get('api.url'));

		$github = new Github($options);

		return $github;
	}

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
		$that = $this;
		$container->share(
			'github',
			function(Container $c) use ($that)
			{
				return $that->getGithub($c);
			},
			true
		);
	}
}
