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
 * GitHub API Tags class for the Joomla Platform.
 *
 * @documentation http://developer.github.com/v3/git/tags
 *
 * @since  1.2
 */
class Tags extends Package
{
	/**
	 * Method to get a single reference.
	 *
	 * @param   string  $user  The name of the owner of the GitHub repository.
	 * @param   string  $repo  The name of the GitHub repository.
	 * @param   string  $tag   The reference to get.
	 *
	 * @throws  \DomainException
	 * @since   1.2
	 *
	 * @return  object
	 */
	public function get($user, $repo, $tag)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/git/tags/' . $tag;

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
}
