<?php

class slave
{

    public $month_id;

    public function qq_main($q)
    {
        return str_replace("''", "'", $q);
    }

    public function qq($q)
    {
        $q = str_replace("'", "&rsquo;", $q);
        $q = str_replace("`", "&rsquo;", $q);
        $q = str_replace("\n", "<br>", $q);
        $q = str_replace("\"", "&quot;", $q);
        return $q;
    }

    public function qqback($q)
    {
        return str_replace("&rsquo;", "'", $q);
    }

    public function resizeimage($image, $size, $filedir, $prefix)
    {
        $prod_img = $filedir . $image;
        $prod_img_thumb = $filedir . $prefix . $image;
        if (file_exists("$prod_img")) {
            $sizes = getimagesize("$prod_img");
            $aspect_ratio = $sizes[0] / $sizes[1];
            $type = $sizes[2];
            if ($sizes[0] <= $size) {
                $new_width = $sizes[0];
                $new_height = $sizes[1];
            } else {
                $new_width = $size;
                $new_height = abs($new_width / $aspect_ratio);
            }
            $destimg = imagecreatetruecolor($new_width, $new_height);
            if ($type == 1) {
                $srcimg = ImageCreateFromGIF($prod_img);
            }
            if ($type == 2) {
                $srcimg = ImageCreateFromJPEG($prod_img);
            }
            if ($type == 3) {
                $srcimg = ImageCreateFromPNG($prod_img);
            }
            if ($type == 4) {
                $srcimg = ImageCreateFromWBMP($prod_img);
            }
            imagecopyresampled($destimg, $srcimg, 0, 0, 0, 0, $new_width, $new_height, ImageSX($srcimg), ImageSY($srcimg));
            if ($type == 1) {
                ImageGIF($destimg, $prod_img_thumb, 100);
            }
            if ($type == 2) {
                ImageJPEG($destimg, $prod_img_thumb, 100);
            }
            if ($type == 3) {
                imagecolortransparent($destimg, "");
                ImagePNG($destimg, $prod_img_thumb, 100);
            }
            if ($type == 4) {
                ImageWBMP($destimg, $prod_img_thumb, 100);
            }
            imagedestroy($destimg);
        }
        return true;
    }

    public function int_to_money($int)
    {
        if ($int != "-") {
            $int = str_replace(",", ".", $int);
            $int = round($int, 2);
            if (strpos($int, ".") > 0 and strpos($int, ".") == strlen($int) - 2) {
                $int .= "0";
            }
            if (strpos($int, ".") == 0) {
                $int .= ".00";
            }
        }
        return $int;
    }

}