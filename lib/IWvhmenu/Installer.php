<?php
/**
 * PostNuke Application Framework
 *
 * @copyright (c) 2002, PostNuke Development Team
 * @link http://www.postnuke.com
 * @version $Id: pninit.php 22139 2007-06-01 10:57:16Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package PostNuke_Value_Addons
 * @subpackage Webbox
 */

/**
 * Initialise the iw_vhmenu module creating module tables and module vars
 * @author Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @return bool true if successful, false otherwise
 */
function iw_vhmenu_init()
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Checks if module iw_main is installed. If not returns error
	$modid = ModUtil::getIdFromName('iw_main');
	$modinfo = ModUtil::getInfo($modid);
	
	if($modinfo['state'] != 3){
		return LogUtil::registerError (__('Module iw_main is needed. You have to install the iw_main module previously to install it.', $dom));
	}
	
	// Check if the version needed is correct
	$versionNeeded = '2.0';
	if(!ModUtil::func('iw_main', 'admin', 'checkVersion', array('version' => $versionNeeded))){
		return false;
	}

	// Create module table
	if (!DBUtil::createTable('iw_vhmenu')) return false;

	//Create indexes
	$pntable = DBUtil::getTables();
	$c = $pntable['iw_vhmenu_column'];
	if (!DBUtil::createIndex($c['id_parent'],'iw_vhmenu', 'id_parent')) return false;	
	
	//Create module vars
	ModUtil::setVar('iw_vhmenu', 'LowBgColor', '#D6DEE7');// Background color when mouse is not over
	ModUtil::setVar('iw_vhmenu', 'LowSubBgColor', '#D6DEE7');// Background color when mouse is not over on subs
	ModUtil::setVar('iw_vhmenu', 'HighBgColor', '#EFEDDE');// Background color when mouse is over
	ModUtil::setVar('iw_vhmenu', 'HighSubBgColor', '#EFEDDE');// Background color when mouse is over on subs
	ModUtil::setVar('iw_vhmenu', 'FontLowColor', '#000000');// Font color when mouse is not over
	ModUtil::setVar('iw_vhmenu', 'FontSubLowColor', '#000000');// Font color subs when mouse is not over
	ModUtil::setVar('iw_vhmenu', 'FontHighColor', '#000000');// Font color when mouse is over
	ModUtil::setVar('iw_vhmenu', 'FontSubHighColor', '#000000');// Font color subs when mouse is over
	ModUtil::setVar('iw_vhmenu', 'BorderColor', '#AA3701');// Border color
	ModUtil::setVar('iw_vhmenu', 'BorderSubColor', '#000000');// Border color for subs
	ModUtil::setVar('iw_vhmenu', 'BorderWidth', 1);// Border width
	ModUtil::setVar('iw_vhmenu', 'BorderBtwnElmnts', 1);// Border between elements 1 or 0
	ModUtil::setVar('iw_vhmenu', 'FontFamily','Tahoma, Verdana, Arial, Helvetica, sans-serif');// Font family menu items
	ModUtil::setVar('iw_vhmenu', 'FontSize',9);// Font size menu items
	ModUtil::setVar('iw_vhmenu', 'FontBold',0);// Bold menu items 1 or 0
	ModUtil::setVar('iw_vhmenu', 'FontItalic',0);// Italic menu items 1 or 0
	ModUtil::setVar('iw_vhmenu', 'MenuTextCentered','center');// Item text position 'left', 'center' or 'right'
	ModUtil::setVar('iw_vhmenu', 'MenuCentered', 'left');// Menu horizontal position 'left', 'center' or 'right'
	ModUtil::setVar('iw_vhmenu', 'MenuVerticalCentered', 'top');// Menu vertical position 'top', 'middle','bottom' or static
	ModUtil::setVar('iw_vhmenu', 'ChildOverlap', '0.1');// horizontal overlap child/ parent
	ModUtil::setVar('iw_vhmenu', 'ChildVerticalOverlap', '0.1');// vertical overlap child/ parent
	ModUtil::setVar('iw_vhmenu', 'StartTop', 71);// Menu offset x coordinate
	ModUtil::setVar('iw_vhmenu', 'StartLeft', 20);// Menu offset y coordinate
	ModUtil::setVar('iw_vhmenu', 'VerCorrect', 0);// Multiple frames y correction
	ModUtil::setVar('iw_vhmenu', 'HorCorrect', 0);// Multiple frames x correction
	ModUtil::setVar('iw_vhmenu', 'LeftPaddng', 3);// Left padding
	ModUtil::setVar('iw_vhmenu', 'TopPaddng', 2);// Top padding
	ModUtil::setVar('iw_vhmenu', 'FirstLineHorizontal', 1);// SET TO 1 FOR HORIZONTAL MENU, 0 FOR VERTICAL
	ModUtil::setVar('iw_vhmenu', 'MenuFramesVertical', 1);// Frames in cols or rows 1 or 0
	ModUtil::setVar('iw_vhmenu', 'DissapearDelay', 1000);// delay before menu folds in
	ModUtil::setVar('iw_vhmenu', 'TakeOverBgColor', 1);// Menu frame takes over background color subitem frame
	ModUtil::setVar('iw_vhmenu', 'FirstLineFrame', 'navig');// Frame where first level appears
	ModUtil::setVar('iw_vhmenu', 'SecLineFrame', 'space');// Frame where sub levels appear
	ModUtil::setVar('iw_vhmenu', 'DocTargetFrame', 'space');// Frame where target documents appear
	ModUtil::setVar('iw_vhmenu', 'TargetLoc', '');// span id for relative positioning
	ModUtil::setVar('iw_vhmenu', 'HideTop', 0);// Hide first level when loading new document 1 or 0
	ModUtil::setVar('iw_vhmenu', 'MenuWrap', 1);// enables/ disables menu wrap 1 or 0
	ModUtil::setVar('iw_vhmenu', 'RightToLeft', 0);// enables/ disables right to left unfold 1 or 0
	ModUtil::setVar('iw_vhmenu', 'UnfoldsOnClick', 0);// Level 1 unfolds onclick/ onmouseover
	ModUtil::setVar('iw_vhmenu', 'WebMasterCheck', 0);// menu tree checking on or off 1 or 0
	ModUtil::setVar('iw_vhmenu', 'ShowArrow',1);// Uses arrow gifs when 1
	ModUtil::setVar('iw_vhmenu', 'KeepHilite', 1);// Keep selected path highligthed
	ModUtil::setVar('iw_vhmenu', 'height', 24);// Default height
	ModUtil::setVar('iw_vhmenu', 'width', 120);// Default width
	ModUtil::setVar('iw_vhmenu', 'imagedir', "iwvhmenu");// Default directori of menu images

	return true;
}

