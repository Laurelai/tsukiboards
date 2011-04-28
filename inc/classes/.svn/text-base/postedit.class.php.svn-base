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
 * +------------------------------------------------------------------------------+
 * PostEdit Class
 * +------------------------------------------------------------------------------+
 * Post editing functions, along with the pages available
 * +------------------------------------------------------------------------------+
 */
 
 
require KU_ROOTDIR . 'inc/classes/posting.class.php';
require KU_ROOTDIR . 'inc/classes/parse.class.php';

 
class PostEdit {

	/* Show the header of the manage page */
	function Header() {
		global $dwoo_data, $tpl_page;

		if (is_file(KU_ROOTDIR . 'inc/pages/modheader.html')) {
			$tpl_includeheader = file_get_contents(KU_ROOTDIR . 'inc/pages/modheader.html');
		} else {
			$tpl_includeheader = '';
		}

		$dwoo_data->assign('includeheader', $tpl_includeheader);
	}

	/* Show the footer of the manage page */
	function Footer() {
		global $dwoo_data, $dwoo, $tpl_page;

		$dwoo_data->assign('page', $tpl_page);

		$board_class = new Board('');

		$dwoo->output(KU_TEMPLATEDIR . '/postedit.tpl', $dwoo_data);
	}
			
	// Printets the password query dialog
	private function PasswordQuery() {
		global $tc_db, $tpl_page;
	
		$tpl_page .= '<form action="" method="post">
		Password:<br /><input type="password" name="password" /><br />
		<input type="submit" name="edit" value="Continue" /></form>';
	
	}
	
	// Checks that the user has access to post edit page
	private function CheckAccess($board_class) {
	
		$bans_class = new Bans();
		$bans_class->BanCheck($_SERVER['REMOTE_ADDR'], $board_class->board['name']);
	
	}
	
