<?php

ob_start();

function show_image($srcimage, $newwidth, $newheight) {
    //$this->helper('file');                   why need this?
    //$image_content = read_file($image);      We does not want to use this as output.
    $extension = pathinfo($srcimage, PATHINFO_EXTENSION);

    //resize image
    list($width, $height) = getimagesize($srcimage);
    $image = "";
    if ($extension == "jpg" || $extension == "jpeg"|| $extension == "JPG"|| $extension == "JPEG") {
        $image = imagecreatefromjpeg($srcimage);
    }
    elseif ($extension == "png" || $extension == "PNG") {
        $image = imagecreatefrompng($srcimage);
    }
    elseif ($extension == "webp" || $extension == "WEBP") {
        $image = imagecreatefromwebp($srcimage);
    }

    $thumbImage = imagecreatetruecolor($newwidth, $newheight);

    imagecopyresized($thumbImage, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    imagedestroy($image);
    //imagedestroy($thumbImage); do not destroy before display :)
    ob_end_clean();  // clean the output buffer ... if turned on.
    header("Content-Type: image/$extension");

    if ($extension == "jpg" || $extension == "jpeg"|| $extension == "JPG"|| $extension == "JPEG") {
        imagejpeg($thumbImage, NULL, 75);
    }
    elseif ($extension == "png" || $extension == "PNG") {
        imagepng($thumbImage, NULL, 75);
    }
    elseif ($extension == "webp" || $extension == "WEBP") {
        imagewebp($thumbImage, NULL, 75);
    }
    imagedestroy($thumbImage); //but not needed, cause the script exit in next line and free the used memory
    exit;
}

$path = $_GET['image'];
$width = $_GET['w'];
$height = $_GET['h'];
$type = $_GET['type'];

if ($type == "" || $type = "catalogue") {
    $path = "uploads/images/catalogue/" . $path;
} elseif ($type == "certificates") {
    $path = "uploads/images/certificates/" . $path;
}

show_image($path, $width, $height);

ob_end_flush();