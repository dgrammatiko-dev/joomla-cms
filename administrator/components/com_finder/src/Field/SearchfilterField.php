<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Field;

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Search Filter field for the Finder package.
 *
 * @since  2.5
 */
class SearchfilterField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $type = 'SearchFilter';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   2.5
	 */
	public function getOptions()
	{
		// Build the query.
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('f.title AS text, f.filter_id AS value')
			->from($db->quoteName('#__finder_filters') . ' AS f')
			->where('f.state = 1')
			->order('f.title ASC');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		array_unshift($options, HTMLHelper::_('select.option', '', Text::_('COM_FINDER_SELECT_SEARCH_FILTER'), 'value', 'text'));

		return $options;
	}
}