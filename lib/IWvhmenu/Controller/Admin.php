<?php
/**
 * Show the list of menu items created and do access to manage them
 * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @return:		The list of menu items
*/
function iw_vhmenu_admin_main()
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Security check
	if (!SecurityUtil::checkPermission( 'iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	// Checks if module iw_main is installed. If not returns error
	$modid = ModUtil::getIdFromName('iw_main');
	$modinfo = ModUtil::getInfo($modid);
	
	if($modinfo['state'] != 3) {
		return LogUtil::registerError (__('Module iw_main is needed. You have to install the iw_main module previously to install it.', $dom));
	}
	
	// Check if the version needed is correct. If not return error
	$versionNeeded = '2.0';
	if(!ModUtil::func('iw_main', 'admin', 'checkVersion',
	               array('version' => $versionNeeded))){
		return false;
	}

	// Create output object
	$view = Zikula_View::getInstance('iw_vhmenu',false);

	// Gets the groups information
	$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
	$grupsInfo = ModUtil::func('iw_main', 'user', 'getAllGroupsInfo',
	                        array('sv' => $sv));

    // Get the menu
    $menu = ModUtil::func('iw_vhmenu', 'admin', 'getsubmenu',
	                   array('id_parent' => 0,
	                         'grups_info' => $grupsInfo,
	                         'level' => 0));

	// Pass the data to the template
	$view->assign('menuarray', $menu);
	$view->assign('image_folder', ModUtil::getVar('iw_vhmenu', 'imagedir'));

	return $view->fetch('iw_vhmenu_admin_main.htm');
}


/**
 * Build an array with the submenu
 * @author:     Toni Ginard (aginard@xtec.cat)
 * @return:		An array with the submenu
*/
function iw_vhmenu_admin_getsubmenu ($args)
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Security check
	if (!SecurityUtil::checkPermission('iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	// Create output object
	$view = Zikula_View::getInstance('iw_vhmenu',false);

	// Get the data of each item
	$SubMenuData = ModUtil::apiFunc('iw_vhmenu', 'admin', 'getall', array( 'id_parent' => $args['id_parent'] ));

	// This provides a way to know if there is another option in the same level, so down arrow must be shown or not
	$iter_number = count($SubMenuData);
	$curr_iter = 0;

	foreach($SubMenuData as $option) {
		// Check whether to show down arrow or not
		$curr_iter++;
		if (($curr_iter < $iter_number)) { $downarrow = 1; } else { $downarrow = 0; }

		// Add the image triangles, one per sublevel
		for($i = 0, $levelimg = ''; $i < $args['level']; $i++) {
			$levelimg .= "<img src='modules/iw_vhmenu/pnimages/level.gif' />";
		}

		// If the URL is empty, put ---
		($option['url'] != '') ? $url = $option['url'] : $url = '---';

		// Get the groups and process them
		$groups = substr($option['groups'],0,-1);
		$groups = explode('$$',$groups);
		array_shift($groups);
		$groups_array = '';
		foreach($groups as $group){
			$group_subgroup = explode('|',$group);
			$name_group = ($group_subgroup[0] == '0') ? __('All',$dom) : $args['grups_info'][$group_subgroup[0]];
			if($group_subgroup[0] == '-1'){$name_group = __('Unregistered', $dom);}
			$name_subgroup = ($group_subgroup[1] == '0') ? __('All',$dom) : '';
			$groups_array .= $name_group;
			if($group_subgroup[1] != '0'){
				$groups_array .= '/'.$name_subgroup;
			}
			$groups_array .= ' <a href="index.php?module=iw_vhmenu&amp;type=admin&amp;func=del_group&amp;group='.$group_subgroup[0].'|'.$group_subgroup[1].'&amp;mid='.$option['mid'].'"><img src="modules/iw_vhmenu/pnimages/del.gif" /></a><br />';
		}

		// Calculate the padding
		$padding = $args['level']*20;
		$padding .= 'px';

		// Build the option and put it within the menu
		$MenuData[] = array(	'mid' 	=> $option['mid'],
					'text' 		=> $option['text'],
					'descriu'	=> $option['descriu'],
					'level' 	=> $levelimg,
					'url' 		=> $url,
					'width' 	=> $option['width'],
					'height' 	=> $option['height'],
					'active' 	=> $option['active'],
					'groups_array'	=> $groups_array,
					'bg_image' 	=> $option['bg_image'],
					'imagepath' 	=> ModUtil::getVar('iw_vhmenu', 'imagedir').'/'.$option['bg_image'],
					'id_parent'	=> $option['id_parent'],
					'iorder'	=> $option['iorder'],
					'grafic' 	=> $option['grafic'],
					'imagepath1'    => ModUtil::getVar('iw_vhmenu', 'imagedir').'/'.$option['image1'],
					'imagepath2'    => ModUtil::getVar('iw_vhmenu', 'imagedir').'/'.$option['image2'],
					'padding' 	=> $padding,
					'downarrow'	=> $downarrow );
		// Add the options
		$SubmenuData = ModUtil::func('iw_vhmenu', 'admin', 'getsubmenu', array(	'id_parent' => $option['mid'], 												'grups_info' => $args['grups_info'],
											'level' => $args['level'] + 1 ));
		if (!empty($SubmenuData)) { // If the menu has items, save them
			foreach ($SubmenuData as $item) // This foreach converts an n-dimension array in a 1-dimension array, suitable for the template
				$MenuData[] = $item;
		}
	}

	return $MenuData;
}

/**
 * Show the information about the module
 * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @return:	The information about this module
*/
function iw_vhmenu_admin_module()
{
	// Create output object
	$view = Zikula_View::getInstance('iw_vhmenu',false);

	$module = ModUtil::func('iw_main', 'user', 'module_info', array('module_name' => 'iw_vhmenu', 'type' => 'admin'));

	$view -> assign('module', $module);
	return $view -> fetch('iw_vhmenu_user_module.htm');
}

/**
 * Show a form needed to create a new menu item
 * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @return:	The form needed to create a new item
*/
function iw_vhmenu_admin_new($args)
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Get parameters from whatever input we need.
    	$mid = FormUtil::getPassedValue('mid', isset($args['mid']) ? $args['mid'] : null, 'REQUEST');
    	$text = FormUtil::getPassedValue('text', isset($args['text']) ? $args['text'] : null, 'POST');
    	$url = FormUtil::getPassedValue('url', isset($args['url']) ? $args['url'] : null, 'POST');
    	$bg_image = FormUtil::getPassedValue('bg_image', isset($args['bg_image']) ? $args['bg_image'] : null, 'POST');
    	$height = FormUtil::getPassedValue('height', isset($args['height']) ? $args['height'] : null, 'POST');
    	$width = FormUtil::getPassedValue('width', isset($args['width']) ? $args['width'] : null, 'POST');
    	$grup = FormUtil::getPassedValue('grup', isset($args['grup']) ? $args['grup'] : null, 'POST');
    	$subgrup = FormUtil::getPassedValue('subgrup', isset($args['subgrup']) ? $args['subgrup'] : null, 'POST');
    	$active = FormUtil::getPassedValue('active', isset($args['active']) ? $args['active'] : null, 'POST');
    	$target = FormUtil::getPassedValue('target', isset($args['target']) ? $args['target'] : null, 'POST');
    	$descriu = FormUtil::getPassedValue('descriu', isset($args['descriu']) ? $args['descriu'] : null, 'POST');
    	$m = FormUtil::getPassedValue('m', isset($args['m']) ? $args['m'] : null, 'REQUEST');
    	$id_parent = FormUtil::getPassedValue('id_parent', isset($args['id_parent']) ? $args['id_parent'] : null, 'POST');
    	$canvi = FormUtil::getPassedValue('canvi', isset($args['canvi']) ? $args['canvi'] : null, 'POST');
    	$grafic = FormUtil::getPassedValue('grafic', isset($args['grafic']) ? $args['grafic'] : null, 'POST');
    	$image1 = FormUtil::getPassedValue('image1', isset($args['image1']) ? $args['image1'] : null, 'POST');
    	$image2 = FormUtil::getPassedValue('image2', isset($args['image2']) ? $args['image2'] : null, 'POST');

	// Security check
	if (!SecurityUtil::checkPermission( 'iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	// Create output object
	$view = Zikula_View::getInstance('iw_vhmenu',false);

	// Init the height i width values
	$height = ($height == 0) ? ModUtil::getVar('iw_vhmenu', 'height') : $height;
	$width = ($width == 0) ? ModUtil::getVar('iw_vhmenu', 'width') : $width;
		
	// A copy is required, so the information is loaded
	if(!empty($mid) && !$canvi){
		$registre = ModUtil::apiFunc('iw_vhmenu', 'admin', 'get', array('mid' => $mid));
		if (!$registre) {
			return LogUtil::registerError (__('Menu option not found', $dom));
		}
		$text = $registre['text'];
		$descriu = $registre['descriu'];
		$url = $registre['url'];
		$bg_image = $registre['bg_image'];
		$height = $registre['height'];
		$width = $registre['width'];
		$active = $registre['active'];
		$target = $registre['target'];
		$id_parent = $registre['id_parent'];
		$grafic = $registre['grafic'];
		$image1 = $registre['image1'];
		$image2 = $registre['image2'];
		$grup = $registre['groups'];
	}

	// Get the images available in images directory
	$dir = ModUtil::getVar('iw_main', 'documentRoot').'/'.ModUtil::getVar('iw_vhmenu', 'imagedir');

	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if(filetype($dir . $file) == '' &&
					(strtolower(substr($file,-4)) == '.gif' ||
					strtolower(substr($file,-4)) == '.jpg' ||
					strtolower(substr($file,-4)) == '.bmp' ||
					strtolower(substr($file,-4)) == '.png')){
					$images[] = array('filename' => $file);
				}
			}
			closedir($dh);
		}
	}

	switch ($m) {
		case 'n':
			$accio = __('Add a new option to the menu', $dom);
			$acciosubmit = __('Create the option of the menu', $dom);
			break;
		case 'e':
			$accio = __('Option edit', $dom);
			$acciosubmit = __('Save changes', $dom);
			break;
		case 'c':
			$accio = __('Copy the option', $dom);
			$acciosubmit = __('Copy', $dom);
			break;
		case 's':
			$accio = __('Add a new option to the submenu', $dom);
			$acciosubmit = __('Create the option of the submenu', $dom);
			break;
	}

	// get the subgroups of the intranet
	// Activate when iw_groups will be avaliable
	// $subgroups = $dades -> Subgrups($group,__('All', $dom));

	// get the intranet groups
	$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
	$grups = ModUtil::func('iw_main', 'user', 'getAllGroups', array('plus' => __('All', $dom),
																'less' => ModUtil::getVar('iw_myrole', 'rolegroup'),
																'sv' => $sv));
	$grups[] = array('id' => '-1',
				'name' => __('Unregistered', $dom));

	// get the intranet groups again without the possibility of all the groups
	if(ModUtil::available('iw_webbox')){
		$view -> assign('iwwebbox', true);
	}
	$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
	$grups1 = ModUtil::func('iw_main', 'user', 'getAllGroups', array('sv' => $sv,
																	'less' => ModUtil::getVar('iw_myrole', 'rolegroup')));
	$security = SecurityUtil::generateAuthKey();
	$view -> assign('security', $security);
	$view -> assign('mid', $mid);
	$view -> assign('text', $text);
	$view -> assign('width', $width);
	$view -> assign('height', $height);
	$view -> assign('descriu', $descriu);
	$view -> assign('bg_image', $bg_image);
	$view -> assign('active', $active);
	$view -> assign('m', $m);
	$view -> assign('accio', $accio);
	$view -> assign('acciosubmit', $acciosubmit);	
	$view -> assign('target', $target);
	$view -> assign('url', $url);
	$view -> assign('id_parent', $id_parent);
	$view -> assign('images', $images);
	$view -> assign('initImagePath', ModUtil::getVar('iw_vhmenu', 'imagedir'));
	$view -> assign('grafic', $grafic);
	$view -> assign('image1', $image1);
	$view -> assign('image2', $image2);
	$view -> assign('grups', $grups);
	$view -> assign('grup', $grup);
	$view -> assign('subgrups', $subgrups);
	$view -> assign('subgrup', $subgrup);

	return $view -> fetch('iw_vhmenu_admin_new.htm');
}

