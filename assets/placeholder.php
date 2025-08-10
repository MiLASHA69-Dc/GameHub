<?php
// Simple image placeholder generator
header('Content-Type: image/png');

// Get parameters
$width = isset($_GET['width']) ? (int)$_GET['width'] : 280;
$height = isset($_GET['height']) ? (int)$_GET['height'] : 200;
$text = isset($_GET['text']) ? $_GET['text'] : 'Game Image';
$bg_color = isset($_GET['bg']) ? $_GET['bg'] : '333333';
$text_color = isset($_GET['color']) ? $_GET['color'] : 'ffffff';

// Create image
$image = imagecreate($width, $height);

// Convert hex colors to RGB
$bg_rgb = sscanf($bg_color, "%02x%02x%02x");
$text_rgb = sscanf($text_color, "%02x%02x%02x");

// Allocate colors
$background = imagecolorallocate($image, $bg_rgb[0], $bg_rgb[1], $bg_rgb[2]);
$textcolor = imagecolorallocate($image, $text_rgb[0], $text_rgb[1], $text_rgb[2]);

// Add text
$font_size = 4;
$text_width = imagefontwidth($font_size) * strlen($text);
$text_height = imagefontheight($font_size);
$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;

imagestring($image, $font_size, $x, $y, $text, $textcolor);

// Output image
imagepng($image);
imagedestroy($image);
?>
