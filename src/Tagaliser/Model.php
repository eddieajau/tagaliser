<?php
/**
 * The Tagaliser model.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    MIT or LGPL.
 */

namespace Tagaliser;

use Joomla\Github\Github;
use Joomla\Registry\Registry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Joomla\Model\AbstractModel;

/**
 * The Tagaliser model class.
 *
 * Required state settings:
 *
 * * user - The owner/user of the Github repository to scan.
 * * repo - The name of the Github repository.
 *
 * @since  1.0
 */
class Model extends AbstractModel implements LoggerAwareInterface
{
	/**
	 * The key and label for pull requests that are not tagged.
	 *
	 * @since  1.3
	 */
	const NOT_TAGGED = 'Not tagged';

	/**
	 * A Github connector.
	 *
	 * @var    Github
	 * @since  1.2
	 */
	private $github;

	/**
	 * A logger.
	 *
	 * @var    LoggerInterface
	 * @since  1.2
	 */
	private $logger;

	/**
	 * An array of the releases in the repository
	 *
	 * @var    array
	 * @since  1.3
	 */
	private $releases;
	/**
	 * An array of the tags in the repository
	 *
	 * @var    array
	 * @since  1.2
	 */
	private $tags;

	/**
	 * Class constructor.
	 *
	 * Overrides the parent class to directly inject the Github connector.
	 *
	 * @param   Github    $github  A Github connector object.
	 * @param   Registry  $state   The model state.
	 *
	 * @since   1.2
	 */
	public function __construct(Github $github, Registry $state)
	{
		parent::__construct($state);

		$this->github = $github;
	}

	/**
	 * Get the changelog.
	 *
	 * The format of the return value is as follows:
	 *
	 * array(
	 *   'tag name' => array(
	 *     'tag' => (object) array(
	 *       'tag' => 'Tag name',
	 *       'date' => 'Date tag created'
	 *     ),
	 *     'pulls' => array(
	 *       'Tag name' => (object) array(
	 *         'url' => 'Link to pull request.',
	 *         'number' => 'Pull request number.',
	 *         'title' => 'Pull request title.',
	 *         'merged_at' => 'Date pull request was merged.',
	 *         'user_login' => 'The user that created the pull request.',
	 *         'user_url' => 'A link to the user profile page that merged the pull request.',
	 *       )
	 *     )
	 *   )
	 * )
	 *
	 * @return  array  An associative array keyed on the tag name.
	 *                 Each element is an array with the keys 'tag' and 'pulls'.
	 *
	 * @since   1.2
	 */
	public function getChangelog()
	{
		$rate = $this->github->authorization->getRateLimit()->rate;
		$this->logger->info(sprintf('Github rate limit %d (%d remaining)', $rate->limit, $rate->remaining));

		$releases = $this->getReleases();

		// Set the maximum number of pages (and runaway failsafe).
		$cutoff = 100;
		$page = 1;

		$log = array();

		while ($cutoff--)
		{
			// Get a page of the closed issues.
			$pulls = $this->getPulls($page++);

			// Check if we've gone past the last page.
			if (empty($pulls))
			{
				break;
			}

			foreach ($pulls as $pull)
			{
				$tag = $this->getTag($pull->merged_at);

				if (null === $tag)
				{
					$tag = (object) array(
						'tag' => self::NOT_TAGGED,
						'tagger' => (object) array(
							'date' => 'to date.'
						)
					);
				}

				if (!isset($log[$tag->tag]))
				{
					$log[$tag->tag] = new \stdClass;
					$log[$tag->tag]->tag = (object) array(
						'tag' => $tag->tag,
						'date' => $tag->tagger->date,
						'release_id' => isset($releases[$tag->tag]) ? $releases[$tag->tag]->id : 0,
						'target_commitish' => isset($releases[$tag->tag]) ? $releases[$tag->tag]->target_commitish : null,
					);

					$log[$tag->tag]->pulls = array();
					$log[$tag->tag]->users = array();
				}

				$log[$tag->tag]->pulls[] = (object) array(
					'url' => $pull->html_url,
					'number' => $pull->number,
					'title' => $pull->title,
					'merged_at' => $pull->merged_at,
					'user_login' => $pull->user->login,
					'user_url' => $pull->user->url,
				);

				if (!isset($log[$tag->tag]->users[$pull->user->login]))
				{
					$log[$tag->tag]->users[$pull->user->login] = (object) array(
						'user_login' => $pull->user->login,
						'count' => 0,
					);
				}

				$log[$tag->tag]->users[$pull->user->login]->count += 1;
			}
		}

		// Sort the users nodes.
		foreach ($log as &$data)
		{
			asort($data->users);
			$data->users = array_values($data->users);
		}

		return $log;
	}

