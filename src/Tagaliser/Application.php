<?php
/**
 * The Tagaliser application.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    MIT or LGPL.
 */

namespace Tagaliser;

use Joomla\Application\AbstractCliApplication;

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
	const VERSION = '1.0';

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

		$this->out('It works!');
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
