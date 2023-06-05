<?php

ob_start();

function show_image($sourceImage, $newWidth, $newHeight = 0) {
    $extension = pathinfo($sourceImage, PATHINFO_EXTENSION);

    list($width, $height) = getimagesize($sourceImage);

    $setNewHeight = $newHeight;
    if ($newHeight === 0 || $newHeight === "0") {
        $setNewHeight = round($height * $newWidth /  $width);
    }

    $image = "";
    if (strtolower($extension) === "jpg" || strtolower($extension) === "jpeg") {
        $image = imagecreatefromjpeg($sourceImage);
    }
    elseif (strtolower($extension)  === "png") {
        $image = imagecreatefrompng($sourceImage);
    }
    elseif (strtolower($extension) === "webp") {
        $image = imagecreatefromwebp($sourceImage);
    }

    $thumbImage = imagecreatetruecolor($newWidth, $setNewHeight);

    imagecopyresized($thumbImage, $image, 0, 0, 0, 0, $newWidth, $setNewHeight, $width, $height);
    imagedestroy($image);

    header("Content-Type: image/$extension");

    if (strtolower($extension) === "jpg" || strtolower($extension) === "jpeg") {
        imagejpeg($thumbImage, NULL, 75);
    }
    elseif (strtolower($extension) === "png") {
        imagepng($thumbImage, NULL, 75);
    }
    elseif (strtolower($extension) === "webp") {
        imagewebp($thumbImage, NULL, 75);
    }
    imagedestroy($thumbImage);
    exit;
}

$path   = $_GET['image'];
$width  = $_GET['w'];
$height = $_GET['h'];
$type   = $_GET['type'];

if ($type === "" || $type === "catalogue") {
    $path = "uploads/images/catalogue/" . $path;
} elseif ($type === "certificates") {
    $path = "uploads/images/certificates/" . $path;
}

show_image($path, $width, $height);

ob_end_flush();