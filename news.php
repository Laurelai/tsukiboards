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
 
/**
 * News display, which is the first page shown when a user visits a chan's index
 *
 * Any news added by an administrator in the manage panel will show here, with
 * the newest entry on the top.
 *
 * @package kusaba
 */

// Require the configuration file
require 'config.php';
require KU_ROOTDIR . 'inc/functions.php';
require_once KU_ROOTDIR . 'lib/dwoo.php';
global $CURRENTLOCALE;
$dwoo_tpl = new Dwoo_Template_File(KU_TEMPLATEDIR . '/news.tpl');

$topads = $tc_db->GetOne("SELECT code FROM `" . KU_DBPREFIX . "ads` WHERE `position` = 'top' AND `disp` = '1'");
$botads = $tc_db->GetOne("SELECT code FROM `" . KU_DBPREFIX . "ads` WHERE `position` = 'bot' AND `disp` = '1'");
$dwoo_data->assign('topads', $topads);
$dwoo_data->assign('botads', $botads);


if (!isset($_GET['p'])) $_GET['p'] = '';

if ($_GET['p'] == 'faq') {
	$entries = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "front` WHERE `page` = 1 ORDER BY `order` ASC");
} elseif ($_GET['p'] == 'rules') {
	$entries = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "front` WHERE `page` = 2 ORDER BY `order` ASC");
} elseif ($_GET['p'] == 'news') {
	$entries = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "front` WHERE `page` = 0 ORDER BY `timestamp` DESC");
} else {
	$limitNews = 10;
	$entries = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "front` WHERE `page` = 0 ORDER BY `timestamp` DESC LIMIT $limitNews");
}

$limitposts = 5;
$recentPosts = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "posts` WHERE `IS_DELETED` = 0 AND NOT `email` = 'sage' ORDER BY `timestamp` DESC LIMIT $limitposts");

foreach ($recentPosts as $k=>$post) {
	$board = $tc_db->GetAll("SELECT `name`, `anonymous` FROM `".KU_DBPREFIX."boards` WHERE `id` = '".$post['boardid']."'");
	$board = $board[0];
	$dateEmail = (empty($board['anonymous'])) ? $post['email'] : 0;
	$post['message'] = stripslashes(formatLongMessage($post['message'], $board['name'], (($post['parentid'] == 0) ? ($post['id']) : ($post['parentid'])), true, 7));
	$post['timestamp_formatted'] = formatDate($post['timestamp'], 'post', $CURRENTLOCALE, $dateEmail);
	$threadId = (($post['parentid'] == 0) ? ($post['id']) : ($post['parentid']));
	$post['reflink'] = formatReflink($board['name'], $threadId, $post['id'], $CURRENTLOCALE);
	$post['refUrl'] = getPostUrl($board['name'], $threadId, $post['id']);
	$post['boardname'] = $board['name'];
	$post['boardUrl'] = KU_BOARDSFOLDER . $board['name'] . '/';
	$recentPosts[$k] = $post;
}

$styles = explode(':', KU_MENUSTYLES);

$dwoo_data->assign('styles', $styles);
$dwoo_data->assign('ku_webpath', getCWebPath());
$dwoo_data->assign('entries', $entries);

if ($_GET['p'] == '') {
	$dwoo_data->assign('recentPosts', $recentPosts);
}

$dwoo->output($dwoo_tpl, $dwoo_data);
?>