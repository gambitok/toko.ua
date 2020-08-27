<?php
error_reporting(0);
@ini_set('display_errors', false);
//if ($_SERVER['REMOTE_ADDR']=="78.152.169.139"){error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);@ini_set('display_errors', true);}
define('RD', dirname (__FILE__));
date_default_timezone_set("Europe/Kiev");

function create_image_thumb($thumb, $size, $height) {
    $prod_img_thumb = 0;
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s", 10000) . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	$er=1;
	if (file_exists("uploads/images/$thumb")){$img_src="uploads/images/$thumb"; $er=0;}
	if ($er==1){$img_src="uploads/images/logo.png";$er=0;}
	
	if ($er==0){
		$sizes = getimagesize($img_src);
		$aspect_ratio = $sizes[0]/$sizes[1]; 
		$type=$sizes[2];header("Content-Type:image/$type");
		if ($sizes[0]>=$sizes[1]){
			if ($sizes[0] <= $size){
				$new_width = $sizes[0];
				$new_height = $sizes[1];
			}else{
				$new_width = $size;
				$new_height = abs($new_width/$aspect_ratio);
			}
		}
		if ($sizes[0]<$sizes[1]){
			if ($sizes[1] <= $size){
				$new_width = $sizes[0];
				$new_height = $sizes[1];
			}else{
				$new_height = $size;
				$new_width = abs($new_height*$aspect_ratio);
			}
		}
		if ($new_height>$size){
			$new_height = $size;
			$new_width = abs($new_height*$aspect_ratio);
		}
		if ($new_height>$height and $height!=""){
			$new_height = $height;
			$new_width = abs($new_height*$aspect_ratio);
		}
		$destimg=imagecreatetruecolor($new_width,$new_height);
		if ($type==1){ $srcimg=ImageCreateFromGIF($img_src); }
		if ($type==2){ $srcimg=ImageCreateFromJPEG($img_src); }
		if ($type==3){ $srcimg=ImageCreateFromPNG($img_src); }
		if ($type==4){ $srcimg=ImageCreateFromWBMP($img_src); }
		if ($type==6){ $srcimg=ImageCreateFromWBMP($img_src); }
		imagecopyresampled($destimg,$srcimg,0,0,0,0,$new_width,$new_height,ImageSX($srcimg),ImageSY($srcimg));
		$black = imagecolorallocate($destimg, 0, 0, 0);
		if ($type==1){ imagecolortransparent($destimg, $black); ImageGIF($destimg,$prod_img_thumb,100);  }
		if ($type==2){ ImageJPEG($destimg,$prod_img_thumb,100); }
		if ($type==3){ imagecolortransparent($destimg, $black);ImagePNG($destimg,$prod_img_thumb,0,100); }
		if ($type==4){ ImageWBMP($destimg); }
		if ($type==6){ ImageWBMP($destimg); }
		ImageDestroy ($destimg);
	}
}

function create_logo_thumb($thumb, $size, $max_height, $op) {
    $prod_img_thumb = 0;
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s", 10000) . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	$res=imagecreatetruecolor($size,$max_height);
	if ($op==""){$img=ImageCreateFromPNG("images/logo.png");}
	if ($op=="shop"){$img=ImageCreateFromPNG("images/logo.png");}
	imagecopyresampled($res,$img,0,0,0,0,$size,$max_height,ImageSX($img),ImageSY($img));
	
	$img_src="images/$thumb";
	$size_logo=$size*0.80;$max_height_logo=$max_height*0.80;
	$sizes = getimagesize($img_src);
	$aspect_ratio = $sizes[0]/$sizes[1]; 
	$type=$sizes[2];header("Content-Type:image/$type");
	if ($sizes[0]>=$sizes[1]){
		if ($sizes[0] <= $size_logo){ $new_width = $sizes[0]; $new_height = $sizes[1]; }
		else{ $new_width = $size_logo; $new_height = abs($new_width/$aspect_ratio); }
	}
	if ($sizes[0]<$sizes[1]){
		if ($sizes[1] <= $size_logo){ $new_width = $sizes[0]; $new_height = $sizes[1]; }
		else{ $new_height = $size_logo; $new_width = abs($new_height*$aspect_ratio); }
	}
	if ($new_height>$size_logo){ $new_height = $size_logo; $new_width = abs($new_height*$aspect_ratio); }
	if ($max_height_logo!="" and $max_height_logo<$new_height){ $new_height=$max_height_logo; $new_width=abs($new_height*$aspect_ratio); }

	if ($type==1){ $srcimg=ImageCreateFromGIF($img_src); }
	if ($type==2){ $srcimg=ImageCreateFromJPEG($img_src); }
	if ($type==3){ $srcimg=ImageCreateFromPNG($img_src); }
	if ($type==4){ $srcimg=ImageCreateFromWBMP($img_src); }
	$x=$size/2-$new_width/2;
	$y=$max_height/2-$new_height/2+2;
	imagecopyresampled($res,$srcimg,$x,$y,0,0,$new_width,$new_height,ImageSX($srcimg),ImageSY($srcimg));
		
	//$black = imagecolorallocate($res, 0, 0, 0);
	ImagePNG($res,$prod_img_thumb,0,100);
	ImageDestroy($res);
}

if ($_GET["image"]!=""){ create_image_thumb($_GET["image"],$_GET["size"],$_GET["height"]);}
if ($_GET["logo"]!=""){ create_logo_thumb($_GET["logo"],$_GET["size"],$_GET["max_height"],$_GET["op"]);}
?>