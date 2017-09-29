<?php
/**
 * Items Model for a Prove Component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_prove
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since  __DEPLOY_VERSION__
 */
namespace Joomla\Component\Workflow\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

/**
 * Model class for items
 *
 * @since  __DEPLOY_VERSION__
 */
class  Workflows extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'title',
				'state',
				'created_by',
				'created',
				'modified'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = \JFactory::getApplication();
		$extension = $app->getUserStateFromRequest($this->context . '.filter.extension', 'extension', 'com_content', 'cmd');

		$this->setState('filter.extension', $extension);
		$parts = explode('.', $extension);

		// Extract the component name
		$this->setState('filter.component', $parts[0]);

		// Extract the optional section name
		$this->setState('filter.section', (count($parts) > 1) ? $parts[1] : null);

		parent::populateState($ordering, $direction);

		// TODO: Change the autogenerated stub
	}


	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  \Joomla\CMS\Table\Table  A JTable object
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getTable($type = 'Workflow', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if ($items)
		{
			$this->countItems($items);
		}

		return $items;
	}

	/**
	 * Add the number of transitions and states to all workflow items
	 *
	 * @param   array  $items  The workflow items
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function countItems($items)
	{
		$db = $this->getDbo();

		$ids = [0];

		foreach ($items as $item)
		{
			$ids[] = (int) $item->id;

			$item->count_states = 0;
			$item->count_transitions = 0;
		}

		$query = $db->getQuery(true);

		$query	->select('workflow_id, count(*) AS count')
				->from($db->qn('#__workflow_states'))
				->where($db->qn('workflow_id') . ' IN(' . implode(',', $ids) . ')')
				->where($db->qn('published') . '>= 0')
				->group('workflow_id');

		$status = $db->setQuery($query)->loadObjectList('workflow_id');

		$query = $db->getQuery(true);

		$query	->select('workflow_id, count(*) AS count')
				->from($db->qn('#__workflow_transitions'))
				->where($db->qn('workflow_id') . ' IN(' . implode(',', $ids) . ')')
				->where($db->qn('published') . '>= 0')
				->group('workflow_id');

		$transitions = $db->setQuery($query)->loadObjectList('workflow_id');

		foreach ($items as $item)
		{
			if (isset($status[$item->id]))
			{
				$item->count_states = (int) $status[$item->id]->count;
			}

			if (isset($transitions[$item->id]))
			{
				$item->count_transitions = (int) $transitions[$item->id]->count;
			}
		}
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  string  The query to database.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getListQuery()
	{
		$db = $this->getDbo();

		$query = parent::getListQuery();

		$select = $db->quoteName(
			array(
			'w.id',
			'w.title',
			'w.created',
			'w.modified',
			'w.published',
			'w.default',
			'u.name'
		)
		);

		$query
			->select($select)
			->from($db->quoteName('#__workflows', 'w'))
			->leftJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->qn('u.id') . ' = ' . $db->qn('w.created_by'));

		// Filter by extension
		if ($extension = $this->getState('filter.extension'))
		{
			$query->where($db->qn('extension') . ' = ' . $db->quote($db->escape($extension)));
		}

		// Filter by author
		$authorId = $this->getState('filter.created_by');

		if (is_numeric($authorId))
		{
			$type = $this->getState('filter.created_by.include', true) ? '= ' : '<>';
			$query->where($db->qn('w.created_by') . $type . (int) $authorId);
		}

		$status = (string) $this->getState('filter.published');

		// Filter by condition
		if (is_numeric($status))
		{
			$query->where($db->qn('w.published') . ' = ' . (int) $status);
		}
		elseif ($status == '')
		{
			$query->where($db->qn('w.published') . " IN ('0', '1')");
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$query->where('(' . $db->qn('w.title') . ' LIKE ' . $search . ' OR ' . $db->qn('w.description') . ' LIKE ' . $search . ')');
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'id');
		$orderDirn 	= strtolower($this->state->get('list.direction', 'asc'));

		$query->order($db->qn($db->escape($orderCol)) . ' ' . $db->escape($orderDirn == 'desc' ? 'DESC' : 'ASC'));

		return $query;
	}

	/**
	 * Build a list of authors
	 *
	 * @return  stdClass[]
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getAuthors()
	{
		$query = $this->getDbo()->getQuery(true);

		$query->select('u.id AS value, u.name AS text')
			->from('#__users AS u')
			->join('INNER', '#__workflows AS c ON c.created_by = u.id')
			->group('u.id, u.name')
			->order('u.name');

		return $this->getDbo()->setQuery($query)->loadObjectList();
	}
}