/**
 * Show a form needed to create a new menu item
 * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @param:	
 * @return:	The form needed to create a new item
*/
function iw_vhmenu_admin_new_sub($args)
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Get parameters from whatever input we need.
    	$mid = FormUtil::getPassedValue('mid', isset($args['mid']) ? $args['mid'] : null, 'REQUEST');
    	$text = FormUtil::getPassedValue('text', isset($args['text']) ? $args['text'] : null, 'POST');
    	$url = FormUtil::getPassedValue('url', isset($args['url']) ? $args['url'] : null, 'POST');
    	$bg_image = FormUtil::getPassedValue('bg_image', isset($args['bg_image']) ? $args['bg_image'] : null, 'POST');
    	$height = FormUtil::getPassedValue('height', isset($args['height']) ? $args['height'] : null, 'POST');
    	$width = FormUtil::getPassedValue('width', isset($args['width']) ? $args['width'] : null, 'POST');
    	$grup = FormUtil::getPassedValue('grup', isset($args['grup']) ? $args['grup'] : null, 'POST');
    	$subgrup = FormUtil::getPassedValue('subgrup', isset($args['subgrup']) ? $args['subgrup'] : null, 'POST');
    	$active = FormUtil::getPassedValue('active', isset($args['active']) ? $args['active'] : null, 'POST');
    	$target = FormUtil::getPassedValue('target', isset($args['target']) ? $args['target'] : null, 'POST');
    	$descriu = FormUtil::getPassedValue('descriu', isset($args['descriu']) ? $args['descriu'] : null, 'POST');
    	$m = FormUtil::getPassedValue('m', isset($args['m']) ? $args['m'] : null, 'REQUEST');
    	$id_parent = FormUtil::getPassedValue('id_parent', isset($args['id_parent']) ? $args['id_parent'] : null, 'POST');
    	$canvi = FormUtil::getPassedValue('canvi', isset($args['canvi']) ? $args['canvi'] : null, 'POST');
    	$grafic = FormUtil::getPassedValue('grafic', isset($args['grafic']) ? $args['grafic'] : null, 'POST');
    	$image1 = FormUtil::getPassedValue('image1', isset($args['image1']) ? $args['image1'] : null, 'POST');
    	$image2 = FormUtil::getPassedValue('image2', isset($args['image2']) ? $args['image2'] : null, 'POST');
    	$level = FormUtil::getPassedValue('level', isset($args['level']) ? $args['level'] : null, 'POST');

	// Security check
	if (!SecurityUtil::checkPermission( 'iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	// Create output object
	$view = Zikula_View::getInstance('iw_vhmenu',false);

	// Get a menu item
	if(!empty($mid) && !$canvi){
		$registre = ModUtil::apiFunc('iw_vhmenu', 'admin', 'get', array('mid' => $mid));
		if (!$registre) {
			return LogUtil::registerError (__('Menu option not found', $dom));
		}
		$level = $registre['level'] + 1;
	}

	// Get the images available in images directory
	$dir = ModUtil::getVar('iw_main', 'documentRoot').'/'.ModUtil::getVar('iw_vhmenu', 'imagedir');
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if(filetype($dir . $file) == '' &&
					(strtolower(substr($file,-4)) == '.gif' ||
					strtolower(substr($file,-4)) == '.jpg' ||
					strtolower(substr($file,-4)) == '.bmp' ||
					strtolower(substr($file,-4)) == '.png')){
					$images[] = array('filename' => $file);
				}
			}
			closedir($dh);
		}
	}

	// get the subgroups of the intranet
	// Activate when iw_groups will be avaliable
	// $subgroups = $dades -> Subgrups($group,__('All', $dom));

	// get the intranet groups
	$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
	$grups = ModUtil::func('iw_main', 'user', 'getAllGroups', array('plus' => __('All', $dom),
																'less' => ModUtil::getVar('iw_myrole', 'rolegroup'),
																'sv' => $sv));
	$grups[] = array(	'id' => '-1',
						'name' => __('Unregistered', $dom));

	// Init the height i width values
	$height = ($height == 0) ? ModUtil::getVar('iw_vhmenu', 'height') : $height;
	$width = ($width == 0) ? ModUtil::getVar('iw_vhmenu', 'width') : $width;

	// get the intranet groups again without the possibility of all the groups
	$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
	$grups1 = ModUtil::func('iw_main', 'user', 'getAllGroups', array('sv' => $sv,
																	'less' => ModUtil::getVar('iw_myrole', 'rolegroup')));

	$security = SecurityUtil::generateAuthKey();
	$view -> assign('security', $security);
	$view -> assign('mid', $mid);
	$view -> assign('level', $level);
	$view -> assign('text', $text);
	$view -> assign('url', $url);
	$view -> assign('descriu', $descriu);	
	$view -> assign('width', $width);
	$view -> assign('height', $height);
	$view -> assign('bg_image', $bg_image);	
	$view -> assign('images', $images);
	$view -> assign('initImagePath', ModUtil::getVar('iw_vhmenu', 'imagedir'));
	$view -> assign('ip_parent', $ip_parent);	
	$view -> assign('grafic', $grafic);
	$view -> assign('image1', $image1);
	$view -> assign('image2', $image2);
	$view -> assign('active', $active);
	$view -> assign('grups', $grups);
	$view -> assign('grup', $grup);	
	$view -> assign('subgrups', $subgrups);
	$view -> assign('subgrup', $subgrup);
	
	return $view -> fetch('iw_vhmenu_admin_new_sub.htm');
}

