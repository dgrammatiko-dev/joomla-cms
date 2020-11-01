<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\View\File;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\AbstractView;

/**
 * View to edit an file.
 *
 * @since  4.0.0
 */
class RawView extends AbstractView
{
    public function display($tpl = null)
    {
        $item = $this->get('File');

        if (empty($item->id) || !in_array($item->access, Factory::getUser()->getAuthorisedViewLevels()))
        {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $app = Factory::getApplication();

		$app->setHeader('Content-Type', $item->mime);
		$app->setHeader('Content-Transfer-Encoding', 'Binary');
		$app->setHeader('Expires', '0');
		$app->setHeader('Cache-Control', 'must-revalidate');
		$app->setHeader('Pragma', 'public');
		$app->setHeader('Content-Length', $item->filesize);
		$app->setHeader('Content-disposition', 'attachment; filename="' . $item->alias . '.' . $item->extension . '"');

		$app->sendHeaders();

		$filepath = Path::check(JPATH_SITE . '/' . $item->filepath);

        readfile($filepath);

        exit;
    }
}
