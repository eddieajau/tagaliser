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
	const VERSION = '__DEPLOY_VERSION__';

	/**
	 * The application's DI container.
	 *
	 * @var    Container
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
	public function execute()
	{
		// Check if help is needed.
		if ($this->input->get('h') || $this->input->get('help'))
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
			$this->out('           --tag          Specifies a single tag to update.');
			$this->out('           --dry-run      Runs the application without adding any data.');
			$this->out('                          Use "Not tagged" to get the list of changes for the next tag.');
			$this->out('           --profile      Use a connection profile from the configuration file.');
			$this->out();
			$this->out('Examples:  php -f tagaliser.php -h');
			$this->out('           php -f tagaliser.php -- --user=foo --repo=bar');
			$this->out('           php -f tagaliser.php -- --tag=v1.0');
			$this->out('           php -f tagaliser.php -- --tag="Not tagged"');
			$this->out('           php -f tagaliser.php -- --dry-run');
			$this->out('           php -f tagaliser.php -- --profile=work');
			$this->out();
		}
		else
		{
			parent::execute();
		}
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \UnexpectedValueException if the Github user and repository name are not provided.
	 * @throws  \LogicException if a dry-run and the canned data file is not found.
	 * @throws  \RuntimeException if a dry-run and the canned data cannot be read.
	 */
	protected function doExecute()
	{
		/** @var Registry $config */
		$config = $this->container->get('config');

		/** @var \Psr\Log\LoggerInterface $logger */
		$logger = $this->container->get('logger');

		/** @var \Joomla\Github\Github $github */
		$github = $this->container->get('github');

		$dryRun = $this->input->getBool('dry-run');
		$user = $this->input->get('user', $config->get('github.user'));
		$repo = $this->input->get('repo', $config->get('github.repo'));
		$tag = $this->input->getString('tag');

		if (empty($user) or empty($repo))
		{
			throw new \UnexpectedValueException(
				'A Github user and repository must be provided via the command line or a configuration profile.'
			);
		}

		// Workaround for bug where there is no rate limit.
		try
		{
			$logger->info(sprintf('Github user="%s", repository="%s")', $user, $repo));
			$rate = $github->authorization->getRateLimit()->rate;
			$logger->info(sprintf('Github rate limit %d (%d remaining)', $rate->limit, $rate->remaining));
		}
		catch (\Exception $e)
		{
			// Check if the exception is that rate limiting was not found.
			if ($e->getCode() != 404)
			{
				throw $e;
			}

			$logger->info(sprintf('Github rate limit *unlimited*.'));
		}

		$state = new Registry(array(
			'user' => $user,
			'repo' => $repo,
			'dry-run' => $dryRun,
		));

		$model = new Model($this->container->get('github'), $state);
		$model->setLogger($this->container->get('logger'));

		if (!$dryRun)
		{
			$log = $model->getChangelog($tag);
		}
		else
		{
			$logger->debug('DRY RUN! Using canned data and nothing will be really created or updated.');

			$dryRunFile = $config->get('path.etc') . '/dry-run.json';

			if (!file_exists($dryRunFile))
			{
				throw new \LogicException('Dry-run data file does not exist.', 500);
			}

			$log = (array) json_decode(file_get_contents($dryRunFile));

			if (null === $log)
			{
				throw new \RuntimeException('Dry-run data file could not be parsed.', 500);
			}

			if ($tag)
			{
				foreach ($log as $k => $data)
				{
					if ($k != $tag)
					{
						unset($log[$k]);
						$logger->debug(sprintf('Removed data for tag `%s` (%d).', $k, count($log)));
					}
				}
			}
		}

		$this->decorateLog($log);
		$model->updateReleases($log);

		if ($dryRun)
		{
			$logger->debug('Dumping release notes that would have been sent to Github.');

			foreach ($log as $k => $data)
			{
				$logger->debug(sprintf('Release notes for tag `%s`', $k));
				$this->out($data->notes);
			}
		}
		elseif ($log[Model::NOT_TAGGED])
		{
			$logger->info('Dumping release notes for pull requests without a tag.');
			$this->out($log[Model::NOT_TAGGED]->notes);
		}
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
	private function decorateLog(&$log)
	{
		/** @var \Mustache_Engine $mustache */
		$mustache = $this->container->get('mustache');
		$view = $mustache->loadTemplate('release_notes');

		foreach ($log as &$data)
		{
			// Note that Mustache does not like looping over associative arrays.
			$data->notes = $view->render($data);
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
		$input = $this->input;

		$container->share('input', function (Container $c) use ($input) {
			return $input;
		}, true);

		$container->registerServiceProvider(new Providers\ConfigServiceProvider(TAGALISER_CONFIG));
		$container->registerServiceProvider(new Providers\GithubServiceProvider);
		$container->registerServiceProvider(new Providers\LoggerServiceProvider);
		$container->registerServiceProvider(new Providers\MustacheServiceProvider);

		$this->container = $container;
	}
}