	/**
	 * Sets a logger instance in the object.
	 *
	 * @param   LoggerInterface  $logger  A logger.
	 *
	 * @return  void
	 *
	 * @since   1.2
	 */
    public function setLogger(LoggerInterface $logger)
    {
		$this->logger = $logger;
    }

	/**
	 * Updates the release notes for each tag on the Github site.
	 *
	 * @param   array  $log  The changelog data object.
	 *
	 * @return  void
	 *
	 * @since   1.3
	 */
	public function updateReleases($log)
	{
		$dryRun = $this->state->get('dry-run');
		$user = $this->state->get('user');
		$repo = $this->state->get('repo');

		foreach ($log as $tag => $data)
		{
			if ($tag == self::NOT_TAGGED)
			{
				$this->logger->debug('Ignoring untagged pull requests.');
				continue;
			}

			if ($data->tag->release_id)
			{
				$this->logger->debug(sprintf('Updating release for `%s`', $tag));

				if (!$dryRun)
				{
					/** @var \Joomla\Github\Package\Releases $releases */
					$releases = $this->github->releases;
					$releases->edit($user, $repo, $data->tag->release_id, $tag, '', $tag, $data->notes, false, false);
				}
			}
			else
			{
				$this->logger->debug(sprintf('Creating new release for `%s`', $tag));

				if (!$dryRun)
				{
					/** @var \Joomla\Github\Package\Releases $releases */
					$releases = $this->github->releases;
					$releases->create($user, $repo, $tag, '', $tag, $data->notes, false, false);
				}
			}
		}
	}

	/**
	 * Get a page of pull requests from the repository.
	 *
	 * @param   integer  $page  The page number.
	 *
	 * @return  array
	 *
	 * @since   1.2
	 */
	private function getPulls($page)
	{
		$this->logger->info(sprintf('Getting pulls page #%02d.', $page));
		$this->logger->info(str_pad('', 40, '-'));

		$user = $this->state->get('user');
		$repo = $this->state->get('repo');

		$pulls = $this->github->pulls
			->getList($user, $repo, 'closed', $page, 100);

		foreach ($pulls as $k => $pull)
		{
			if (!$this->github->pulls->isMerged($user, $repo, $pull->number))
			{
				unset($pulls[$k]);
			}
		}

		$this->logger->info(sprintf('Got %s merged pulls.', count($pulls)));

		return $pulls;
	}

	/**
	 * Get a list of the existing releases.
	 *
	 * @param   integer  $page  The page number.
	 *
	 * @return  array
	 *
	 * @since   1.3
	 */
	private function getReleases($page = 1)
	{
		if (null === $this->releases)
		{
			$this->logger->info('Getting releases');
			$this->logger->info(str_pad('', 40, '-'));

			$user = $this->state->get('user');
			$repo = $this->state->get('repo');

			$this->releases = $this->github->releases
				->getList($user, $repo, $page, 100);
		}

		return $this->releases;
	}

	/**
	 * Get the tag that would apply for a given date and time.
	 *
	 * @param   string  $date  The date in standard time format.
	 *
	 * @return  string|null  The tag that would apply for the date, or null if no tag was found.
	 */
	private function getTag($date)
	{
		if (null === $this->tags)
		{
			$this->tags = $this->getTags(1);
			ksort($this->tags);
		}

		foreach ($this->tags as $k => $tag)
		{
			if ($date < $k)
			{
				return $tag;
			}
		}

		return null;
	}

	/**
	 * Get a list of the tags from a repository.
	 *
	 * @param   integer  $page  The page number.
	 *
	 * @return  array  An associative array of tag names, keyed to the date the tag was created.
	 *
	 * @since   1.2
	 */
	private function getTags($page)
	{
		$this->logger->info(sprintf('Getting tags page #%02d.', $page));
		$this->logger->info(str_pad('', 40, '-'));

		$user = $this->state->get('user');
		$repo = $this->state->get('repo');

		$tags = array();

		foreach ($this->github->refs->getList($user, $repo, 'tags', $page, 100) as $ref)
		{
			$tag = $this->github->tags->get($user, $repo, $ref->object->sha);
			$tags[$tag->tagger->date] = $tag;
		}

		$this->logger->info(sprintf('Got %s tags.', count($tags)));

		return $tags;
	}
}