/**
 * Delete the iw_vhmenu module
 * @author Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @return bool true if successful, false otherwise
 */
function iw_vhmenu_delete()
{
	// Delete module table
	DBUtil::dropTable('iw_vhmenu');

	//Delete module vars
	ModUtil::delVar('iw_vhmenu', 'LowBgColor');
	ModUtil::delVar('iw_vhmenu', 'LowSubBgColor');
	ModUtil::delVar('iw_vhmenu', 'HighBgColor');
	ModUtil::delVar('iw_vhmenu', 'HighSubBgColor');
	ModUtil::delVar('iw_vhmenu', 'FontLowColor');
	ModUtil::delVar('iw_vhmenu', 'FontSubLowColor');
	ModUtil::delVar('iw_vhmenu', 'FontHighColor');
	ModUtil::delVar('iw_vhmenu', 'FontSubHighColor');
	ModUtil::delVar('iw_vhmenu', 'BorderColor');
	ModUtil::delVar('iw_vhmenu', 'BorderSubColor');
	ModUtil::delVar('iw_vhmenu', 'BorderWidth');
	ModUtil::delVar('iw_vhmenu', 'BorderBtwnElmnts');
	ModUtil::delVar('iw_vhmenu', 'FontFamily');
	ModUtil::delVar('iw_vhmenu', 'FontSize');
	ModUtil::delVar('iw_vhmenu', 'FontBold');
	ModUtil::delVar('iw_vhmenu', 'FontItalic');
	ModUtil::delVar('iw_vhmenu', 'MenuTextCentered');
	ModUtil::delVar('iw_vhmenu', 'MenuCentered');
	ModUtil::delVar('iw_vhmenu', 'MenuVerticalCentered');
	ModUtil::delVar('iw_vhmenu', 'ChildOverlap');
	ModUtil::delVar('iw_vhmenu', 'ChildVerticalOverlap');
	ModUtil::delVar('iw_vhmenu', 'StartTop');
	ModUtil::delVar('iw_vhmenu', 'StartLeft');
	ModUtil::delVar('iw_vhmenu', 'VerCorrect');
	ModUtil::delVar('iw_vhmenu', 'HorCorrect');
	ModUtil::delVar('iw_vhmenu', 'LeftPaddng');
	ModUtil::delVar('iw_vhmenu', 'TopPaddng');
	ModUtil::delVar('iw_vhmenu', 'FirstLineHorizontal');
	ModUtil::delVar('iw_vhmenu', 'MenuFramesVertical');
	ModUtil::delVar('iw_vhmenu', 'DissapearDelay');
	ModUtil::delVar('iw_vhmenu', 'TakeOverBgColor');
	ModUtil::delVar('iw_vhmenu', 'FirstLineFrame');
	ModUtil::delVar('iw_vhmenu', 'SecLineFrame');
	ModUtil::delVar('iw_vhmenu', 'DocTargetFrame');
	ModUtil::delVar('iw_vhmenu', 'TargetLoc');
	ModUtil::delVar('iw_vhmenu', 'HideTop');
	ModUtil::delVar('iw_vhmenu', 'MenuWrap');
	ModUtil::delVar('iw_vhmenu', 'RightToLeft');
	ModUtil::delVar('iw_vhmenu', 'UnfoldsOnClick');
	ModUtil::delVar('iw_vhmenu', 'WebMasterCheck');
	ModUtil::delVar('iw_vhmenu', 'ShowArrow');
	ModUtil::delVar('iw_vhmenu', 'KeepHilite');
	ModUtil::delVar('iw_vhmenu', 'height');
	ModUtil::delVar('iw_vhmenu', 'width');	
	ModUtil::delVar('iw_vhmenu', 'imagedir');

	//Deletion successfull
	return true;
}

/**
 * Update the iw_vhmenu module
 * @author Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @return bool true if successful, false otherwise
 */
function iw_vhmenu_upgrade($oldversion)
{
	if ($oldversion < 1.1) {
		if (!DBUtil::changeTable('iw_vhmenu')) return false;
		//Create indexes
		$pntable = DBUtil::getTables();
		$c = $pntable['iw_vhmenu_column'];
		if (!DBUtil::createIndex($c['id_parent'],'iw_vhmenu', 'id_parent')) return false;
	}
	return true;
}
