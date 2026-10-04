<?php
if (!extension_loaded('gd')) {
    // Write minimal dummy jpeg header
    $fp = fopen(__DIR__ . '/storage/uploads/sample_repair_ram.jpg', 'w');
    fwrite($fp, "Sample Repair Photo Content");
    fclose($fp);
    exit;
}

$im = imagecreatetruecolor(600, 400);
$bg = imagecolorallocate($im, 30, 41, 59);
imagefilledrectangle($im, 0, 0, 600, 400, $bg);
$cyan = imagecolorallocate($im, 56, 189, 248);
$white = imagecolorallocate($im, 241, 245, 249);
$green = imagecolorallocate($im, 34, 197, 94);
imagerectangle($im, 20, 20, 580, 380, $cyan);
imagestring($im, 5, 140, 150, 'Smart IT Helpdesk - Repair Result', $white);
imagestring($im, 5, 180, 180, 'RAM Cleaned & Tested OK', $green);
imagestring($im, 3, 210, 220, 'Technician Inspection Pass', $cyan);
imagejpeg($im, __DIR__ . '/storage/uploads/sample_repair_ram.jpg', 90);
imagedestroy($im);
echo "Image generated successfully\n";