/**
 * Recursive function that returns all the menu items associated with a item of the first level
 * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @param:		Array with the id of the item of the first level
 * @return:		The items information
*/
function iw_vhmenu_admin_menu_items($args)
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Get parameters from whatever input we need
	$id_parent = FormUtil::getPassedValue('id_parent', isset($args['id_parent']) ? $args['id_parent'] : null, 'POST');
	$objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'POST');
	if (!empty($objectid)) {
		$id_parent = $objectid;
	}

	// Security check
	if (!SecurityUtil::checkPermission( 'iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	if($id_parent != 0){
		ModUtil::setVar('iw_vhmenu', 'arbre', ModUtil::getVar('iw_vhmenu','arbre').$id_parent.'$');
	}
	$itemsmenu = ModUtil::apiFunc('iw_vhmenu', 'admin', 'getall', array('id' => $id_parent));
	foreach ($itemsmenu as $itemmenu){
		ModUtil::func('iw_vhmenu', 'admin', 'menu_items', array('id_parent' => $itemmenu['mid']));
	}

	return $menuarray;
}

/**
 * Show the form that allow to choose a new group
 * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @param:	Array with the id of the item, the group, the subgroup and the group_db
 * @return:	The form with all the groups ans subgroups
*/
function iw_vhmenu_admin_add_group($args)
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Get parameters from whatever input we need
	$mid = FormUtil::getPassedValue('mid', isset($args['mid']) ? $args['mid'] : null, 'REQUEST');

	// Security check
	if (!SecurityUtil::checkPermission( 'iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	// Create output object
	$view = Zikula_View::getInstance('iw_vhmenu',false);
		
	// A copy is required, so the information is loaded
	if(!empty($mid)){	
		$registre = ModUtil::apiFunc('iw_vhmenu', 'admin', 'get', array('mid' => $mid));
		if (!$registre) {
			return LogUtil::registerError (__('Menu option not found', $dom));
		}
		$text      = $registre['text'];
		$descriu   = $registre['descriu'];
		$url       = $registre['url'];
		$bg_image  = $registre['bg_image'];
		$height    = $registre['height'];
		$width     = $registre['width'];
		$active    = $registre['active'];
		$target    = $registre['target'];
		$groups_db = $registre['groups'];
	}
	else {
		return LogUtil::registerError (__('Menu option not found', $dom));
	}

	// get the intranet groups
	$sv    = ModUtil::func('iw_main', 'user', 'genSecurityValue');
	$grups = ModUtil::func('iw_main', 'user', 'getAllGroups', array('plus' => __('All', $dom),
																 'less' => ModUtil::getVar('iw_myrole', 'rolegroup'),
																 'sv' => $sv));
	$grups[] = array('id' => '-1',
				'name' => __('Unregistered', $dom));

	$security = SecurityUtil::generateAuthKey();
	$view -> assign('security', $security);
	$view -> assign('mid', $mid);
	$view -> assign('text', $text);
	$view -> assign('descriu', $descriu);
	$view -> assign('url', $url);
	$view -> assign('groups_db', $groups_db);
	$view -> assign('grups', $grups);

	return $view -> fetch('iw_vhmenu_admin_add_group.htm');
}


/**
 * Check the information received from the form of creation of a item and call the api function to create it
 * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @param:	Array with the form information needed in case the form is reloaded
 * @return:	Redirect to the main admin page
*/
function iw_vhmenu_admin_create($args)
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Get parameters from whatever input we need.
	$mid = FormUtil::getPassedValue('mid', isset($args['mid']) ? $args['mid'] : null, 'REQUEST');
	$text = FormUtil::getPassedValue('text', isset($args['text']) ? $args['text'] : null, 'POST');
	$url = FormUtil::getPassedValue('url', isset($args['url']) ? $args['url'] : null, 'POST');
	$bg_image = FormUtil::getPassedValue('bg_image', isset($args['bg_image']) ? $args['bg_image'] : null, 'POST');
	$height = FormUtil::getPassedValue('height', isset($args['height']) ? $args['height'] : null, 'POST');
	$width = FormUtil::getPassedValue('width', isset($args['width']) ? $args['width'] : null, 'POST');
	$grup = FormUtil::getPassedValue('grup', isset($args['grup']) ? $args['grup'] : null, 'POST');
	$subgrup = FormUtil::getPassedValue('subgrup', isset($args['subgrup']) ? $args['subgrup'] : null, 'POST');
	$active = FormUtil::getPassedValue('active', isset($args['active']) ? $args['active'] : null, 'POST');
	$target = FormUtil::getPassedValue('target', isset($args['target']) ? $args['target'] : null, 'POST');
	$descriu = FormUtil::getPassedValue('descriu', isset($args['descriu']) ? $args['descriu'] : null, 'POST');
	$m = FormUtil::getPassedValue('m', isset($args['m']) ? $args['m'] : null, 'REQUEST');
	$id_parent = FormUtil::getPassedValue('id_parent', isset($args['id_parent']) ? $args['id_parent'] : null, 'REQUEST');
	$grafic = FormUtil::getPassedValue('grafic', isset($args['grafic']) ? $args['grafic'] : null, 'POST');
	$image1 = FormUtil::getPassedValue('image1', isset($args['image1']) ? $args['image1'] : null, 'POST');
	$image2 = FormUtil::getPassedValue('image2', isset($args['image2']) ? $args['image2'] : null, 'POST');

	// Security check
	if (!SecurityUtil::checkPermission( 'iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	// Confirm authorisation code
	if (!SecurityUtil::confirmAuthKey()) {
		return LogUtil::registerAuthidError (ModUtil::url('iw_vhmenu', 'admin', 'main'));
	}

	$active = ($active == 'on') ? 1 : 0;
	$grafic = ($grafic == 'on') ? 1 : 0;

	if($m != 'c'){
		// Construct the groups string
		(!isset($subgrup)) ? $subgrup = 0 : "";
		$groups = '$$'.$grup.'|'.$subgrup.'$';
	}else{
		$groups = $grup;
	}

	// Modify a menu item
	if($m == 'e'){
		$lid = ModUtil::apiFunc('iw_vhmenu', 'admin', 'update', array('mid' => $mid,
										'text' => $text,
										'descriu' => $descriu,
										'active' => $active,
										'target' => $target,
										'url' => $url,
										'width' => $width,
										'height' => $height,
										'bg_image' => $bg_image,
										'grafic' => $grafic,
										'image1' => $image1,
										'image2' => $image2));
		if ($lid != false) {
			// A item has been modified
			LogUtil::registerStatus (__('The option has been updated successfully', $dom));

			//Reset the users menus for all users
			$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
			ModUtil::func('iw_main', 'user', 'usersVarsDelModule', array('module' => 'iw_vhmenu',
											'name' => 'userMenu',
											'sv' => $sv));
		}
	}else{
		$lid = ModUtil::apiFunc('iw_vhmenu', 'admin', 'create', array('text' => $text,
										'descriu' => $descriu,
										'active' => $active,
										'target' => $target,
										'url' => $url,
										'groups' => $groups,
										'width' => $width,
										'height' => $height,
										'id_parent' => $id_parent,
										'bg_image' => $bg_image,
										'grafic' => $grafic,
										'image1' => $image1,
										'image2' => $image2));
    		if ($lid != false) {
        		// A new entry has been created
			LogUtil::registerStatus (__('A new option has been created', $dom));

			//Reorder the menu items
			ModUtil::func('iw_vhmenu', 'admin', 'reorder', array('id_parent' => 0));

			//Reset the users menus for all users
			$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
			ModUtil::func('iw_main', 'user', 'usersVarsDelModule', array('module' => 'iw_vhmenu',
											'name' => 'userMenu',
											'sv' => $sv));
		}
	}
  
	//Redirect to admin main page
	return System::redirect(ModUtil::url('iw_vhmenu', 'admin', 'main'));
}

/**
 * Check the information received from the form of creation of a submenu item and call the api function to create it
 * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @param:	Array with the form information needed in case the form is reloaded
 * @return:	Redirect to the main admin page
*/
function iw_vhmenu_admin_create_sub($args)
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Get parameters from whatever input we need
    	$text = FormUtil::getPassedValue('text', isset($args['text']) ? $args['text'] : null, 'POST');
    	$url = FormUtil::getPassedValue('url', isset($args['url']) ? $args['url'] : null, 'POST');
    	$descriu = FormUtil::getPassedValue('descriu', isset($args['descriu']) ? $args['descriu'] : null, 'POST');
    	$target = FormUtil::getPassedValue('target', isset($args['target']) ? $args['target'] : null, 'POST');
    	$grup = FormUtil::getPassedValue('grup', isset($args['grup']) ? $args['grup'] : null, 'POST');
    	$subgrup = FormUtil::getPassedValue('subgrup', isset($args['subgrup']) ? $args['subgrup'] : null, 'POST');
    	$active = FormUtil::getPassedValue('active', isset($args['active']) ? $args['active'] : null, 'POST');
    	$width = FormUtil::getPassedValue('width', isset($args['width']) ? $args['width'] : null, 'POST');
    	$height = FormUtil::getPassedValue('height', isset($args['height']) ? $args['height'] : null, 'POST');
    	$mid = FormUtil::getPassedValue('mid', isset($args['mid']) ? $args['mid'] : null, 'POST');
    	$level = FormUtil::getPassedValue('level', isset($args['level']) ? $args['level'] : null, 'POST');
    	$bg_image = FormUtil::getPassedValue('bg_image', isset($args['bg_image']) ? $args['bg_image'] : null, 'POST');
    	$grafic = FormUtil::getPassedValue('grafic', isset($args['grafic']) ? $args['grafic'] : null, 'POST');
    	$image1 = FormUtil::getPassedValue('image1', isset($args['image1']) ? $args['image1'] : null, 'POST');
    	$image2 = FormUtil::getPassedValue('image2', isset($args['image2']) ? $args['image2'] : null, 'POST');

	// Security check
	if (!SecurityUtil::checkPermission( 'iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	// Confirm authorisation code
	if (!SecurityUtil::confirmAuthKey()) {
		return LogUtil::registerAuthidError (ModUtil::url('iw_vhmenu', 'admin', 'main'));
	}

	$active = ($active == 'on') ? 1 : 0;
	$grafic = ($grafic == 'on') ? 1 : 0;

	// Construct the group string
	$subgrup = (!isset($subgrup)) ? 0 : "";
	$groups = '$$'.$grup.'|'.$subgrup.'$';

	// Create a submenu item
	$lid = ModUtil::apiFunc('iw_vhmenu', 'admin', 'create_sub', array('mid'=>$mid,
									'text' => $text,
									'descriu' => $descriu,
									'active' => $active,
									'target' => $target,
									'url' => $url,
									'groups' => $groups,
									'width' => $width,
									'height' => $height,
									'id_parent' => $mid,
									'level' => $level,
									'bg_image' => $bg_image,
									'grafic' => $grafic,
									'image1' => $image1,
									'image2' => $image2));
	if ($lid != false) {
       		// Successfull creation
		LogUtil::registerStatus (__('A new option has been created', $dom));

		// Reorder the menu items
		ModUtil::func('iw_vhmenu', 'admin', 'reorder', array('id_parent' => $mid));

		// Reset the users menus for all users
		$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
		ModUtil::func('iw_main', 'user', 'usersVarsDelModule', array('module' => 'iw_vhmenu',
										'name' => 'userMenu',
										'sv' => $sv));
	}

	// Redirect to admin main page
	return System::redirect(ModUtil::url('iw_vhmenu', 'admin', 'main'));
}

/**
 * Check the information received from the form of creation of a new group with access to the menu
 * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @param:	Array with the form received from the form
 * @return:	Redirect to the main admin page
*/
function iw_vhmenu_admin_create_add_group($args)
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Get parameters from whatever input we need
    	$mid = FormUtil::getPassedValue('mid', isset($args['mid']) ? $args['mid'] : null, 'REQUEST');
    	$grup = FormUtil::getPassedValue('grup', isset($args['grup']) ? $args['grup'] : null, 'POST');
    	$subgrup = FormUtil::getPassedValue('subgrup', isset($args['subgrup']) ? $args['subgrup'] : null, 'POST');
    	$groups_db = FormUtil::getPassedValue('groups_db', isset($args['groups_db']) ? $args['groups_db'] : null, 'POST');

	// Security check
	if (!SecurityUtil::checkPermission( 'iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	// Confirm authorisation code
	if (!SecurityUtil::confirmAuthKey()) {
		return LogUtil::registerAuthidError (ModUtil::url('iw_vhmenu', 'admin', 'main'));
	}

	// Construct the group string
	$subgrup = (!isset($subgrup)) ? 0: "";
	$groups = $groups_db.'$'.$grup.'|'.$subgrup.'$';

	// Modify the groups that have access to the menu item
	$lid = ModUtil::apiFunc('iw_vhmenu', 'admin', 'modify_grup', array('mid' => $mid,
									'groups' => $groups));
	if ($lid != false) {
		// A new entry has been created
		LogUtil::registerStatus (__('The access to the option has been granted to a group', $dom));

		//Reset the users menus for all users
		$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
		ModUtil::func('iw_main', 'user', 'usersVarsDelModule', array('module' => 'iw_vhmenu',
										'name' => 'userMenu',
										'sv' => $sv));
	}

	//Redirect to admin main page
	return System::redirect(ModUtil::url('iw_vhmenu', 'admin', 'main'));
}

/**
 * Show a form that allow to define the properties of the menu
 * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @return:	Redirect to the admin config page
*/
function iw_vhmenu_admin_conf()
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Security check
	if (!SecurityUtil::checkPermission( 'iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	// Create output object
	$view = Zikula_View::getInstance('iw_vhmenu',false);

	if(!file_exists(ModUtil::getVar('iw_main', 'documentRoot').'/'.ModUtil::getVar('iw_vhmenu','imagedir'))){
		$view -> assign('noFolder', true);
	}


	$menu_vars = array('LowBgColor' => ModUtil::getVar('iw_vhmenu', 'LowBgColor'),
				'LowSubBgColor' => ModUtil::getVar('iw_vhmenu', 'LowSubBgColor'),
				'HighBgColor' => ModUtil::getVar('iw_vhmenu', 'HighBgColor'),
				'HighSubBgColor' => ModUtil::getVar('iw_vhmenu', 'HighSubBgColor'),
				'FontLowColor' => ModUtil::getVar('iw_vhmenu', 'FontLowColor'),
				'FontSubLowColor' => ModUtil::getVar('iw_vhmenu', 'FontSubLowColor'),
				'FontHighColor' => ModUtil::getVar('iw_vhmenu', 'FontHighColor'),
				'FontSubHighColor' => ModUtil::getVar('iw_vhmenu', 'FontSubHighColor'),
				'BorderColor' => ModUtil::getVar('iw_vhmenu', 'BorderColor'),
				'BorderSubColor' => ModUtil::getVar('iw_vhmenu', 'BorderSubColor'),
				'BorderWidth' => ModUtil::getVar('iw_vhmenu', 'BorderWidth'),
				'BorderBtwnElmnts' => ModUtil::getVar('iw_vhmenu', 'BorderBtwnElmnts'),
				'FontFamily' => ModUtil::getVar('iw_vhmenu', 'FontFamily'),
				'FontSize' => ModUtil::getVar('iw_vhmenu', 'FontSize'),
				'FontBold' => ModUtil::getVar('iw_vhmenu', 'FontBold'),
				'FontItalic' => ModUtil::getVar('iw_vhmenu', 'FontItalic'),
				'MenuTextCentered' => ModUtil::getVar('iw_vhmenu', 'MenuTextCentered'),
				'MenuCentered' => ModUtil::getVar('iw_vhmenu', 'MenuCentered'),
				'MenuVerticalCentered' => ModUtil::getVar('iw_vhmenu', 'MenuVerticalCentered'),
				'ChildOverlap' => ModUtil::getVar('iw_vhmenu', 'ChildOverlap'),
				'ChildVerticalOverlap' => ModUtil::getVar('iw_vhmenu', 'ChildVerticalOverlap'),
				'StartTop' => ModUtil::getVar('iw_vhmenu', 'StartTop'),
				'StartLeft' => ModUtil::getVar('iw_vhmenu', 'StartLeft'),
				'VerCorrect' => ModUtil::getVar('iw_vhmenu', 'VerCorrect'),
				'HorCorrect' => ModUtil::getVar('iw_vhmenu', 'HorCorrect'),
				'LeftPaddng' => ModUtil::getVar('iw_vhmenu', 'LeftPaddng'),
				'TopPaddng' => ModUtil::getVar('iw_vhmenu', 'TopPaddng'),
				'FirstLineHorizontal' => ModUtil::getVar('iw_vhmenu', 'FirstLineHorizontal'),
				'MenuFramesVertical' => ModUtil::getVar('iw_vhmenu', 'MenuFramesVertical'),
				'DissapearDelay' => ModUtil::getVar('iw_vhmenu', 'DissapearDelay'),
				'TakeOverBgColor' => ModUtil::getVar('iw_vhmenu', 'TakeOverBgColor'),
				'FirstLineFrame' => ModUtil::getVar('iw_vhmenu', 'FirstLineFrame'),
				'SecLineFrame' => ModUtil::getVar('iw_vhmenu', 'SecLineFrame'),
				'DocTargetFrame' => ModUtil::getVar('iw_vhmenu', 'DocTargetFrame'),
				'TargetLoc' => ModUtil::getVar('iw_vhmenu', 'TargetLoc'),
				'HideTop' => ModUtil::getVar('iw_vhmenu', 'HideTop'),
				'MenuWrap' => ModUtil::getVar('iw_vhmenu', 'MenuWrap'),
				'RightToLeft' => ModUtil::getVar('iw_vhmenu', 'RightToLeft'),
				'UnfoldsOnClick' => ModUtil::getVar('iw_vhmenu', 'UnfoldsOnClick'),
				'WebMasterCheck' => ModUtil::getVar('iw_vhmenu', 'WebMasterCheck'),
				'ShowArrow' => ModUtil::getVar('iw_vhmenu', 'ShowArrow'),
				'KeepHilite' => ModUtil::getVar('iw_vhmenu', 'KeepHilite'),
				'height' => ModUtil::getVar('iw_vhmenu', 'height'),
				'width' => ModUtil::getVar('iw_vhmenu', 'width'),
				'imagedir' => ModUtil::getVar('iw_vhmenu', 'imagedir'));

	$security = SecurityUtil::generateAuthKey();
	$view -> assign('security', $security);
    $multizk = (isset($GLOBALS['PNConfig']['Multisites']['multi']) && $GLOBALS['PNConfig']['Multisites']['multi'] == 1) ? 1 : 0;
	$view -> assign('multizk', $multizk);
	$view -> assign('directoriroot', ModUtil::getVar('iw_main', 'documentRoot'));
	$view -> assign('menu_vars',$menu_vars);
	return $view -> fetch('iw_vhmenu_admin_conf.htm');
}

/**
 * Update the module vars with the properties of the menu
 * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @param:	Array with the form information needed
 * @return:	True if success
*/
function iw_vhmenu_admin_conf_update($args)
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Get parameters from whatever input we need
    	$LowBgColor = FormUtil::getPassedValue('LowBgColor', isset($args['LowBgColor']) ? $args['LowBgColor'] : null, 'POST');
    	$LowSubBgColor = FormUtil::getPassedValue('LowSubBgColor', isset($args['LowSubBgColor']) ? $args['LowSubBgColor'] : null, 'POST');
    	$HighBgColor = FormUtil::getPassedValue('HighBgColor', isset($args['HighBgColor']) ? $args['HighBgColor'] : null, 'POST');
    	$HighSubBgColor = FormUtil::getPassedValue('HighSubBgColor', isset($args['HighSubBgColor']) ? $args['HighSubBgColor'] : null, 'POST');
    	$FontLowColor = FormUtil::getPassedValue('FontLowColor', isset($args['FontLowColor']) ? $args['FontLowColor'] : null, 'POST');
    	$FontSubLowColor = FormUtil::getPassedValue('FontSubLowColor', isset($args['FontSubLowColor']) ? $args['FontSubLowColor'] : null, 'POST');
    	$FontHighColor = FormUtil::getPassedValue('FontHighColor', isset($args['FontHighColor']) ? $args['FontHighColor'] : null, 'POST');
    	$FontSubHighColor = FormUtil::getPassedValue('FontSubHighColor', isset($args['FontSubHighColor']) ? $args['FontSubHighColor'] : null, 'POST');
    	$BorderColor = FormUtil::getPassedValue('BorderColor', isset($args['BorderColor']) ? $args['BorderColor'] : null, 'POST');
    	$BorderSubColor = FormUtil::getPassedValue('BorderSubColor', isset($args['BorderSubColor']) ? $args['BorderSubColor'] : null, 'POST');
    	$BorderWidth = FormUtil::getPassedValue('BorderWidth', isset($args['BorderWidth']) ? $args['BorderWidth'] : null, 'POST');
    	$BorderBtwnElmnts = FormUtil::getPassedValue('BorderBtwnElmnts', isset($args['BorderBtwnElmnts']) ? $args['BorderBtwnElmnts'] : null, 'POST');
    	$FontFamily = FormUtil::getPassedValue('FontFamily', isset($args['FontFamily']) ? $args['FontFamily'] : null, 'POST');
    	$FontSize = FormUtil::getPassedValue('FontSize', isset($args['FontSize']) ? $args['FontSize'] : null, 'POST');
    	$FontBold = FormUtil::getPassedValue('FontBold', isset($args['FontBold']) ? $args['FontBold'] : null, 'POST');
    	$FontItalic = FormUtil::getPassedValue('FontItalic', isset($args['FontItalic']) ? $args['FontItalic'] : null, 'POST');
    	$MenuTextCentered = FormUtil::getPassedValue('MenuTextCentered', isset($args['MenuTextCentered']) ? $args['MenuTextCentered'] : null, 'POST');
    	$MenuCentered = FormUtil::getPassedValue('MenuCentered', isset($args['MenuCentered']) ? $args['MenuCentered'] : null, 'POST');
    	$MenuVerticalCentered = FormUtil::getPassedValue('MenuVerticalCentered', isset($args['MenuVerticalCentered']) ? $args['MenuVerticalCentered'] : null, 'POST');
    	$ChildOverlap = FormUtil::getPassedValue('ChildOverlap', isset($args['ChildOverlap']) ? $args['ChildOverlap'] : null, 'POST');
    	$ChildVerticalOverlap = FormUtil::getPassedValue('ChildVerticalOverlap', isset($args['ChildVerticalOverlap']) ? $args['ChildVerticalOverlap'] : null, 'POST');
    	$StartTop = FormUtil::getPassedValue('StartTop', isset($args['StartTop']) ? $args['StartTop'] : null, 'POST');
    	$StartLeft = FormUtil::getPassedValue('StartLeft', isset($args['StartLeft']) ? $args['StartLeft'] : null, 'POST');
    	$VerCorrect = FormUtil::getPassedValue('VerCorrect', isset($args['VerCorrect']) ? $args['VerCorrect'] : null, 'POST');
    	$HorCorrect = FormUtil::getPassedValue('HorCorrect', isset($args['HorCorrect']) ? $args['HorCorrect'] : null, 'POST');
    	$LeftPaddng = FormUtil::getPassedValue('LeftPaddng', isset($args['LeftPaddng']) ? $args['LeftPaddng'] : null, 'POST');
    	$TopPaddng = FormUtil::getPassedValue('TopPaddng', isset($args['TopPaddng']) ? $args['TopPaddng'] : null, 'POST');
    	$FirstLineHorizontal = FormUtil::getPassedValue('FirstLineHorizontal', isset($args['FirstLineHorizontal']) ? $args['FirstLineHorizontal'] : null, 'POST');
    	$MenuFramesVertical = FormUtil::getPassedValue('MenuFramesVertical', isset($args['MenuFramesVertical']) ? $args['MenuFramesVertical'] : null, 'POST');
    	$DissapearDelay = FormUtil::getPassedValue('DissapearDelay', isset($args['DissapearDelay']) ? $args['DissapearDelay'] : null, 'POST');
    	$TakeOverBgColor = FormUtil::getPassedValue('TakeOverBgColor', isset($args['TakeOverBgColor']) ? $args['TakeOverBgColor'] : null, 'POST');
    	$FirstLineFrame = FormUtil::getPassedValue('FirstLineFrame', isset($args['FirstLineFrame']) ? $args['FirstLineFrame'] : null, 'POST');
    	$SecLineFrame = FormUtil::getPassedValue('SecLineFrame', isset($args['SecLineFrame']) ? $args['SecLineFrame'] : null, 'POST');
    	$DocTargetFrame = FormUtil::getPassedValue('DocTargetFrame', isset($args['DocTargetFrame']) ? $args['DocTargetFrame'] : null, 'POST');
    	$TargetLoc = FormUtil::getPassedValue('TargetLoc', isset($args['TargetLoc']) ? $args['TargetLoc'] : null, 'POST');
    	$HideTop = FormUtil::getPassedValue('HideTop', isset($args['HideTop']) ? $args['HideTop'] : null, 'POST');
    	$MenuWrap = FormUtil::getPassedValue('MenuWrap', isset($args['MenuWrap']) ? $args['MenuWrap'] : null, 'POST');
    	$RightToLeft = FormUtil::getPassedValue('RightToLeft', isset($args['RightToLeft']) ? $args['RightToLeft'] : null, 'POST');
    	$UnfoldsOnClick = FormUtil::getPassedValue('UnfoldsOnClick', isset($args['UnfoldsOnClick']) ? $args['UnfoldsOnClick'] : null, 'POST');
    	$WebMasterCheck = FormUtil::getPassedValue('WebMasterCheck', isset($args['WebMasterCheck']) ? $args['WebMasterCheck'] : null, 'POST');
    	$ShowArrow = FormUtil::getPassedValue('ShowArrow', isset($args['ShowArrow']) ? $args['ShowArrow'] : null, 'POST');
    	$KeepHilite = FormUtil::getPassedValue('KeepHilite', isset($args['KeepHilite']) ? $args['KeepHilite'] : null, 'POST');
    	$height = FormUtil::getPassedValue('height', isset($args['height']) ? $args['height'] : null, 'POST');
    	$width = FormUtil::getPassedValue('width', isset($args['width']) ? $args['width'] : null, 'POST');
    	$imagedir = FormUtil::getPassedValue('imagedir', isset($args['imagedir']) ? $args['imagedir'] : null, 'POST');

	// Security check
	if (!SecurityUtil::checkPermission( 'iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	$BorderBtwnElmnts = ($BorderBtwnElmnts == 'on') ? 1 : 0;
	$FontBold = ($FontBold == 'on') ? 1 : 0;
	$FontItalic = ($FontItalic == 'on') ? 1 : 0;
	$HideTop = ($HideTop == 'on')? 1 : 0;
	$MenuWrap = ($MenuWrap == 'on') ? 1 : 0;
	$RightToLeft = ($RightToLeft == 'on') ? 1: 0;
	$UnfoldsOnClick = ($UnfoldsOnClick == 'on') ? 1 : 0;
	$WebMasterCheck = ($WebMasterCheck == 'on') ? 1: 0;
	$ShowArrow = ($ShowArrow == 'on') ? 1 : 0;
	$KeepHilite = ($KeepHilite == 'on') ? 1 : 0;

	// Confirm authorisation code
	if (!SecurityUtil::confirmAuthKey()) {
		return LogUtil::registerAuthidError (ModUtil::url('iw_vhmenu', 'admin', 'main'));
	}

	ModUtil::setVar('iw_vhmenu', 'LowBgColor', $LowBgColor);
	ModUtil::setVar('iw_vhmenu', 'LowSubBgColor', $LowSubBgColor);
	ModUtil::setVar('iw_vhmenu', 'HighBgColor', $HighBgColor);
	ModUtil::setVar('iw_vhmenu', 'HighSubBgColor', $HighSubBgColor);
	ModUtil::setVar('iw_vhmenu', 'FontLowColor', $FontLowColor);
	ModUtil::setVar('iw_vhmenu', 'FontSubLowColor', $FontSubLowColor);
	ModUtil::setVar('iw_vhmenu', 'FontHighColor', $FontHighColor);
	ModUtil::setVar('iw_vhmenu', 'FontSubHighColor', $FontSubHighColor);
	ModUtil::setVar('iw_vhmenu', 'BorderColor', $BorderColor);
	ModUtil::setVar('iw_vhmenu', 'BorderSubColor', $BorderSubColor);
	ModUtil::setVar('iw_vhmenu', 'BorderWidth', $BorderWidth);
	ModUtil::setVar('iw_vhmenu', 'BorderBtwnElmnts', $BorderBtwnElmnts);
	ModUtil::setVar('iw_vhmenu', 'FontFamily', $FontFamily);
	ModUtil::setVar('iw_vhmenu', 'FontSize', $FontSize);
	ModUtil::setVar('iw_vhmenu', 'FontBold', $FontBold);
	ModUtil::setVar('iw_vhmenu', 'FontItalic', $FontItalic);
	ModUtil::setVar('iw_vhmenu', 'MenuTextCentered', $MenuTextCentered);
	ModUtil::setVar('iw_vhmenu', 'MenuCentered', $MenuCentered);
	ModUtil::setVar('iw_vhmenu', 'MenuVerticalCentered', $MenuVerticalCentered);
	ModUtil::setVar('iw_vhmenu', 'ChildOverlap', $ChildOverlap);
	ModUtil::setVar('iw_vhmenu', 'ChildVerticalOverlap', $ChildVerticalOverlap);
	ModUtil::setVar('iw_vhmenu', 'StartTop', $StartTop);
	ModUtil::setVar('iw_vhmenu', 'StartLeft', $StartLeft);
	//ModUtil::setVar('iw_vhmenu', 'VerCorrect', $VerCorrect);
	//ModUtil::setVar('iw_vhmenu', 'HorCorrect', $HorCorrect);
	ModUtil::setVar('iw_vhmenu', 'LeftPaddng', $LeftPaddng);
	ModUtil::setVar('iw_vhmenu', 'TopPaddng', $TopPaddng);
	ModUtil::setVar('iw_vhmenu', 'FirstLineHorizontal', $FirstLineHorizontal);
	//ModUtil::setVar('iw_vhmenu', 'MenuFramesVertical', $MenuFramesVertical);
	ModUtil::setVar('iw_vhmenu', 'DissapearDelay', $DissapearDelay);
	//ModUtil::setVar('iw_vhmenu', 'TakeOverBgColor', $TakeOverBgColor);
	//ModUtil::setVar('iw_vhmenu', 'FirstLineFrame', $FirstLineFrame);
	//ModUtil::setVar('iw_vhmenu', 'SecLineFrame', $SecLineFrame);
	//ModUtil::setVar('iw_vhmenu', 'DocTargetFrame', $DocTargetFrame);
	//ModUtil::setVar('iw_vhmenu', 'TargetLoc', $TargetLoc);
	//ModUtil::setVar('iw_vhmenu', 'HideTop', $HideTop);
	//ModUtil::setVar('iw_vhmenu', 'MenuWrap', $MenuWrap);
	ModUtil::setVar('iw_vhmenu', 'RightToLeft', $RightToLeft);
	ModUtil::setVar('iw_vhmenu', 'UnfoldsOnClick', $UnfoldsOnClick);
	//ModUtil::setVar('iw_vhmenu', 'WebMasterCheck', $WebMasterCheck);
	ModUtil::setVar('iw_vhmenu', 'ShowArrow', $ShowArrow);
	ModUtil::setVar('iw_vhmenu', 'KeepHilite', $KeepHilite);
	ModUtil::setVar('iw_vhmenu', 'height', $height);
	ModUtil::setVar('iw_vhmenu', 'width', $width);
	ModUtil::setVar('iw_vhmenu', 'imagedir', $imagedir);

	LogUtil::registerStatus (__('Menu configuration completed successfully', $dom));

	// Reset the users menus for all users
	$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
	ModUtil::func('iw_main', 'user', 'usersVarsDelModule', array('module' => 'iw_vhmenu',
									'name' => 'userMenu',
									'sv' => $sv));

	// Redirect to admin config page
	return System::redirect(ModUtil::url('iw_vhmenu', 'admin', 'conf'));
}

/**
 * Delete a menu item and all the submenus if exists
 * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @param:	Array with the identity of the item that have to be deleted
 * @return:	True if success
*/
function iw_vhmenu_admin_delete($args)
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Get parameters from whatever input we need
    	$mid = FormUtil::getPassedValue('mid', isset($args['mid']) ? $args['mid'] : null, 'REQUEST');
    	$confirmation = FormUtil::getPassedValue('confirmation', isset($args['confirmation']) ? $args['confirmation'] : null, 'POST');
    	$submenusId = FormUtil::getPassedValue('submenusId', isset($args['submenusId']) ? $args['submenusId'] : null, 'POST');

	// Security check
	if (!SecurityUtil::checkPermission( 'iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	// Create output object
	$view = Zikula_View::getInstance('iw_vhmenu',false);

	//Cridem la funciï¿œ de l'API de l'usuari que ens retornarï¿œ la inforamciï¿œ del registre demanat
	$registre = ModUtil::apiFunc('iw_vhmenu', 'admin', 'get', array('mid' => $mid));
	if (!$registre) {
		return LogUtil::registerError (__('Menu option not found', $dom));
	}

	// Ask for confirmation
	if (empty($confirmation)) {
		$view -> assign('text',$registre['text']);
		$view -> assign('mid',$mid);
		$security = SecurityUtil::generateAuthKey();
		$view -> assign('security',$security);
		//get all the submenus that have to be deleted
		$submenusId_array = ModUtil::func('iw_vhmenu', 'admin', 'getsubmenusIds', array('mid' => $mid));
		$submenusId = implode(",", $submenusId_array);
		$view -> assign('submenusId',$submenusId);
		return $view -> fetch('iw_vhmenu_admin_del.htm');
	}	
	
	// User has confirmed the deletion
	// Confirm authorisation code
	if (!SecurityUtil::confirmAuthKey()) {
		return LogUtil::registerAuthidError (ModUtil::url('iw_vhmenu', 'admin', 'main'));
	}

	if (ModUtil::apiFunc('iw_vhmenu', 'admin', 'delete', array('submenusId' => $submenusId))) {
		// The deletion has been successful
		LogUtil::registerStatus (__('The option and its submenus have been deleted', $dom));

		// Reset the users menus for all users
		$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
		ModUtil::func('iw_main', 'user', 'usersVarsDelModule', array('module' => 'iw_vhmenu',
										'name' => 'userMenu',
										'sv' => $sv));
	}

	// Redirect user to admin main page
	return System::redirect(ModUtil::url('iw_vhmenu', 'admin', 'main'));
}

/**
 * Delete a group and subgroup with access to the menu item
 * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @param:	Array with the identity of the item where a group is going to be deleted
 * @return:	True if success
*/
function iw_vhmenu_admin_del_group($args)
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Get parameters from whatever input we need
    	$mid = FormUtil::getPassedValue('mid', isset($args['mid']) ? $args['mid'] : null, 'REQUEST');
    	$confirmation = FormUtil::getPassedValue('confirmation', isset($args['confirmation']) ? $args['confirmation'] : null, 'POST');
    	$group = FormUtil::getPassedValue('group', isset($args['group']) ? $args['group'] : null, 'GET');
    	$groups = FormUtil::getPassedValue('groups', isset($args['groups']) ? $args['groups'] : null, 'POST');

 	// Security check
	if (!SecurityUtil::checkPermission( 'iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	// Create output object
	$view = Zikula_View::getInstance('iw_vhmenu',false);

	// Gets the item information
	$registre = ModUtil::apiFunc('iw_vhmenu', 'admin', 'get', array('mid' => $mid));
	if (!$registre) {
		return LogUtil::registerError (__('Menu option not found', $dom));
	}

	$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
	$grupsInfo = ModUtil::func('iw_main', 'user', 'getAllGroupsInfo', array('sv' => $sv,
																		'less' => ModUtil::getVar('iw_myrole', 'rolegroup')));
	
	// Ask for confirmation
	if (empty($confirmation)) {
		$view -> assign('mid',$mid);
		$security = SecurityUtil::generateAuthKey();
		$view -> assign('security',$security);
		$group_subgroup = explode('|',$group);
		$name_group = ($group_subgroup[0] == '0') ? __('All',$dom) : $grupsInfo[$group_subgroup[0]];
		if($group_subgroup[0] == '-1'){$name_group = __('Unregistered', $dom);}
		$name_subgroup = ($group_subgroup[1] == '0') ? __('All',$dom) : ''; //$dades -> infoSubgrup($group_subgroup[1]);
		$groups = str_replace('$'.$group.'$','',$registre['groups']);
		$group = $name_group;
		if($group_subgroup[1] != '0'){
			$group .= '/'.$name_subgroup;
		}
		$view -> assign('groups', $groups);
		$view -> assign('text', $registre['text']);
		$view -> assign('group', $group);
		return $view -> fetch('iw_vhmenu_admin_del_group.htm');
	}	
		
	// User has confirmed the deletion
	// Confirm authorisation code
	if (!SecurityUtil::confirmAuthKey()) {
		return LogUtil::registerAuthidError (ModUtil::url('iw_vhmenu', 'admin', 'main'));
	}

	// Modify the groups information in database
	if (ModUtil::apiFunc('iw_vhmenu', 'admin', 'modify_grup', array('mid' => $mid,
									'groups' => $groups))) {
        	// L'esborrament ha estat un ï¿œxit i ho notifiquem
		LogUtil::registerStatus (__('The access of the group to the option has been revoked', $dom));

		//Reset the users menus for all users
		$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
		ModUtil::func('iw_main', 'user', 'usersVarsDelModule', array('module' => 'iw_vhmenu',
										'name' => 'userMenu',
										'sv' => $sv));
	}

	// Redirect user to admin main page
	return System::redirect(ModUtil::url('iw_vhmenu', 'admin', 'main'));
}

/**
 * Change the items order
 * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @param:	Array with the identity of the item where the order will be changed
 * @return:	Redirect user to admin main page
*/
function iw_vhmenu_admin_order($args)
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Get parameters from whatever input we need
    	$mid = FormUtil::getPassedValue('mid', isset($args['mid']) ? $args['mid'] : null, 'GET');
    	$id_parent = FormUtil::getPassedValue('id_parent', isset($args['id_parent']) ? $args['id_parent'] : null, 'GET');
    	$puts = FormUtil::getPassedValue('puts', isset($args['puts']) ? $args['puts'] : null, 'GET');

	if (!SecurityUtil::checkPermission( 'iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	// Create output object
	$view = Zikula_View::getInstance('iw_vhmenu',false);
	
	// Security check
	if (!SecurityUtil::checkPermission( 'iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	// change item order
	// Get item information
	$item = ModUtil::apiFunc('iw_vhmenu', 'admin', 'get', array('mid' => $mid));
	if (!$item) {
		return LogUtil::registerError (__('Menu option not found', $dom));
	}

	$iorder = ($puts == '-1') ? $item['iorder'] + 3 : $item['iorder'] - 3;
	ModUtil::apiFunc('iw_vhmenu', 'admin', 'put_order', array('mid' => $mid,
								'iorder' => $iorder));	

	// Reorder the items
	ModUtil::func('iw_vhmenu', 'admin', 'reorder', array('id_parent' => $id_parent));

	// Reset the users menus for all users
	$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
	ModUtil::func('iw_main', 'user', 'usersVarsDelModule', array('module' => 'iw_vhmenu',
									'name' => 'userMenu',
									'sv' => $sv));
	
	// Redirect to admin main page
	return System::redirect(ModUtil::url('iw_vhmenu', 'admin', 'main'));
}

/**
 * Reorder the menu items
 * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @param:	Array with the identity of the item parent of the meu tree
 * @return:	Redirect user to admin main page
*/
function iw_vhmenu_admin_reorder($args)
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Get parameters from whatever input we need
    	$id_parent = FormUtil::getPassedValue('id_parent', isset($args['id_parent']) ? $args['id_parent'] : null, 'GET');
	$objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'GET');
	if (!empty($objectid)) {
		$id_parent = $objectid;
	}

 	// Security check
	if (!SecurityUtil::checkPermission( 'iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	// Create output object
	$view = Zikula_View::getInstance('iw_vhmenu',false);

	// Get item information
	$items = ModUtil::apiFunc('iw_vhmenu', 'admin', 'getall',array('id_parent' => $id_parent,
									'mid'=>$mid));
	if (!$items) {
		return LogUtil::registerError (__('Menu option not found', $dom));
	}
	
	// Reorder all the items with the values 0 2 4 6 8...
	foreach($items as $item){
		$i = $i + 2;	
		ModUtil::apiFunc('iw_vhmenu', 'admin', 'put_order', array('mid' => $item['mid'],
									'iorder' => $i));
	}
	
	//Redirect user to admin main page
	return System::redirect(ModUtil::url('iw_vhmenu', 'admin', 'main'));
}


/**
 * Change position or id_parent of an item
 * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @param:	Array with the identity of the item and his parent
 * @return:	Redirect user to admin main page
*/
function iw_vhmenu_admin_movelevel($args)
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Get parameters from whatever input we need
	$confirmation = FormUtil::getPassedValue('confirmation', isset($args['confirmation']) ? $args['confirmation'] : null, 'POST');
	$mid = FormUtil::getPassedValue('mid', isset($args['mid']) ? $args['mid'] : null, 'REQUEST');
	$upmid = FormUtil::getPassedValue('upmid', isset($args['upmid']) ? $args['upmid'] : null, 'POST');

	if (!SecurityUtil::checkPermission( 'iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	// Create output object
	$view = Zikula_View::getInstance('iw_vhmenu',false);

	// Get item information
	$registre = ModUtil::apiFunc('iw_vhmenu', 'admin', 'get', array('mid' => $mid));
	if (!$registre) {
		return LogUtil::registerError (__('Menu option not found', $dom));
	}

	// Ask confirmation to change the level
	if (empty($confirmation)) {
		//Agafem els nemï¿œs que tenen per id_parent el mateix que el registre que es vol pujar
		$records = ModUtil::apiFunc('iw_vhmenu', 'admin', 'getall', array('id_parent' => '-1'));
		// get all the submenus from the menu
		$submenusId = ModUtil::func('iw_vhmenu', 'admin', 'getsubmenusIds', array('mid' => $mid));

		// add the root in the records array
		$records_array[] = array('mid' => 0, 'text' => __('Root', $dom));
		foreach ($records as $record){
			if(!in_array($record['mid'],$submenusId)){
				$records_array[] = array('mid' => $record['mid'], 'text' => $record['text']);
			}
		}

		$view -> assign('registres', $records_array);
		$view -> assign('text', $registre['text']);
		$view -> assign('mid', $mid);
		$security = SecurityUtil::generateAuthKey();
		$view -> assign('security', $security);
		return $view -> fetch('iw_vhmenu_admin_movelevel.htm');
	}	
		
	// User has confirmed the action
	// Confirm authorisation code
	if (!SecurityUtil::confirmAuthKey()) {
		return LogUtil::registerAuthidError (ModUtil::url('iw_vhmenu', 'admin', 'main'));
	}
	
	// Up the item level
	if (ModUtil::apiFunc('iw_vhmenu', 'admin', 'move_level', array('mid' => $mid,
									'id_parent' => $upmid))) {
		// Update successful
		LogUtil::registerStatus (__('The option has been moved to the parent level', $dom));

		// Reset the users menus for all users
		$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
		ModUtil::func('iw_main', 'user', 'usersVarsDelModule', array('module' => 'iw_vhmenu',
										'name' => 'userMenu',
										'sv' => $sv));
	}

	// Redirect user to admin main page
	return System::redirect(ModUtil::url('iw_vhmenu', 'admin', 'main'));
}

/**
 * Get the submenus of a menu
 * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
 * @param:	Array with the identity of the item and his parent
 * @return:	Return the submenus of a menu
*/
function iw_vhmenu_admin_getsubmenusIds($args)
{
	$dom=ZLanguage::getModuleDomain('iw_vhmenu');
	// Get parameters from whatever input we need
	$mid = FormUtil::getPassedValue('mid', isset($args['mid']) ? $args['mid'] : null, 'POST');
	if (!SecurityUtil::checkPermission( 'iw_vhmenu::', '::', ACCESS_ADMIN)) {
		return LogUtil::registerPermissionError();
	}

	$records_array[] = $mid;

	$records = ModUtil::apiFunc('iw_vhmenu', 'admin', 'getall', array('id_parent' => $mid));

	foreach($records as $record){
		$submenusId = ModUtil::func('iw_vhmenu', 'admin', 'getsubmenusIds', array('mid' => $record['mid']));
		$records_array = array_merge($records_array,$submenusId);
	}

	return $records_array;
}
