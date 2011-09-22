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
 * Captcha display
 *
 * Generates a random word and stores it in a session variable, which is later used as a verification that the poster is in fact a human
 *
 * @package kusaba
 */

/**
 * Start the session
 */
session_start();

$preconfig_db_unnecessary = true;

/**
 * Require the configuration file
 */
require 'config.php';

/*
 * File: CaptchaSecurityImages.php
 * Author: Simon Jarvis
 * Copyright: 2006 Simon Jarvis
 * Date: 03/08/06
 * Updated: 07/02/07
 * Requirements: PHP 4/5 with GD and FreeType libraries
 * Link: http://www.white-hat-web-design.co.uk/articles/php-captcha.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details:
 * http://www.gnu.org/licenses/gpl.html
 *
 */

/**
 * Captcha image class
 *
 * @package kusaba
 */
class CaptchaSecurityImages {

    function CaptchaSecurityImages($width='130',$height='42',$characters='7',$font) {
        global $font,$font_ballback;

        require_once KU_ROOTDIR . 'inc/classes/randword.class.php';
        $randword_class = new Rand_Word;

        $code = $randword_class->rand_word($characters);

        $font_size = $height * 0.85;
        $image = @imagecreate($width, $height) or die('Cannot initialize new GD image stream');
        $image2 = @imagecreate($width, $height) or die('Cannot initialize new GD image stream');
        $background_color = imagecolorallocate($image, 255, 255, 255);
        $background_color2 = imagecolorallocate($image2, 255, 255, 255);
        $text_color = imagecolorallocate($image, 35, 45, 100);
        $text_color2 = imagecolorallocate($image2, 35, 45, 100);
        imagecolortransparent($image2, $background_color2);
        imagecolortransparent($image, $background_color);
        $noise_color = imagecolorallocate($image,  mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
            for( $i=0; $i<($width*$height)/9; $i++ ) {
                imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $noise_color);
            }
            $noise_color = imagecolorallocate($image,  mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
            for( $i=0; $i<($width*$height)/9; $i++ ) {
                imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $noise_color);
            }
            $noise_color = imagecolorallocate($image,  mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
            for( $i=0; $i<($width*$height)/9; $i++ ) {
                imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $noise_color);
            }
        $textbox = imagettfbbox($font_size, 0, $font, $code) or die('Error in imagettfbbox function');
        $x = ($width - $textbox[4])/2;
        $y = ($height - $textbox[5])/2;
        imagettftext($image2, $font_size, 0, $x, $y, $text_color2, $font, $code) or die('Error in imagettftext function');

        imagecopy($image2, $image, 0, 0, 0, 0, $width, $height);
        header('Content-Type: image/jpeg');
        imagejpeg($image2);
        imagedestroy($image);
        imagedestroy($image2);
        $_SESSION['security_code'] = $code;
    }

}

$width = 100;
$height = 31;
$characters = 7;

$font = KU_ROOTDIR . 'lib/fonts/monofont.ttf';
$font_fallback = imageloadfont(KU_ROOTDIR . 'lib/fonts/captchafont.gdf');

$captcha = new CaptchaSecurityImages($width,$height,$characters,$font);

?>
