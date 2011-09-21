<?php
/** 
 * Animu Character Image Captcha, Oneechan edition
 * Inspired by the "Flower Bus Engine" of 410chan.ru, and like theirs adapted from the (crappy) "faptcha" implementation in Serissa
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
	die("No faptcha images found! Please place them in faptchas/");  // at least 2 are needed for it to work, actually ...
}

srand((double)microtime()*1000000);
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
	$image = imagecreatefrompng($file);
	$image = ImageMangle( $image );
	header('Content-Type: image/png');	
	imagepng($image);
}
else if( ".jpg" == substr( $file, -4) || ".jpeg" == substr( $file, -5) )
{
	$image = imagecreatefromjpeg($file);
	$image = ImageMangle( $image );
	header('Content-Type: image/jpeg');	
	imagejpeg($image);
}

function ImageMangle( $image )
{
	// Image mangling, to prevent a trivial hash matching database attack.
	// For now we just draw a couple of random coloured lines on it (an improved version of what 410chan do.)
	// TODO: Not as strong as I'd like. Perspective deformation etcetera via ImageMagick?
	$width = imagesx($image);
	$height = imagesy($image);
	$noise_color = imagecolorallocate($image, rand(0,255), rand(0,255), rand(0,255));
	imageline($image, mt_rand(0,$width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height), $noise_color);
	$noise_color = imagecolorallocate($image, rand(0,255), rand(0,255), rand(0,255));
	imageline($image, mt_rand(0,$width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height), $noise_color);

	return $image;
}

?>



