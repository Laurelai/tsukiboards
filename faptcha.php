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
 * Animu Character Image Captcha, Oneechan edition
 * Inspired by the "Flower Bus Engine" of 410chan.ru, and like theirs adapted from the (crappy) "faptcha" implementation in Serissa
 * Now uses ImageMagick
 * 
 * @package kusaba  
 */
 
session_start();	// We are stateful, to prevent recaptcha style bulk pre-solve attack
//error_reporting(E_ALL);	// DEBUG TEMP!

$dir = 'faptchas' . '/';	// base images go in here
$dh  = opendir($dir);
while (false !== ($filename = readdir($dh))) 
{
	if( (".png" == strtolower(substr( $filename, -4))) 
		|| (".jpg" == strtolower(substr( $filename, -4)))
		|| (".jpeg" == strtolower(substr( $filename, -5))) )  // We only handle .png and .jpg (so far)
	{
	   	$files[] = $filename;
	}
}	
closedir($dh);

$NumFiles = count($files);
if( $NumFiles <= 0 )
{
	die("No faptcha images found! Please place them in faptchas/");
}

//srand((double)microtime()*1000000);	// RH - not necessary on PHP >=4.2.0, and it's bad for randomness to reseed
$randnum = rand(0,$NumFiles - 1);
$file = $dir . $files[$randnum];	
$filename = $files[$randnum];


// Tokenise filename into an array of acceptable answer words, set it as a session variable to check against user input later
$filename2 = strtolower($filename);
$trailingNumPattern = '/\s_*[0-9]+\.(jpg|jpeg|png)$/';			// trailing numbers: zero or more of any char (e.g. _) followed by one or more integers followed by the extension / EOL
$filename2 = preg_replace( $trailingNumPattern, '', $filename2 );	// replace with nothing, i.e. ignore them
$filename2 = preg_replace('/.(jpg|jpeg|png)$/', '', $filename2 );	// remove extensions not caught by the above (unnumbered files)
$filename2 = preg_replace( '/\s/', ' ', $filename2 );			// ensure whitespace delimiters are a single space
$words = explode(" ", $filename2, 8);	// max 8 "possible answer" words (sensible limit? Could probably be less)
$_SESSION['faptcha_answers'] = $words;	// assign them to session variable

// Serve the image
if( ".png" == substr( $file, -4) )
{
	$image = ImageMangle( $file );
	header('Content-Type: image/png');	
	echo $image;
}
else if( ".jpg" == substr( $file, -4) || ".jpeg" == substr( $file, -5) )
{
	$image = ImageMangle( $file );
	header('Content-Type: image/jpeg');	
	echo $image;
}

function ImageMangle( $file )
{
	// Image mangling. This prevents trivial database hash matching, and is supposed to also defend against image recognition services.
	// Unfortunately it turns out that some of these are quite good ... the below is enough to defeat them most of the time.
	// TODO: Some sort of mild warping? Perspective deformation kind of works ...
	$image = new Imagick($file);
	$width = $image->getImageWidth();
	$height = $image->getImageHeight();
	
	// Randomly rotate 8 - 40 degrees one way or the other, random background colour.
	$bg = new ImagickPixel();
	$bg->setColor( GetRandomColour() );
	$rotate = 0;
	$rotate = mt_rand(-40,40);
	if( $rotate >=0 && $rotate < 7 )
		$rotate += 8;
	else if( $rotate <= 0 && $rotate >-7 )
		$rotate -= 8;
	$image->rotateImage( $bg, $rotate ); 

	// Draw 2 random lines as before, thickness also randomised a bit now
	$draw = new ImagickDraw();
	$draw->setStrokeColor( GetRandomColour() );
	$draw->setStrokeWidth( mt_rand(1,3) );
	$draw->line( mt_rand(0, $width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height) );
	$draw->setStrokeColor( GetRandomColour() );
	$draw->setStrokeWidth( mt_rand(1,3) );
	$draw->line( mt_rand(0, $width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height) );
	$image->drawImage( $draw );

	// Crop it a bit
	$image->cropImage( $image->getImageWidth() - 10, $image->getImageHeight() - 10, 5, 5 );

	// Shrink if neccessary
	while( $image->getImageWidth() > 110 )
	{
		$newWidth = $image->getImageWidth() * 0.9;
		$newHeight = $image->getImageHeight() * 0.9;
		// High quality resize, bigger pics go blurry if we use scaleImage
		$image->resizeImage( $newWidth, $newHeight, Imagick::FILTER_LANCZOS, 1 );
	}

	// Horizontal flip
	$image->flopImage();

	return $image;
}

function GetRandomColour()
{
	// Returns a random colour in HTML triplet form, e.g. "#cc00cc"
	$c = "";
	for ($i = 0; $i<6; $i++)
	{
		$c .=  dechex(rand(0,15));
	}
	return "#$c";
} 

?>



