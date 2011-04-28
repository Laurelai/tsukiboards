<?php
/*
 * This file is part of kusaba.
 *
 * kusaba is free software; you can redistribute it and/or modify it under the
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
 */
/**
 * Post management page. Currently only post editing is implemented here.
 *
 * @package kusaba
 */

require 'config.php';
require KU_ROOTDIR . 'lib/dwoo.php';
require KU_ROOTDIR . 'inc/functions.php';
require KU_ROOTDIR . 'inc/classes/postedit.class.php';
require KU_ROOTDIR . 'inc/classes/board-post.class.php';
require KU_ROOTDIR . 'inc/classes/bans.class.php';

$dwoo_data->assign('styles', explode(':', KU_MENUSTYLES));


$postedit_class = new PostEdit();

/* Decide what needs to be done */
$action = $_REQUEST['action'];
switch ($action) {
	default:
		/* Halts execution if not validated */
		manage_page($action);
		break;
}

/* Show a particular manage function */
function manage_page($action) {
	global $postedit_class, $tpl_page;

	$postedit_class->Header();

	if (is_callable(array($postedit_class, $action))) {
		$postedit_class->$action();
	} else {
		$tpl_page .= sprintf(_gettext('%s not implemented.'), $action);
	}

	$postedit_class->Footer();
}

?>
