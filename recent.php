<?php
require_once 'config.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Recent Posts on Oneechan</title>
	<link rel="shortcut icon" href="<?php echo KU_WEBPATH; ?>/favicon.ico" />
</head>
<?php
include KU_ROOTDIR . 'inc/func/stringformatting.php';

global $tc_db;
$limitposts = 20;

$results = $tc_db->GetAll("SELECT * FROM `".KU_DBPREFIX."posts` WHERE `IS_DELETED` = 0 ORDER BY `timestamp` DESC LIMIT $limitposts") or die("invalid query");

if (count($results) > 0) {
	$tpl_page .= '<table border="1" width="100%">'. "\n";
	$tpl_page .= '<tr><th width="75px">'._gettext('Post Number').'</th><th>'._gettext('Post Message').'</th></tr>'. "\n";
	foreach ($results as $result) {
		$real_parentid = ($result['parentid'] == 0) ? $result['id'] : $result['parentid'];
		$result['boardname'] = $tc_db->GetOne("SELECT `name` FROM `".KU_DBPREFIX."boards` WHERE `id` = '".$result['boardid']."'");
		$result['timestamp_formatted'] = formatDate($result['timestamp']);
		
		$tpl_page .= '<tr><td><a href="'. KU_BOARDSPATH . '/'. $result['boardname'] . '/res/'. $real_parentid . '.html#'. $result['id'] . '">/'. $result['boardname'] . '/'. $result['id'] . '</a></td>
		<td>By ' . formatDisplayName($result['name'], $result['tripcode']) . ' - ' . $result['timestamp_formatted'] . '<br />' .
		stripslashes($result['message']) . '</td></tr>';
	}
	$tpl_page .= '</table>';
}
echo $tpl_page;

?>