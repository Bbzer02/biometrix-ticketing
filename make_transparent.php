<?php
/**
 * Remove solid light blue background from login illustration and save with transparency.
 * Run: php make_transparent.php
 * Requires PHP ext-gd.
 */
$input  = __DIR__ . '/public/image/login-illustration.png';
$output = $input; // overwrite

if (!extension_loaded('gd')) {
    fwrite(STDERR, "PHP GD extension is required. Install it or run the Python script instead.\n");
    exit(1);
}

$img = @imagecreatefrompng($input);
if (!$img) {
    fwrite(STDERR, "Could not load image: $input\n");
    exit(1);
}

imagealphablending($img, false);
imagesavealpha($img, true);

$w = imagesx($img);
$h = imagesy($img);

// Light blue range: high B, high G, moderate R
$isLightBlue = function ($r, $g, $b) {
    return $r >= 140 && $r <= 235
        && $g >= 190 && $g <= 255
        && $b >= 220 && $b <= 255
        && $b >= $g && $g >= $r - 30;
};

for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $c = imagecolorat($img, $x, $y);
        $a = ($c >> 24) & 0x7F;
        $r = ($c >> 16) & 0xFF;
        $g = ($c >> 8) & 0xFF;
        $b = $c & 0xFF;
        if ($isLightBlue($r, $g, $b)) {
            $transparent = imagecolorallocatealpha($img, 255, 255, 255, 127);
            imagesetpixel($img, $x, $y, $transparent);
        }
    }
}

imagepng($img, $output);
imagedestroy($img);
echo "Saved transparent image to $output\n";