	// Checks that the entered password matched the one in the post
	private function CheckPass($board_class) {
		global $tc_db, $tpl_page;
	
		$editpostid = isset($_GET['editpostid']) ? $_GET['editpostid'] : '';
		$board_id = $board_class->board['id'];
	
		$password = md5($_POST['password']);	
		$postPass = $tc_db->GetOne("SELECT `password` FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $board_id . " AND `id` = " . $tc_db->qstr($editpostid) . " ");
		
		if (!isset($password) || $password != $postPass) {	
			$tpl_page .= _gettext('Incorrect password.') . ' <br /><hr />';			
			$this->PasswordQuery();
			return false;			
		}
		
		return true;
	
	}
	
	// Prints the form for post editing
	private function EditPostPrompt($board_class) {
		global $tc_db, $tpl_page;
	
		if (!$this->CheckPass($board_class))
			return;
	
		$editpostid = isset($_GET['editpostid']) ? $_GET['editpostid'] : '';
		$board_id = $board_class->board['id'];
				
		$results = $tc_db->GetAll("SELECT `parentid`,`message`,`subject` FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $board_id . " AND `id` = " . $tc_db->qstr($editpostid) . " ");
		
		foreach ($results as $line) {
			$parentid = $line['parentid'];
			$message = strip_tags(stripslashes($line['message']));
			$subject = $line['subject'];
		}
		if($parentid == 0) { $parentid = $editpostid; }
		
		$tpl_page .= '<form action="" method="post">
		<input type="hidden" name="password" value="'.$_POST['password'].'" />
		Subject:<br /><input type="text" name="subject" value="'.$subject.'" /><br />
		Message:<br /><textarea cols="80" rows="15" name="message">'.$message.'</textarea>
		<input type="hidden" name="replythread" value="'.$parentid.'" /><br />
		<input type="submit" name="edit" value="Edit" /></form>';
			
	}
	
	// Updates the post.
	private function UpdatePost() {
		global $tc_db, $tpl_page, $board_class;
	
		if (!$this->CheckPass($board_class))
			return;
	
		$editpostid = isset($_GET['editpostid']) ? $_GET['editpostid'] : '';
		$board_id = $board_class->board['id'];
	
		$posting_class = new Posting();
		$posting_class->UTF8Strings();
	
		$tc_db->Execute("START TRANSACTION");
		$posting_class->CheckReplyTime();
		$posting_class->CheckNewThreadTime();
		$posting_class->CheckMessageLength();
		//$posting_class->CheckCaptcha();		// CAPTCHA not supported yet
		//$posting_class->CheckBannedHash();	// Cannot upload images while editing
		$posting_class->CheckBlacklistedText();
		$post_isreply = $posting_class->CheckIsReply();

		if ($post_isreply) {
			list($thread_replies, $thread_locked, $thread_replyto) = $posting_class->GetThreadInfo($_POST['replythread']);
		} else {
			// Note: uploading files when editing a post is not supported
			$thread_replies = 0;
			$thread_locked = 0;
			$thread_replyto = 0;
		}
				
		$post_subject = isset($_POST['subject']) ? htmlspecialchars($_POST['subject'], ENT_QUOTES) : '';
		
		if ($board_class->board['type'] == 1) {
			if ($post_isreply) {
				$post_subject = '';
			} else {
				$posting_class->CheckNotDuplicateSubject($post_subject);
			}
		}
	
		if ($thread_locked == 1) {
			// Don't let the user post
			exitWithErrorPage(_gettext('Sorry, this thread is locked and can not be replied to.'));
		}

		$parse_class = new Parse();
		$parse_class->id = $post_id;
		
		$post_message = $parse_class->ParsePost($_POST['message'], $board_class->board['name'], $board_class->board['type'], $thread_replyto, $board_class->board['id']);
		
		
		$posting_class->CheckBadUnicode('', '', $post_subject, $post_message);
				
		if ($board_class->board['type'] == 1 && !$post_isreply && $post_subject == '') {
			exitWithErrorPage('A subject is required to make a new thread.');
		}

		if ($board_class->board['locked'] == 0) {
		
			$post = array();
			$post['board'] = $board_class->board['name'];
			$post['subject'] = substr($post_subject, 0, 74);
			$post['message'] = $post_message;

			$post = hook_process('posting', $post);

			if ($thread_replyto != '0') {
				if ($post['message'] == '' && KU_NOMESSAGEREPLY != '') {
					$post['message'] = KU_NOMESSAGEREPLY;
				}
			} else {
				if ($post['message'] == '' && KU_NOMESSAGETHREAD != '') {
					$post['message'] = KU_NOMESSAGETHREAD;
				}
			}
			
			// Do update
			$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `message` = ".$tc_db->qstr(addslashes($post_message)).", `subject` = ".$tc_db->qstr($post_subject)."  WHERE `boardid` = ".$board_id." AND `id` = ".$tc_db->qstr($editpostid)." ");
		
			$tc_db->Execute("COMMIT");
		
			$board_class->RegeneratePages();

			if ($thread_replyto == '0') {
				// Regenerate the thread
				$board_class->RegenerateThreads($editpostid);
			} else {
				// Regenerate the thread
				$board_class->RegenerateThreads($thread_replyto);
			}
			
			$tpl_page .= _gettext('Edit successful.') . ' <br /><hr />';
	
		} else {
			exitWithErrorPage(_gettext('Sorry, this board is locked and can not be posted in.'));		
		}
	
	}

	function editpost() {
		global $tc_db, $tpl_page, $board_class;

		$boardName = isset($_GET['boarddir']) ? $_GET['boarddir'] : '';		
		$board_class = new Board($boardName);		
		$editpostid = isset($_GET['editpostid']) ? $_GET['editpostid'] : '';
		$board_id = $board_class->board['id'];
		
		$parentid = $tc_db->GetOne("SELECT `parentid` FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $board_id . " AND `id` = " . $tc_db->qstr($editpostid) . " ");		
		if($parentid == 0) { $parentid = $editpostid; }
	
		$tpl_page .= '<h2>'. _gettext('Editing post number') . ' ' . $editpostid . '</h2><br />';
	
		if (!$_POST['password']) {	
			$this->PasswordQuery();
		} else {
						
			// Start the session
			session_start();

			// {{{ Module loading
			modules_load_all();
			
			if (!empty($board_class->board['locale'])) {
				changeLocale($board_class->board['locale']);
			}
		
			$this->CheckAccess($board_class);
			
			if (isset($_POST['message'])) {
				$this->UpdatePost();
			} else {
				$this->EditPostPrompt($board_class);
			}
					
		}	
			
		$tpl_page .= '<br /><a href="'.KU_BOARDSFOLDER.$board_class->board['name'] . '/res/' . $parentid . '.html">Back to thread</a>';
			
		unset($board_class);
			
	}
		
}

?>