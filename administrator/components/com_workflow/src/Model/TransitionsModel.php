<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0.0
 */
namespace Joomla\Component\Workflow\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Model class for transitions
 *
 * @since  4.0.0
 */
class TransitionsModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since  4.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 't.id',
				'published', 't.published',
				'ordering', 't.ordering',
				'title', 't.title',
				'from_stage', 't.from_stage_id',
				'to_stage', 't.to_stage_id'
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
	 * @since  4.0.0
	 */
	protected function populateState($ordering = 't.ordering', $direction = 'ASC')
	{
		$app = Factory::getApplication();
		$workflowID = $app->getUserStateFromRequest($this->context . '.filter.workflow_id', 'workflow_id', 1, 'int');
		$extension = $app->getUserStateFromRequest($this->context . '.filter.extension', 'extension', null, 'cmd');

		if ($workflowID)
		{
			$table = $this->getTable('Workflow', 'Administrator');

			if ($table->load($workflowID))
			{
				$this->setState('active_workflow', $table->title);
			}
		}

		$this->setState('filter.workflow_id', $workflowID);
		$this->setState('filter.extension', $extension);

		parent::populateState($ordering, $direction);
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
	 * @since  4.0.0
	 */
	public function getTable($type = 'Transition', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   object  $table  A record object.
	 *
	 * @return  array  An array of conditions to add to ordering queries.
	 *
	 * @since   4.0.0
	 */
	protected function getReorderConditions($table)
	{
		return [
			$this->_db->quoteName('workflow_id') . ' = ' . (int) $table->workflow_id,
		];
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  string  The query to database.
	 *
	 * @since  4.0.0
	 */
	public function getListQuery()
	{
		$db = $this->getDbo();

		$query = parent::getListQuery();

		$select = $db->quoteName(
			array(
			't.id',
			't.title',
			't.from_stage_id',
			't.to_stage_id',
			't.published',
			't.checked_out',
			't.checked_out_time',
			't.ordering',
			't.description',
			)
		);

		$select[] = $db->quoteName('f_stage.title', 'from_stage');
		$select[] = $db->quoteName('t_stage.title', 'to_stage');
		$joinTo = $db->quoteName('#__workflow_stages', 't_stage') .
			' ON ' . $db->quoteName('t_stage.id') . ' = ' . $db->quoteName('t.to_stage_id');

		$query
			->select($select)
			->from($db->quoteName('#__workflow_transitions', 't'))
			->leftJoin(
				$db->quoteName('#__workflow_stages', 'f_stage') . ' ON ' . $db->quoteName('f_stage.id') . ' = ' . $db->quoteName('t.from_stage_id')
			)
			->leftJoin($joinTo);

		// Join over the users for the checked out user.
		$query->select($db->quoteName('uc.name', 'editor'))
			->join('LEFT', $db->quoteName('#__users', 'uc'), $db->quoteName('uc.id') . ' = ' . $db->quoteName('t.checked_out'));

		// Filter by extension
		if ($workflowID = (int) $this->getState('filter.workflow_id'))
		{
			$query->where($db->quoteName('t.workflow_id') . ' = ' . $workflowID);
		}

		$status = $this->getState('filter.published');

		// Filter by status
		if (is_numeric($status))
		{
			$query->where($db->quoteName('t.published') . ' = ' . (int) $status);
		}
		elseif ($status == '')
		{
			$query->where($db->quoteName('t.published') . ' IN (0, 1)');
		}

		// Filter by column from_stage_id
		if ($fromStage = $this->getState('filter.from_stage'))
		{
			$query->where($db->quoteName('from_stage_id') . ' = ' . (int) $fromStage);
		}

		// Filter by column from_stage_id
		if ($toStage = $this->getState('filter.to_stage'))
		{
			$query->where($db->quoteName('to_stage_id') . ' = ' . (int) $toStage);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$query->where('(' . $db->quoteName('title') . ' LIKE ' . $search . ' OR ' . $db->quoteName('description') . ' LIKE ' . $search . ')');
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 't.id');
		$orderDirn 	= strtolower($this->state->get('list.direction', 'asc'));

		$query->order($db->quoteName($orderCol) . ' ' . $db->escape($orderDirn == 'desc' ? 'DESC' : 'ASC'));

		return $query;
	}

	/**
	 * Get the filter form
	 *
	 * @param   array    $data      data
	 * @param   boolean  $loadData  load current data
	 *
	 * @return  \JForm|boolean  The \JForm object or false on error
	 *
	 * @since  4.0.0
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		$form = parent::getFilterForm($data, $loadData);

		$id = (int) $this->getState('filter.workflow_id');

		if ($form)
		{
			$where = $this->getDbo()->quoteName('workflow_id') . ' = ' . $id . ' AND ' . $this->getDbo()->quoteName('published') . ' = 1';

			$form->setFieldAttribute('from_stage', 'sql_where', $where, 'filter');
			$form->setFieldAttribute('to_stage', 'sql_where', $where, 'filter');
		}

		return $form;
	}

	/**
	 * Returns a workflow object
	 *
	 * @return  object  The workflow
	 *
	 * @since  4.0.0
	 */
	public function getWorkflow()
	{
		$table = $this->getTable('Workflow', 'Administrator');

		$workflowId = (int) $this->getState('filter.workflow_id');

		if ($workflowId > 0)
		{
			$table->load($workflowId);
		}

		return (object) $table->getProperties();
	}

}
