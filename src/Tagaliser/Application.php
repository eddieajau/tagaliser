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
use Joomla\Registry\Registry;

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
	const VERSION = '1.2.1';

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

		$config = $this->container->get('config');
		$user = $this->input->get('user', $config->get('github.user'));
		$repo = $this->input->get('repo', $config->get('github.repo'));

		if (empty($user) or empty($repo))
		{
			throw new \UnexpectedValueException('A Github user and repository must be provided via the command line or application configuration.');
		}

		$state = new Registry(array(
			'user' => $user,
			'repo' => $repo,
		));

		$model = new Model($this->container->get('github'), $state);
		$model->setLogger($this->container->get('logger'));
		$log = $model->getChangelog();

		$this->renderLog($log);
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
		$input = $this->input;

		$container->share('input', function (Container $c) use ($input) {
			return $input;
		}, true);

		$container->registerServiceProvider(new Providers\ConfigServiceProvider);
		$container->registerServiceProvider(new Providers\GithubServiceProvider);
		$container->registerServiceProvider(new Providers\LoggerServiceProvider);

		$this->container = $container;
	}

	/**
	 * Display the help text.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function help()
	{
		$this->out('Tagaliser ' . self::VERSION);
		$this->out();
		$this->out('Usage:     php -f tagaliser.php -- [switches]');
		$this->out();
		$this->out('Switches:  -h | --help    Prints this usage information.');
		$this->out('           --user         The name of the Github user (associated with the repository).');
		$this->out('           --repo         The name of the Github repository.');
		$this->out('           --username     Your Github login username.');
		$this->out('           --password     Your Github login password.');
		$this->out();
		$this->out('Examples:  php -f tagaliser.php -h');
		$this->out('           php -f tagaliser.php -- --user=foo --repo=bar');
		$this->out();
	}

	/**
	 * Renders a changelog array.
	 *
	 * @param   array  $log  The changelog details.
	 *
	 * @return  void
	 *
	 * @since   1.2
	 */
	private function renderLog($log)
	{
		foreach ($log as $tag)
		{
			$this->out(sprintf('## %s - %s', $tag['tag']->tag, $tag['tag']->date));

			foreach ($tag['pulls'] as $pull)
			{
				$this->out();
				$this->out(sprintf('* [# %d](%s) : %s by [%s](%s) %s', $pull->number, $pull->url, $pull->title, $pull->user_login, $pull->user_url, $pull->merged_at));
			}

			$this->out();
		}
	}
}
