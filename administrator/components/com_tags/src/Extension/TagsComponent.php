<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Administrator\Extension;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\MVCComponent;

/**
 * Component class for com_tags
 *
 * @since  4.0.0
 */
class TagsComponent extends MVCComponent implements RouterServiceInterface
{
	use RouterServiceTrait;
}
