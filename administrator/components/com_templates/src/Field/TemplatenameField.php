<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\Component\Templates\Administrator\Helper\TemplatesHelper;

/**
 * Template Name field.
 *
 * @since  3.5
 */
class TemplatenameField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var	   string
	 * @since  3.5
	 */
	protected $type = 'TemplateName';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	public function getOptions()
	{
		// Get the client_id filter from the user state.
		$clientId = Factory::getApplication()->getUserStateFromRequest('com_templates.styles.client_id', 'client_id', '0', 'string');

		// Get the templates for the selected client_id.
		$options = TemplatesHelper::getTemplateOptions($clientId);

		// Merge into the parent options.
		return array_merge(parent::getOptions(), $options);
	}
}
