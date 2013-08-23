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
require realpath(__DIR__ . '/../vendor/autoload.php');

try
{
	$app = new Tagaliser\Application;
	$app->execute();
}
catch (Exception $e)
{
	// An exception has been caught, just echo the message.
	fwrite(STDOUT, $e->getMessage() . "\n");
	exit($e->getCode());
}
