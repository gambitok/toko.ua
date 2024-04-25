<?php

error_reporting(0);
@ini_set('display_errors', false);
define('RD', dirname (__FILE__));
date_default_timezone_set("Europe/Kiev");

function create_image_thumb($thumb, $size, $height) {
    $prod_img_thumb = 0;
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s", 10000) . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	$er = 1;
    $img_src = "";
    $new_height = $new_width = 0;

	if (file_exists("uploads/images/$thumb")) {
	    $img_src = "uploads/images/$thumb";
	    $er = 0;
	}
	if ($er == 1) {
	    $img_src = "uploads/images/logo.png";
	    $er = 0;
	}
	
	if ($er == 0) {
		$sizes = getimagesize($img_src);
		$aspect_ratio = $sizes[0] / $sizes[1];
		$type = $sizes[2];
		header("Content-Type:image/$type");

		if ($sizes[0] >= $sizes[1]) {
			if ($sizes[0] <= $size) {
				$new_width = $sizes[0];
				$new_height = $sizes[1];
			} else {
				$new_width = $size;
				$new_height = abs($new_width / $aspect_ratio);
			}
		}

		if ($sizes[0] < $sizes[1]) {
			if ($sizes[1] <= $size) {
				$new_width = $sizes[0];
				$new_height = $sizes[1];
			} else {
				$new_height = $size;
				$new_width = abs($new_height * $aspect_ratio);
			}
		}

		if ($new_height > $size) {
			$new_height = $size;
			$new_width = abs($new_height * $aspect_ratio);
		}

		if ($new_height > $height and $height != "") {
			$new_height = $height;
			$new_width = abs($new_height * $aspect_ratio);
		}

        $src_img = "";
		$dest_img = imagecreatetruecolor($new_width, $new_height);
		if ($type == 1) { $src_img = ImageCreateFromGIF($img_src); }
		if ($type == 2) { $src_img = ImageCreateFromJPEG($img_src); }
		if ($type == 3) { $src_img = ImageCreateFromPNG($img_src); }
		if ($type == 4) { $src_img = ImageCreateFromWBMP($img_src); }
		if ($type == 6) { $src_img = ImageCreateFromWBMP($img_src); }
		imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $new_width, $new_height, ImageSX($src_img), ImageSY($src_img));
		$black = imagecolorallocate($dest_img, 0, 0, 0);
		if ($type == 1) { imagecolortransparent($dest_img, $black); ImageGIF($dest_img, $prod_img_thumb); }
		if ($type == 2) { ImageJPEG($dest_img, $prod_img_thumb, 100); }
		if ($type == 3) { imagecolortransparent($dest_img, $black); ImagePNG($dest_img, $prod_img_thumb, 0, 100); }
		if ($type == 4) { ImageWBMP($dest_img); }
		if ($type == 6) { ImageWBMP($dest_img); }
		ImageDestroy($dest_img);
	}
}

if ($_GET["image"] != "") {
    create_image_thumb($_GET["image"], $_GET["size"], $_GET["height"]);
}
