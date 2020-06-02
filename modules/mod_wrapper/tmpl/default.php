<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_wrapper
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->registerAndUseScript('com_wrapper.iframe', 'com_wrapper/iframe-height.min.js', [], ['defer' => true]);

?>
<iframe <?php echo $load; ?>
	id="blockrandom-<?php echo $id; ?>"
	name="<?php echo $target; ?>"
	src="<?php echo $url; ?>"
	width="<?php echo $width; ?>"
	height="<?php echo $height; ?>"
	scrolling="<?php echo $scroll; ?>"
	frameborder="<?php echo $frameborder; ?>"
	title="<?php echo $ititle; ?>"
	class="mod-wrapper wrapper">
	<?php echo Text::_('MOD_WRAPPER_NO_IFRAMES'); ?>
</iframe>
