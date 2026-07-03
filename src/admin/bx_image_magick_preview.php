<?php
require ('includes/application_top.php');
require_once (DIR_FS_ADMIN . 'includes/classes/bx_image_magick.php');

$imageSrc  = isset($_GET['src']) ? trim((string)$_GET['src']) : '';
$transform = isset($_GET['transform']) ? trim((string)$_GET['transform']) : '';
$maxWidth  = isset($_GET['width']) ? (int)$_GET['width'] : 0;
$maxHeight = isset($_GET['height']) ? (int)$_GET['height'] : 0;

if ($imageSrc === '' || $maxWidth <= 0 || $maxHeight <= 0) {
    header('HTTP/1.1 400 Bad Request');
    exit;
}

$sourceFile = bx_imagemagick_resolve_preview_source($imageSrc);
if ($sourceFile === '') {
    header('HTTP/1.1 404 Not Found');
    exit;
}

$previewName = basename($sourceFile);
$preview = new image_manipulation($sourceFile, $maxWidth, $maxHeight, $previewName, null, $transform);
$preview->setOutputToBrowser(true);
$preview->create();
exit;
