<?php
/**
 * RH - Handle captcha.php requests appropriately depending on the CAPTCHA type we are using.
 * Only reason we need this is for compatibility with dollchan extension tools.
 * In future, if/when we add native thread watching and updating, we can probably drop this.
 *
 * @package kusaba
 *
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
 */

require 'config.php';

if( KU_CAPTCHA_TYPE == 'faptcha' )
{
	include 'faptcha.php';
}
else if( KU_CAPTCHA_TYPE == 'recaptcha' )
{
	// RH - use reCAPTCHA challenge instead of the faptcha.
	require_once(KU_ROOTDIR.'recaptchalib.php');
	$publickey = "6LdVg8YSAAAAAOhqx0eFT1Pi49fOavnYgy7e-lTO";
	echo recaptcha_get_html($publickey);
}
else
{
	die("Unrecognised CAPTCHA type set in config.php!");
}
?>

