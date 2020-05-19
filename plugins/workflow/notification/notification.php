<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Workflow.Notification
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\User;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Utilities\ArrayHelper;

/**
 * Workflow Notification Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgWorkflowNotification extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Loads the CMS Application for direct access
	 *
	 * @var   CMSApplicationInterface
	 * @since __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  3.9.0
	 */
	protected $db;

	/**
	 * The form event.
	 *
	 * @param   Form      $form  The form
	 * @param   stdClass  $data  The data
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentPrepareForm(Form $form, $data)
	{
		$context = $form->getName();

		// Extend the transition form
		if ($context !== 'com_workflow.transition')
		{
			return;
		}

		return $this->enhanceTransitionForm($form, $data);
	}

	/**
	 * Add different parameter options to the transition view, we need when executing the transition
	 *
	 * @param   Form      $form  The form
	 * @param   stdClass  $data  The data
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function enhanceTransitionForm(Form $form, $data)
	{
		Form::addFormPath(__DIR__ . '/forms');

		$form->loadFile('workflow_notification');

		return true;
	}

	/**
	 * Send a Notification to defined users a transion is performed
	 *
	 * @param   string   $context  The context for the content passed to the plugin.
	 * @param   array    $pks      A list of primary key ids of the content that has changed stage.
	 * @param   object  $data    Object containing data about the transition
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onWorkflowAfterTransition($context, $pks, $data)
	{
		$parts = explode('.', $context);

		// Check the extension 
		if (count($parts) < 2)
		{
			return false;
		}

		$component = $this->app->bootComponent($parts[0]);

		if (!$component->isWorkflowActive($context))
		{
			return false;
		}

		// Check if send-mail is active
		if (empty($data->options['notification_send_mail']))
		{
			return true;
		}

		// ID of the items whose state has changed. 
		$pks = ArrayHelper::toInteger($pks);

		if (empty($pks))
		{
			return true;
		}

		// Get UserIds of Receivers
		$userIds = $this->getUsersFromGroup($data);

		// The active user
		$user = $this->app->getIdentity();

		// Prepare Language for messages
		$default_language = ComponentHelper::getParams('com_languages')->get('administrator');
		$debug = $this->app->get('debug_lang');

		$modelName = $component->getModelName($context);
		$model = $component->getMVCFactory()->createModel($modelName, $this->app->getName(),  ['ignore_request' => true]);

		$authorId = 0; 

		// Add author of the item to the receivers array if the param email-author is set
		if (!empty($data->options['notification_email_author']) && !empty($item->created_by))
		{
			$author = $this->app->getIdentity($item->created_by);

			if (!empty($author) && !$author->block)
			{
				if (!in_array($author->id, $userIds))
				{
					$userIds[] = $author->id;
					$authorId = $author->id;
				}
			}
		}

		// Don't send the notification to the active user
		$key = array_search($user->id, $userIds);

		if ($key)
		{
			unset($userIds[$key]);
		}

		// If there are no receivers, stop here
		if (empty($userIds))
		{
			return true;
		}

		// Get the model for private messages
		$model_message = $this->app->bootComponent('com_messages')
					->getMVCFactory()->createModel('Message', 'Administrator');

		// Get the title of the stage
		$model_stage = $this->app->bootComponent('com_workflow')
					->getMVCFactory()->createModel('Stage', 'Administrator');

		$toStage = $model_stage->getItem($data->to_stage_id)->title;

		foreach ($pks as $pk)
		{
			// Get the item whose state has been changed
			$item = $model->getItem($pk);

			// Send Email to receivers
			foreach ($userIds as $user_id)
			{
				$receiver = User::getInstance($user_id);

				// Load language for messaging
				$lang = Language::getInstance($user->getParam('admin_language', $default_language), $debug);
				$lang->load('plg_workflow_notification');
				$messageText = sprintf($lang->_('PLG_WORKFLOW_NOTIFICATION_ON_TRANSITION_MSG'), $item->title, $user->name, $lang->_($toStage));

				if (!empty($data->options['notification_text'] && $user_id !== $authorId))
				{
					$messageText .= '<br>' . htmlspecialchars($lang->_($data->options['notification_text']));
				}

				if (!empty($data->options['notification_author_text'] && $user_id === $authorId))
				{
					$messageText .= '<br>' . htmlspecialchars($lang->_($data->options['notification_text_author']));
				}

				$message = array(
					'user_id_to' => $receiver->id,
					'subject' => sprintf($lang->_('PLG_WORKFLOW_NOTIFICATION_ON_TRANSITION_SUBJECT'), $modelName),
					'message' => $messageText,
				);

				$model_message->save($message);
			}
		}

		return true;
	}

	/*
	 * Get user_ids of receivers	
	 * 
	 * @param   object  $data    Object containing data about the transition
	 *
	 * @return   array  $userIds  The receivers
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getUsersFromGroup($data): Array
	{
		$users = [];

		// Single userIds
		if (!empty($data->options['notification_receivers']))
		{
			$users = ArrayHelper::toInteger($data->options['notification_receivers']);
		} 

		// Usergroups
		$groups = [];

		if (!empty($data->options['notification_groups']))
		{
			$groups = ArrayHelper::toInteger($data->options['notification_groups']);
		}

		$users2 = [];

		if (!empty($groups))
		{
			// UserIds from usergroups
			$model =  Factory::getApplication()->bootComponent('com_users')
				->getMVCFactory()->createModel('Users', 'Administrator', ['ignore_request' => true]);

			$model->setState('list.select', 'id');
			$model->setState('filter.groups', $groups);
			$model->setState('filter.state', 0);

			// Ids from usergroups 
			$groupUsers = $model->getItems();	
			$users2 = ArrayHelper::getColumn($groupUsers, 'id');
		}

		// Merge userIds from individual entries and userIDs from groups
		$userIds = array_unique(array_merge($users, $users2));

		return $userIds;
	}
}
