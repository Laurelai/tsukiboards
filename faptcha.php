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

//global $dir;
//global $files;
//global $NumFiles;
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
	// Unfortunately it turns out that some of these are quite good ... the below seems to be enough to defeat them.
	// TODO: Some sort of mild warping? Perspective deformation kind of works ...
	$image = new Imagick($file);
	$width = $image->getImageWidth();
	$height = $image->getImageHeight();
	
	// Randomly rotate 10 - 35 degrees one way or the other
	$rotate = 0;
	$rotate = mt_rand(-35,35);
	if( $rotate >=0 && $rotate < 9 )
		$rotate += 10;
	else if( $rotate <= 0 && $rotate >-9 )
		$rotate -= 10;
	// $image->rotateImage( $bg, $rotate );
	$image->rotateImage( new ImagickPixel('none'), $rotate );   // transparent bg

	// Draw 2 random lines as before, thickness also randomised a bit now
	// Disabled for now, somewhat ugly and I don't think it meaningfully improves security any more?
	/*
	$draw = new ImagickDraw();
	$draw->setStrokeColor( GetRandomColour() );
	$draw->setStrokeWidth( mt_rand(1,3) );
	$draw->line( mt_rand(0, $width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height) );
	$draw->setStrokeColor( GetRandomColour() );
	$draw->setStrokeWidth( mt_rand(1,3) );
	$draw->line( mt_rand(0, $width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height) );
	$image->drawImage( $draw );
	*/


	// Image composition: get a new random faptcha as the background, paste our rotated faptcha on the top.
	// Idea behind this is to make edge detection harder.
	global $NumFiles, $dir, $files;		// Get a new faptcha to use as the BG

	// Temporary hack to avoid using PNG for background. This is because it might be transparent.
	// Real fix is to composite on top of a BG colour but that doesn't work yet, see below ...
	$done = false;
	while( ! $done )
	{
		$randnum = rand(0, $NumFiles - 1);
		$bgFaptchaFile = $dir . $files[$randnum];
		if( pathinfo($bgFaptchaFile, PATHINFO_EXTENSION) != "png" )
			$done = true;
	}
	$bgFaptcha = new Imagick( $bgFaptchaFile );
	// Set bg colourspace to the same as the foreground faptcha
	$bgFaptcha->setImageColorspace($image->getImageColorspace() );
	// BG must also be the same size, or excessive cropping can happen
	$bgFaptcha->scaleImage( $image->getImageWidth(), $image->getImageHeight() );

	// Cheap background permutation, 50% chance of flipping or flopping
	if( mt_rand( 0, 1) )
	{
		$bgFaptcha->flopImage();
	}
	if( mt_rand( 0, 1) )
	{
		$bgFaptcha->flipImage();
	}
/*	// TODO: improve case where BG image or both have a transparent background (alpha). Below silently doesn't work for some reason.
	$backgroundColour = new Imagick();
	$bg = new ImagickPixel();
	$bg->setColor( GetRandomColour() );
	$backgroundColour->newImage( $image->getImageWidth(), $image->getImageHeight(), $bg );
	$backgroundColour->compositeImage( $bgFaptcha, $bgFaptcha->getImageCompose(), 0, 0 );
	$bgFaptcha = $backgroundColour;
*/
	// Faptcha is put on top of the BG one
	$bgFaptcha->compositeImage($image, $image->getImageCompose(), 0, 0);
	// Assign back to main faptcha image
	$image = $bgFaptcha;


	// Crop it a bit
	$image->cropImage( $image->getImageWidth() - 5, $image->getImageHeight() - 5, 5, 5 );

	// Shrink further if neccessary
	while( $image->getImageWidth() > 100 )
	{
		$newWidth = $image->getImageWidth() * 0.95;
		$newHeight = $image->getImageHeight() * 0.95;
		// High quality resize, bigger pics go blurry if we use scaleImage (was LANCOZ, this should be faster)
		$image->resizeImage( $newWidth, $newHeight, Imagick::FILTER_CATROM, 1 );
	}

	// Horizontal flip
	$image->flopImage();

	// Apply some mild perspective distortion
	// This one makes the image recede to the right ...
	if( mt_rand( 0, 1) )
	{
		$controlPoints = array( 10, 10,
					10, 5,
	 
					10, $image->getImageHeight() - 20,
					10, $image->getImageHeight() - 15,
	 
					$image->getImageWidth() - 10, 10,
					$image->getImageWidth() - 10, 15,
	 
					$image->getImageWidth() - 10, $image->getImageHeight() - 10,
					$image->getImageWidth() - 10, $image->getImageHeight() - 15);
	}
	else	// and this one to the left
	{
		$controlPoints = array( 10, 5,
					10, 10,
 
					10, $image->getImageHeight() - 15,
					10, $image->getImageHeight() - 20,

					$image->getImageWidth() - 10, 15, 
					$image->getImageWidth() - 10, 10,
	 
					$image->getImageWidth() - 10, $image->getImageHeight() - 15,
					$image->getImageWidth() - 10, $image->getImageHeight() - 10 );
	}
	$image->distortImage( Imagick::DISTORTION_PERSPECTIVE, $controlPoints, true );

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



