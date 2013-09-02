<?php
/**
 * Part of the Joomla Framework Github Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Github\Package;

use Joomla\Github\Package;

/**
 * GitHub API References class for the Joomla Platform.
 *
 * @documentation http://developer.github.com/v3/git/refs
 *
 * @since  1.2
 */
class Refs extends Package
{
	/**
	 * Method to get a single reference.
	 *
	 * @param   string  $user  The name of the owner of the GitHub repository.
	 * @param   string  $repo  The name of the GitHub repository.
	 * @param   string  $ref   The reference to get.
	 *
	 * @throws  \DomainException
	 * @since   1.2
	 *
	 * @return  object
	 */
	public function get($user, $repo, $ref)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/git/refs/' . $ref;

		// Send the request.
		$response = $this->client->get($this->fetchUrl($path));

		// Validate the response code.
		if ($response->code != 200)
		{
			// Decode the error response and throw an exception.
			$error = json_decode($response->body);
			throw new \DomainException($error->message, $response->code);
		}

		return json_decode($response->body);
	}

	/**
	 * Method to list all references.
	 *
	 * @param   string   $user       The name of the owner of the GitHub repository.
	 * @param   string   $repo       The name of the GitHub repository.
	 * @param   string   $namespace  An optional reference namespace: commit | heads | tags.
	 * @param   integer  $page       The page number from which to get items.
	 * @param   integer  $limit      The number of items on a page.
	 *
	 * @throws  \DomainException
	 * @since   1.2
	 *
	 * @return  array
	 */
	public function getList($user, $repo, $namespace = null, $page = 0, $limit = 0)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/git/refs';

		$namespace = strtolower((string) $namespace);

		if (in_array($namespace, array('commit', 'heads', 'tags')))
		{
			$path .= '/' . $namespace;
		}

		// Send the request.
		$response = $this->client->get($this->fetchUrl($path, $page, $limit));

		// Validate the response code.
		if ($response->code != 200)
		{
			// Decode the error response and throw an exception.
			$error = json_decode($response->body);
			throw new \DomainException($error->message, $response->code);
		}

		return json_decode($response->body);
	}
}
