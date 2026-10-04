<?php
/**
 * يولّد ملفات الشعار للويب من kasp-logo-source.png:
 *   php resources/brand/generate.php
 * المخرجات في public/images/brand وpublic/apple-touch-icon.png
 * (نسخة فاتحة للخلفيات الداكنة + الشعار الدائري للأيقونة).
 */
$src = imagecreatefrompng(__DIR__.'/kasp-logo-source.png');
imagesavealpha($src, true);
$w = imagesx($src); $h = imagesy($src);

// حدود المحتوى غير الشفاف
$minX = $w; $minY = $h; $maxX = 0; $maxY = 0;
for ($y = 0; $y < $h; $y += 1) for ($x = 0; $x < $w; $x += 1) {
    $a = (imagecolorat($src, $x, $y) >> 24) & 0x7F;
    if ($a < 120) { $minX = min($minX, $x); $maxX = max($maxX, $x); $minY = min($minY, $y); $maxY = max($maxY, $y); }
}
echo "bounds $minX,$minY - $maxX,$maxY\n";

function crop($src, $x, $y, $cw, $ch) {
    $im = imagecreatetruecolor($cw, $ch);
    imagealphablending($im, false); imagesavealpha($im, true);
    imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
    imagecopy($im, $src, 0, 0, $x, $y, $cw, $ch);
    return $im;
}
function resize($im, $nw) {
    $ow = imagesx($im); $oh = imagesy($im); $nh = (int) round($oh * $nw / $ow);
    $out = imagecreatetruecolor($nw, $nh);
    imagealphablending($out, false); imagesavealpha($out, true);
    imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
    imagecopyresampled($out, $im, 0, 0, 0, 0, $nw, $nh, $ow, $oh);
    return $out;
}
/** نسخة للخلفيات الداكنة: البكسلات الداكنة منخفضة التشبع (النص والشعار الأسود) تصبح بيضاء */
function lighten($im) {
    $out = imagecreatetruecolor(imagesx($im), imagesy($im));
    imagealphablending($out, false); imagesavealpha($out, true);
    for ($y = 0; $y < imagesy($im); $y++) for ($x = 0; $x < imagesx($im); $x++) {
        $c = imagecolorat($im, $x, $y);
        $a = ($c >> 24) & 0x7F; $r = ($c >> 16) & 0xFF; $g = ($c >> 8) & 0xFF; $b = $c & 0xFF;
        $max = max($r, $g, $b); $min = min($r, $g, $b);
        if ($max < 75 && ($max - $min) < 30) { $r = $g = $b = 255; }
        imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, $r, $g, $b, $a));
    }
    return $out;
}

$full = crop($src, $minX, $minY, $maxX - $minX + 1, $maxY - $minY + 1);
@mkdir(__DIR__.'/../../public/images/brand', 0777, true);
$dark = resize($full, 720);
imagepng($dark, __DIR__.'/../../public/images/brand/kasp-logo.png', 9);
imagepng(lighten($dark), __DIR__.'/../../public/images/brand/kasp-logo-light.png', 9);

// الشعار الدائري وحده (الجزء الأيمن من الصورة)
$fw = imagesx($full); $fh = imagesy($full);
$ex = (int) round($fw * 0.635);
$emblem = crop($full, $ex, 0, $fw - $ex, $fh);
$ew = imagesx($emblem); $eh = imagesy($emblem); $side = max($ew, $eh);
$sq = imagecreatetruecolor($side, $side);
imagealphablending($sq, false); imagesavealpha($sq, true);
imagefill($sq, 0, 0, imagecolorallocatealpha($sq, 0, 0, 0, 127));
imagecopy($sq, $emblem, (int)(($side - $ew) / 2), (int)(($side - $eh) / 2), 0, 0, $ew, $eh);
imagepng(resize($sq, 256), __DIR__.'/../../public/images/brand/kasp-emblem.png', 9);
imagepng(lighten(resize($sq, 256)), __DIR__.'/../../public/images/brand/kasp-emblem-light.png', 9);
imagepng(resize($sq, 180), __DIR__.'/../../public/apple-touch-icon.png', 9);
imagepng(resize($sq, 64), __DIR__.'/../../public/images/brand/favicon-64.png', 9);
echo "done\n";
