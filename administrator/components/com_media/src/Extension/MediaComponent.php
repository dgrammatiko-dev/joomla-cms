<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Extension\MVCComponent;
use Psr\Container\ContainerInterface;

/**
 * Component class for com_media
 *
 * @since  __DEPLOY_VERSION__
 */
class MediaComponent extends MVCComponent implements CategoryServiceInterface
{
	use CategoryServiceTrait;

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   \stdClass[]  $items    The category objects
	 * @param   string       $section  The section
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function countItems(array $items, string $section)
	{
		return $items;
	}
}
