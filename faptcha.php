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
	$image = imagecreatefrompng($file);
	if( FALSE == $image )
	{
		error_log('Faptcha serve failed, ' . $file . ' is not a valid PNG!');	// this has happened ... identify such cases via error log
	}
	$image = ImageMangle( $image );
	header('Content-Type: image/png');	
	imagepng($image);
}
else if( ".jpg" == substr( $file, -4) || ".jpeg" == substr( $file, -5) )
{
	$image = imagecreatefromjpeg($file);
	if( FALSE == $image )
	{
		error_log('Faptcha serve failed, ' . $file . ' is not a valid JPEG!');
	}
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

	// Randomly resize it a bit. Mostly an additional defence against a filesize matching attack.
	// 5px seems to be enough to introduce useful variation without messing up the postbox layout or making it too small.
	$resizePix = 0;
	while( 0 == $resizePix )
		$resizePix = rand(-5,5);
	$image = ResizeImage( $image, $width, $height, $width+$resizePix, $height+$resizePix, FALSE );

	return $image;
}


function ResizeImage( $image, $width, $height, $w, $h, $crop=FALSE ) 
{
    $r = $width / $height;
    if ($crop) {
        if ($width > $height) {
            $width = ceil($width-($width*($r-$w/$h)));
        } else {
            $height = ceil($height-($height*($r-$w/$h)));
        }
        $newwidth = $w;
        $newheight = $h;
    } else {
        if ($w/$h > $r) {
            $newwidth = $h*$r;
            $newheight = $h;
        } else {
            $newheight = $w/$r;
            $newwidth = $w;
        }
    }
    $src = $image;
    $dst = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    return $dst;
}

?>


