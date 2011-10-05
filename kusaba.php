<?php
/*
 * This file is part of arcNET
 *
 * arcNET uses core code from Kusaba X and Oneechan
 *
 * tsukihi.me kusabax.cultnet.net oneechan.org
 *
 * arcNET is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * kusaba is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * kusaba; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * credits to jmyeom for improving this
 *
 */
if (file_exists("install.php")) {
	die('You are seeing this message because either you haven\'t ran the install file yet, and can do so <a href="install.php">here</a>, or already have, and <strong>must delete it</strong>.');
}
if (!isset($_GET['info'])) {
	$preconfig_db_unnecessary = true;
}
require 'config.php';
$menufile = (KU_STATICMENU) ? 'menu.html' : 'menu.php';
$menusize = (KU_MENUTYPE == 'normal') ? '15%' : '10%';
$mainsize = 100-$menusize . '%';
header("Expires: Mon, 1 Jan 2030 05:00:00 GMT");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php echo KU_NAME; ?></title>
	<link rel="shortcut icon" href="<?php echo KU_WEBPATH; ?>/favicon.ico" />
	<style type="text/css">
		body, html {
			width: 100%;
			height: 100%;
			margin: 0;
			padding: 0;
			overflow: auto;
		}
		#menu {
			position: absolute;
			left: 0px;
			top: 0px;
			margin: 0;
			padding: 0;
			border: 0px;
			height: 100%;
			width: <?php echo $menusize; ?>;
		}
		#main {
			position: absolute;
			left: <?php echo $menusize; ?>;
			top: 0px;
			border: 0px;
			height: 100%;
			width: <?php echo $mainsize; ?>;
		}
	</style>
</head>
<?php
if (isset($_GET['info'])) {
	require KU_ROOTDIR . 'inc/functions.php';
	echo '<body>';
	echo '<h1>General info:</h1><ul>';
	echo '<li>Version: kusaba x ' . KU_VERSION . '</li>';
	$bans = $tc_db->GetOne("SELECT COUNT(*) FROM `".KU_DBPREFIX."banlist`");
	echo '<li>Active bans: ' . $bans . '</li>';
	$wordfilters = $tc_db->GetOne("SELECT COUNT(*) FROM `".KU_DBPREFIX."wordfilter`");
	echo '<li>Wordfilters: ' . $wordfilters . '</li>';
	echo '<li>Modules loaded: ';
	$modules = modules_list();
	if (count($modules) > 0) {
		$moduleslist = '';
		foreach ($modules as $module) {
			$moduleslist .= $module . ', ';
		}
		echo substr($moduleslist, 0, -2);
	} else {
		echo 'none';
	}
	echo '</li>';
	echo '</ul>';
	echo '</body></html>';
	die();
}
?>
<body>
	<iframe src="<?php echo $menufile; ?>" name="menu" id="menu">
		<a href="<?php echo KU_WEBPATH . '/' . $menufile; ?>"><?php echo KU_NAME; ?></a>
	</iframe>
	<iframe src="news.php" name="main" id="main">
		<a href="<?php echo KU_WEBPATH;?>/news.php"><?php echo KU_NAME; ?> Navigation</a>
	</iframe>
</body>
</html>