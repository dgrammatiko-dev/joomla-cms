<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * Text Filters form field.
 *
 * @since  1.6
 */
class FiltersField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'Filters';

	/**
	 * Method to get the field input markup.
	 *
	 * TODO: Add access check.
	 *
	 * @return	string	The field input markup.
	 *
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Load Framework
		HTMLHelper::_('jquery.framework');

		// Add translation string for notification
		Text::script('COM_CONFIG_TEXT_FILTERS_NOTE');

		// Add Javascript
		$doc = Factory::getDocument();
		$doc->addScriptDeclaration('
			jQuery( document ).ready(function( $ ) {
				$("#filter-config select").change(function() {
					var currentFilter = $(this).children("option:selected").val();

					if($(this).children("option:selected").val() === "NONE") {
						var child = $("#filter-config select[data-parent=" + $(this).attr("data-id") + "]");

						while(child.length !== 0) {
							if(child.children("option:selected").val() !== "NONE") {
								alert(Joomla.JText._("COM_CONFIG_TEXT_FILTERS_NOTE"));
								break;
							}

							child = $("#filter-config select[data-parent=" + child.attr("data-id") + "]");
						}

						return;
					}

					var parent = $("#filter-config select[data-id=" + $(this).attr("data-parent") + "]");

					while(parent.length !== 0) {
						if(parent.children("option:selected").val() === "NONE") {
							alert(Joomla.JText._("COM_CONFIG_TEXT_FILTERS_NOTE"));
							break;
						}

						parent = $("#filter-config select[data-id=" + parent.attr("data-parent") + "]")
					}
				});
			});'
		);

		// Get the available user groups.
		$groups = $this->getUserGroups();

		// Build the form control.
		$html = array();

		// Open the table.
		$html[] = '<table id="filter-config" class="table">';

		// The table heading.
		$html[] = '	<thead>';
		$html[] = '	<tr>';
		$html[] = '		<th>';
		$html[] = '			<span class="acl-action">' . Text::_('JGLOBAL_FILTER_GROUPS_LABEL') . '</span>';
		$html[] = '		</th>';
		$html[] = '		<th>';
		$html[] = '			<span class="acl-action">' . Text::_('JGLOBAL_FILTER_TYPE_LABEL') . '</span>';
		$html[] = '		</th>';
		$html[] = '		<th>';
		$html[] = '			<span class="acl-action">' . Text::_('JGLOBAL_FILTER_TAGS_LABEL') . '</span>';
		$html[] = '		</th>';
		$html[] = '		<th>';
		$html[] = '			<span class="acl-action">' . Text::_('JGLOBAL_FILTER_ATTRIBUTES_LABEL') . '</span>';
		$html[] = '		</th>';
		$html[] = '	</tr>';
		$html[] = '	</thead>';

		// The table body.
		$html[] = '	<tbody>';

		foreach ($groups as $group)
		{
			if (!isset($this->value[$group->value]))
			{
				$this->value[$group->value] = array('filter_type' => 'BL', 'filter_tags' => '', 'filter_attributes' => '');
			}

			$group_filter = $this->value[$group->value];

			$group_filter['filter_tags']       = !empty($group_filter['filter_tags']) ? $group_filter['filter_tags'] : '';
			$group_filter['filter_attributes'] = !empty($group_filter['filter_attributes']) ? $group_filter['filter_attributes'] : '';

			$html[] = '	<tr>';
			$html[] = '		<td class="acl-groups left">';
			$html[] = '			' . LayoutHelper::render('joomla.html.treeprefix', array('level' => $group->level + 1)) . $group->text;
			$html[] = '		</td>';
			$html[] = '		<td>';
			$html[] = '			<label for="' . $this->id . $group->value . '_filter_type" class="sr-only">'
				. Text::_('JGLOBAL_FILTER_TYPE_LABEL') . '</label>';
			$html[] = '				<select'
				. ' name="' . $this->name . '[' . $group->value . '][filter_type]"'
				. ' id="' . $this->id . $group->value . '_filter_type"'
				. ' data-parent="' . ($group->parent) . '" '
				. ' data-id="' . ($group->value) . '" '
				. ' class="novalidate custom-select"'
				. '>';
				// "BL" is deprecated in Joomla! 4, will be removed in Joomla! 5
			$html[] = '					<option value="DL"' . (in_array($group_filter['filter_type'], ['BL', 'DL']) ? ' selected="selected"' : '') . '>'
				. Text::_('COM_CONFIG_FIELD_FILTERS_DEFAULT_ALLOW_LIST') . '</option>';
				// "CBL" is deprecated in Joomla! 4, will be removed in Joomla! 5
			$html[] = '					<option value="CDL"' . (in_array($group_filter['filter_type'], ['CBL', 'CDL']) ? ' selected="selected"' : '') . '>'
				. Text::_('COM_CONFIG_FIELD_FILTERS_CUSTOM_DISALLOW_LIST') . '</option>';
				// "WL" is deprecated in Joomla! 4, will be removed in Joomla! 5
			$html[] = '					<option value="AL"' . (in_array($group_filter['filter_type'], ['WL', 'AL']) ? ' selected="selected"' : '') . '>'
				. Text::_('COM_CONFIG_FIELD_FILTERS_ALLOW_LIST') . '</option>';
			$html[] = '					<option value="NH"' . ($group_filter['filter_type'] == 'NH' ? ' selected="selected"' : '') . '>'
				. Text::_('COM_CONFIG_FIELD_FILTERS_NO_HTML') . '</option>';
			$html[] = '					<option value="NONE"' . ($group_filter['filter_type'] == 'NONE' ? ' selected="selected"' : '') . '>'
				. Text::_('COM_CONFIG_FIELD_FILTERS_NO_FILTER') . '</option>';
			$html[] = '				</select>';
			$html[] = '		</td>';
			$html[] = '		<td>';
			$html[] = '			<label for="' . $this->id . $group->value . '_filter_tags" class="sr-only">'
				. Text::_('JGLOBAL_FILTER_TAGS_LABEL') . '</label>';
			$html[] = '				<input'
				. ' name="' . $this->name . '[' . $group->value . '][filter_tags]"'
				. ' type="text"'
				. ' id="' . $this->id . $group->value . '_filter_tags" class="novalidate form-control"'
				. ' value="' . htmlspecialchars($group_filter['filter_tags'], ENT_QUOTES) . '"'
				. '>';
			$html[] = '		</td>';
			$html[] = '		<td>';
			$html[] = '			<label for="' . $this->id . $group->value . '_filter_attributes"'
				. ' class="sr-only">' . Text::_('JGLOBAL_FILTER_ATTRIBUTES_LABEL') . '</label>';
			$html[] = '				<input'
				. ' name="' . $this->name . '[' . $group->value . '][filter_attributes]"'
				. ' type="text"'
				. ' id="' . $this->id . $group->value . '_filter_attributes" class="novalidate form-control"'
				. ' value="' . htmlspecialchars($group_filter['filter_attributes'], ENT_QUOTES) . '"'
				. '>';
			$html[] = '		</td>';
			$html[] = '	</tr>';
		}

		$html[] = '	</tbody>';

		// Close the table.
		$html[] = '</table>';

		return implode("\n", $html);
	}

	/**
	 * A helper to get the list of user groups.
	 *
	 * @return	array
	 *
	 * @since	1.6
	 */
	protected function getUserGroups()
	{
		// Get a database object.
		$db = Factory::getDbo();

		// Get the user groups from the database.
		$query = $db->getQuery(true);
		$query->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level, a.parent_id as parent');
		$query->from('#__usergroups AS a');
		$query->join('LEFT', '#__usergroups AS b on a.lft > b.lft AND a.rgt < b.rgt');
		$query->group('a.id, a.title, a.lft');
		$query->order('a.lft ASC');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return $options;
	}
}
