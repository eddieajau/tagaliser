#!/usr/bin/env php
<?php
/**
 * The Tagaliser phar stub.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    MIT or LGPL.
 */

define('TAGALISER_CONFIG', __DIR__ . '/tagaliser.json');

Phar::mapPhar('tagaliser.phar');

require 'phar://tagaliser.phar/bin/tagaliser.php';

__HALT_COMPILER();
