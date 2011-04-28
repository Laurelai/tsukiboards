<?php
/*
	Robot 9000 implementation for Kusaba X
	Implementation is based on https://github.com/savetheinternet/Tinyboard/
*/

class Robot {

	var $boardId;

	function __construct($boardId) {
		$this->boardId = $boardId;
	}
	
	function CheckRobot($body) {
		global $tc_db;
	
		$stripped = $this->Strip($body);
		
		if (strlen($stripped) < ROBOT_MIN_POST_CHARS) {
			exitWithErrorPage('Minimum post length is ' . ROBOT_MIN_POST_CHARS . ' characters');
		}
		
		$hash = sha1($stripped);
		
		$unoriginal = $tc_db->GetOne("SELECT 1 FROM `".KU_DBPREFIX."robot` WHERE `board_id` = ".$tc_db->qstr($this->boardId)." AND `hash` = ".$tc_db->qstr($hash)." LIMIT 1");
		
		if ($unoriginal) {
			return true;
		} else {
		
			$tc_db->Execute("INSERT INTO `".KU_DBPREFIX."robot` (`board_id`, `hash`) VALUES ( " . $tc_db->qstr($this->boardId) . ", " . $tc_db->qstr($hash) . ")");
			return false;
		
		}
	
	}
	
	function IsEnabled($board_name) {
	
		return (ROBOT_ENABLE && $board_name == ROBOT_BOARD);
	
	}
	
	function Strip($body) {
	
		$body = strtolower($body);

		if (ROBOT_STRIP_HTML)
			$body = strip_tags($body);
			
		if (ROBOT_STRIP_QUOTES)
			$body = preg_replace('/^\s*(&gt;&gt;|&gt;)/', '', $body);		// Strips away > and >>
		
		// Leave only letters
		$body = preg_replace('/[^a-z]/i', '', $body);
		
		// Remove repeating characters
		if(ROBOT_STRIP_REPEATING)
			$body = preg_replace('/(.)\\1+/', '$1', $body);

		return $body;
	
	}

	// Calculates effective mute time for the current user
	function MuteTime() {
		global $tc_db;
	
		$startTime = time()-(ROBOT_MUTE_HOUR*3600);
		$ip = $_SERVER['REMOTE_ADDR'];
		
		// Find number of mutes in the past X hours
		$query = "SELECT COUNT(*) as `count` FROM `".KU_DBPREFIX."mutes` WHERE `board_id` = ". $tc_db->qstr($this->boardId) . " AND `time` >= ". $startTime ." AND `ip` = ". $tc_db->qstr($ip);
		$count = $tc_db->GetOne($query);

		if($count == 0) return 0;
		return pow(ROBOT_MUTE_MULTIPLIER, $count);
	}

	// Inserts a new mute for the current user
	function Mute() {
		global $tc_db;
	
		// Insert mute
		$ip = $_SERVER['REMOTE_ADDR'];
		
		$tc_db->Execute("INSERT INTO `".KU_DBPREFIX."mutes` (`board_id`,`ip`,`time`) VALUES (" . $tc_db->qstr($this->boardId) . ", " . $tc_db->qstr($ip) . ", " . time() . ")");

		return $this->MuteTime();
	}

		
	function CheckMute() {
		global $tc_db;
		
		$muteTime = $this->MuteTime();
		if($muteTime > 0) {
		
			$ip = $_SERVER['REMOTE_ADDR'];
		
			// Find last mute time
			$lastMute = $tc_db->GetOne("SELECT `time` FROM `".KU_DBPREFIX."mutes` WHERE `board_id` = " . $tc_db->qstr($this->boardId) . " AND `ip` = " . $tc_db->qstr($ip) . " ORDER BY `time` DESC LIMIT 1");

			if($lastMute + $muteTime > time()) {
				// Not expired yet
				$left = $lastMute + $muteTime - time();
				exitWithErrorPage('You are still muted for ' . $left . ' seconds');
			} else {
				// Already expired	
				return;
			}
		}
	}
}

?>