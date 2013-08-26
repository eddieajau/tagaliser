<?php
/**
 * The Tagaliser application.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    MIT or LGPL.
 */

namespace Tagaliser;

use Joomla\Application\AbstractCliApplication;
use Joomla\DI\Container;

/**
 * The Tagaliser application class.
 *
 * @since  1.0
 */
class Application extends AbstractCliApplication
{
	/**
	 * The application version.
	 *
	 * @var    string
	 * @since  1.0
	 */
	const VERSION = '1.1';

	/**
	 * The application's DI container.
	 *
	 * @var    Di\Container
	 * @since  1.1
	 */
	private $container;

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function doExecute()
	{
		// Check if help is needed.
		if ($this->input->get('h') || $this->input->get('help'))
		{
			$this->help();

			return;
		}

		/* @var $github \Joomla\Github\Github */
		$github  = $this->container->get('github');

		$this->out('Repositories:');

		foreach ($github->repositories->getListOrg('joomla') as $repository)
		{
			$this->out('* ' . $repository->name);
		}
	}

	/**
	 * Custom initialisation method.
	 *
	 * Called at the end of the Base::__construct method. This is for developers to inject initialisation code for their application classes.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 * @since   1.0
	 */
	protected function initialise()
	{
		// New DI stuff!
		$container = new Container;

		$container->registerServiceProvider(new Providers\GithubServiceProvider);

		$this->container = $container;
	}

	/**
	 * Display the help text.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function help()
	{
		$this->out('Tagaliser ' . self::VERSION);
		$this->out();
		$this->out('Usage:     php -f tagaliser.php -- [switches]');
		$this->out('           tagaliser [switches]');
		$this->out();
		$this->out('Switches:  -h | --help    Prints this usage information.');
		$this->out();
		$this->out('Examples:  tagaliser -h');
		$this->out();
	}
}
