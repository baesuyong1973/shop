<?php
// Usage: php replace_image.php "<target-filename.jpg>"
// Converts storage/app/public/products/_tmp_<target> (downloaded on the host
// by fetch_image.js) into a proper JPEG at storage/app/public/products/<target>,
// overwriting the existing placeholder, then deletes the temp file.
// Prints "OK <target>" or "FAIL <reason>".

$target = $argv[1] ?? null;

if (!$target) {
    fwrite(STDERR, "Usage: php replace_image.php <target-filename.jpg>\n");
    exit(1);
}

$dir = __DIR__ . '/storage/app/public/products/';
$tmpPath = $dir . '_tmp_' . basename($target);
$targetPath = $dir . basename($target);

if (!is_file($tmpPath)) {
    echo "FAIL tmp_file_missing {$tmpPath}\n";
    exit(1);
}

$data = file_get_contents($tmpPath);
$im = @imagecreatefromstring($data);

if ($im === false) {
    echo "FAIL invalid_image {$tmpPath}\n";
    exit(1);
}

$width = imagesx($im);
$height = imagesy($im);

$maxDim = 1200;
if ($width > $maxDim || $height > $maxDim) {
    $scale = min($maxDim / $width, $maxDim / $height);
    $newWidth = (int) round($width * $scale);
    $newHeight = (int) round($height * $scale);
} else {
    $newWidth = $width;
    $newHeight = $height;
}

$flat = imagecreatetruecolor($newWidth, $newHeight);
imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
imagecopyresampled($flat, $im, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

if (!imagejpeg($flat, $targetPath, 85)) {
    echo "FAIL save_failed {$targetPath}\n";
    exit(1);
}

imagedestroy($im);
imagedestroy($flat);
unlink($tmpPath);

echo "OK {$target}\n";
