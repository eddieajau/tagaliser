<?php
/**
 * Analyses a Github repository based on tags.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    MIT
 */

// Max out error reporting.
error_reporting(-1);
ini_set('display_errors', 1);

// Bootstrap the Joomla Framework.
require dirname(__DIR__) . '/vendor/autoload.php';

try
{
	if (!defined('TAGALISER_CONFIG'))
	{
		define('TAGALISER_CONFIG', dirname(__DIR__) . '/etc/config.json');
	}

	$app = new Tagaliser\Application;
	$app->execute();
}
catch (Exception $e)
{
	// An exception has been caught, just echo the message.
	fwrite(STDOUT, $e->getMessage() . "\n");
	exit($e->getCode());
}
