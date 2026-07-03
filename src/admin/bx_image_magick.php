<?php
/** --------------------------------------------------------------
 * $Id: admin/bx_image_magick.php 16358 2026-06-03 12:00:00Z benax $
 * modified eCommerce Shopsoftware
 * http://www.modified-shop.org
 * 
 * Copyright (c) 2009 - 2013 [www.modified-shop.org]
 * --------------------------------------------------------------
 * based on:
 * (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
 * (c) 2002-2003 osCommercecoding standards www.oscommerce.com
 * (c) 2003	nextcommerce www.nextcommerce.org
 * (c) 2003 XT-Commerce
 * 
 * Released under the GNU General Public License
 * --------------------------------------------------------------
 */

require ('includes/application_top.php');
require_once(DIR_FS_ADMIN . 'includes/extra/functions/bx_image_magick.php');
require_once(DIR_FS_ADMIN . 'includes/classes/bx_image_magick.php');
require_once(DIR_FS_INC . 'xtc_try_upload.inc.php');
/*
echo '<pre>';
var_dump($_POST);
echo '</pre>'; die();
*/

$imageSizeTabs = array(
  array(
    'id'              => 'mini',
    'title'           => TEXT_BX_IMAGE_MAGICK_TAB_MINI,
    'merge_const'     => 'PRODUCT_IMAGE_MINI_MERGE',
    'transform_const' => 'PRODUCT_IMAGE_MINI_TRANSFORM',
  ),
  array(
    'id'              => 'midi',
    'title'           => TEXT_BX_IMAGE_MAGICK_TAB_MIDI,
    'merge_const'     => 'PRODUCT_IMAGE_MIDI_MERGE',
    'transform_const' => 'PRODUCT_IMAGE_MIDI_TRANSFORM',
  ),
  array(
    'id'              => 'thumbnail',
    'title'           => TEXT_BX_IMAGE_MAGICK_TAB_THUMBNAIL,
    'merge_const'     => 'PRODUCT_IMAGE_THUMBNAIL_MERGE',
    'transform_const' => 'PRODUCT_IMAGE_THUMBNAIL_TRANSFORM',
  ),
  array(
    'id'              => 'info',
    'title'           => TEXT_BX_IMAGE_MAGICK_TAB_INFO,
    'merge_const'     => 'PRODUCT_IMAGE_INFO_MERGE',
    'transform_const' => 'PRODUCT_IMAGE_INFO_TRANSFORM',
  ),
  array(
    'id'              => 'popup',
    'title'           => TEXT_BX_IMAGE_MAGICK_TAB_POPUP,
    'merge_const'     => 'PRODUCT_IMAGE_POPUP_MERGE',
    'transform_const' => 'PRODUCT_IMAGE_POPUP_TRANSFORM',
  ),
);

$previewUploadDirectoryFS    = DIR_FS_ADMIN . DIR_WS_IMAGES . 'bx-image-magick/original/';
$previewGeneratedDirectoryFS = DIR_FS_ADMIN . DIR_WS_IMAGES . 'bx-image-magick/generated/';
$mergeOverlayDirectoryFS     = DIR_FS_ADMIN . DIR_WS_IMAGES . 'bx-image-magick/overlay/';
$previewUploadDirectoryWS    = DIR_WS_IMAGES . 'bx-image-magick/original/';
$mergeOverlayDirectoryWS     = DIR_WS_IMAGES . 'bx-image-magick/overlay/';
$previewGeneratedBaseName    = 'preview_sample';
$previewGeneratedExtensions  = array('jpg', 'png', 'gif', 'webp');
$previewGeneratedInfoImageWS = '';

foreach ($previewGeneratedExtensions as $generatedExtension) {
  $generatedInfoFileFS = $previewGeneratedDirectoryFS . 'info/' . $previewGeneratedBaseName . '.' . $generatedExtension;
  if (is_file($generatedInfoFileFS)) {
    $previewGeneratedInfoImageWS = DIR_WS_IMAGES . 'bx-image-magick/generated/info/' . $previewGeneratedBaseName . '.' . $generatedExtension;
    break;
  }
}

$mergeOverlayOptions = array();
$mergeOverlayFiles = glob($mergeOverlayDirectoryFS . '*.{png,PNG,jpg,JPG,jpeg,JPEG,gif,GIF,webp,WEBP}', GLOB_BRACE);
$mergeOverlayFiles = is_array($mergeOverlayFiles) ? $mergeOverlayFiles : array();
if (!empty($mergeOverlayFiles)) {
  natsort($mergeOverlayFiles);
  foreach ($mergeOverlayFiles as $overlayFile) {
    $overlayBasename = basename((string)$overlayFile);
    if ($overlayBasename === '') {
      continue;
    }

    $overlayValue = 'bx-image-magick/overlay/' . $overlayBasename;
    $mergeOverlayOptions[$overlayValue] = $overlayBasename;
  }
}

$previewImageTabs = array(
  'mini' => array(
    'width' => (int)(defined('PRODUCT_IMAGE_MINI_WIDTH') ? constant('PRODUCT_IMAGE_MINI_WIDTH') : 0),
    'height' => (int)(defined('PRODUCT_IMAGE_MINI_HEIGHT') ? constant('PRODUCT_IMAGE_MINI_HEIGHT') : 0),
    'transform' => (string)(defined('PRODUCT_IMAGE_MINI_TRANSFORM') ? constant('PRODUCT_IMAGE_MINI_TRANSFORM') : ''),
  ),
  'midi' => array(
    'width' => (int)(defined('PRODUCT_IMAGE_MIDI_WIDTH') ? constant('PRODUCT_IMAGE_MIDI_WIDTH') : 0),
    'height' => (int)(defined('PRODUCT_IMAGE_MIDI_HEIGHT') ? constant('PRODUCT_IMAGE_MIDI_HEIGHT') : 0),
    'transform' => (string)(defined('PRODUCT_IMAGE_MIDI_TRANSFORM') ? constant('PRODUCT_IMAGE_MIDI_TRANSFORM') : ''),
  ),
  'thumbnail' => array(
    'width' => (int)(defined('PRODUCT_IMAGE_THUMBNAIL_WIDTH') ? constant('PRODUCT_IMAGE_THUMBNAIL_WIDTH') : 0),
    'height' => (int)(defined('PRODUCT_IMAGE_THUMBNAIL_HEIGHT') ? constant('PRODUCT_IMAGE_THUMBNAIL_HEIGHT') : 0),
    'transform' => (string)(defined('PRODUCT_IMAGE_THUMBNAIL_TRANSFORM') ? constant('PRODUCT_IMAGE_THUMBNAIL_TRANSFORM') : ''),
  ),
  'info' => array(
    'width' => (int)(defined('PRODUCT_IMAGE_INFO_WIDTH') ? constant('PRODUCT_IMAGE_INFO_WIDTH') : 0),
    'height' => (int)(defined('PRODUCT_IMAGE_INFO_HEIGHT') ? constant('PRODUCT_IMAGE_INFO_HEIGHT') : 0),
    'transform' => (string)(defined('PRODUCT_IMAGE_INFO_TRANSFORM') ? constant('PRODUCT_IMAGE_INFO_TRANSFORM') : ''),
  ),
  'popup' => array(
    'width' => (int)(defined('PRODUCT_IMAGE_POPUP_WIDTH') ? constant('PRODUCT_IMAGE_POPUP_WIDTH') : 0),
    'height' => (int)(defined('PRODUCT_IMAGE_POPUP_HEIGHT') ? constant('PRODUCT_IMAGE_POPUP_HEIGHT') : 0),
    'transform' => (string)(defined('PRODUCT_IMAGE_POPUP_TRANSFORM') ? constant('PRODUCT_IMAGE_POPUP_TRANSFORM') : ''),
  ),
);

if (isset($_GET['action']) && $_GET['action'] === 'save' && isset($_POST['save_image_magick_settings'])) {
  $submitAction = isset($_POST['submit_action']) ? trim((string)$_POST['submit_action']) : '';
  $requestedPreviewFile = '';

  if (isset($_POST['delete_preview_image'])) {
    $submitAction = 'delete_preview_image';
    $requestedPreviewFile = basename((string)$_POST['delete_preview_image']);
  } elseif (isset($_POST['generate_preview_image'])) {
    $submitAction = 'generate_preview_image';
    $requestedPreviewFile = basename((string)$_POST['generate_preview_image']);
  }

  if ($submitAction === '') {
    $submitAction = 'save_settings';
  }

  if ($submitAction === 'upload_preview') {
    $uploadDirectory = $previewUploadDirectoryFS;
    
    if (!is_dir($uploadDirectory)) {
      if (!mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
        $messageStack->add_session(sprintf(TEXT_BX_IMAGE_MAGICK_MESSAGE_UPLOAD_DIR_NOT_WRITABLE, $uploadDirectory), 'error');
        xtc_redirect(xtc_href_link(FILENAME_BX_IMAGE_MAGICK));
      }
    }

    if (!is_dir($uploadDirectory) || !is_writable($uploadDirectory)) {
      $messageStack->add_session(sprintf(TEXT_BX_IMAGE_MAGICK_MESSAGE_UPLOAD_DIR_NOT_WRITABLE, $uploadDirectory), 'error');
      xtc_redirect(xtc_href_link(FILENAME_BX_IMAGE_MAGICK));
    }

    $accepted_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $accepted_mime_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp');

    $uploadResult = xtc_try_upload('preview_original_file', 
                $uploadDirectory, 
                '644', 
                $accepted_extensions, 
                $accepted_mime_types);

    if ($uploadResult !== false && isset($uploadResult->filename) && $uploadResult->filename !== '') {
      $messageStack->add_session(TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_UPLOAD_SUCCESS, 'success');
    } else {
      $messageStack->add_session(TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_UPLOAD_FAILED, 'warning');
    }

    xtc_redirect(xtc_href_link(FILENAME_BX_IMAGE_MAGICK));
  }

  if ($submitAction === 'delete_preview_image') {
    $uploadRoot = realpath($previewUploadDirectoryFS);
    $targetFile = realpath($previewUploadDirectoryFS . $requestedPreviewFile);
    $normalizedUploadRoot = $uploadRoot !== false ? str_replace('\\', '/', $uploadRoot) : '';
    $normalizedTargetFile = $targetFile !== false ? str_replace('\\', '/', $targetFile) : '';
    $targetPrefixPosition = ($normalizedUploadRoot !== '' && $normalizedTargetFile !== '') ? strpos($normalizedTargetFile, $normalizedUploadRoot) : false;

    if ($requestedPreviewFile === ''
        || $uploadRoot === false
        || $targetFile === false
        || $targetPrefixPosition === false
        || $targetPrefixPosition !== 0
        || !is_file($targetFile)
    ) {
      $messageStack->add_session(TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_NOT_FOUND, 'warning');
      xtc_redirect(xtc_href_link(FILENAME_BX_IMAGE_MAGICK));
    }

    $deleted = false;
    if (is_writable($targetFile)) {
      $deleted = unlink($targetFile);
    }

    $requestedPathInfo = pathinfo($requestedPreviewFile);
    $requestedBaseName = isset($requestedPathInfo['filename']) ? (string)$requestedPathInfo['filename'] : '';

    foreach ($previewImageTabs as $tabId => $tabConfig) {
      if ($requestedBaseName !== '') {
        foreach ($previewGeneratedExtensions as $generatedExtension) {
          $generatedFile = $previewGeneratedDirectoryFS . $tabId . '/' . $requestedBaseName . '.' . $generatedExtension;
          if (is_file($generatedFile) && is_writable($generatedFile)) {
            unlink($generatedFile);
          }
        }
      }

      foreach ($previewGeneratedExtensions as $generatedExtension) {
        $fixedGeneratedFile = $previewGeneratedDirectoryFS . $tabId . '/' . $previewGeneratedBaseName . '.' . $generatedExtension;
        if (is_file($fixedGeneratedFile) && is_writable($fixedGeneratedFile)) {
          unlink($fixedGeneratedFile);
        }
      }

      $legacyGeneratedFile = $previewGeneratedDirectoryFS . $tabId . '/' . $requestedPreviewFile;
      if (is_file($legacyGeneratedFile) && is_writable($legacyGeneratedFile)) {
        unlink($legacyGeneratedFile);
      }
    }

    if ($deleted) {
      $messageStack->add_session(sprintf(TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_DELETED, $requestedPreviewFile), 'success');
    } else {
      $messageStack->add_session(TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_DELETE_FAILED, 'error');
    }

    xtc_redirect(xtc_href_link(FILENAME_BX_IMAGE_MAGICK));
  }

  if ($submitAction === 'generate_preview_image') {
    $uploadRoot = realpath($previewUploadDirectoryFS);
    $sourceFile = realpath($previewUploadDirectoryFS . $requestedPreviewFile);
    $normalizedUploadRoot = $uploadRoot !== false ? str_replace('\\', '/', $uploadRoot) : '';
    $normalizedSourceFile = $sourceFile !== false ? str_replace('\\', '/', $sourceFile) : '';
    $sourcePrefixPosition = ($normalizedUploadRoot !== '' && $normalizedSourceFile !== '') ? strpos($normalizedSourceFile, $normalizedUploadRoot) : false;

    if ($requestedPreviewFile === ''
        || $uploadRoot === false
        || $sourceFile === false
        || $sourcePrefixPosition === false
        || $sourcePrefixPosition !== 0
        || !is_file($sourceFile)
    ) {
      $messageStack->add_session(TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_NOT_FOUND, 'warning');
      xtc_redirect(xtc_href_link(FILENAME_BX_IMAGE_MAGICK));
    }

    $generatedCount = 0;
    foreach ($previewImageTabs as $tabId => $tabConfig) {
      if ((int)$tabConfig['width'] <= 0 || (int)$tabConfig['height'] <= 0) {
        continue;
      }

      foreach ($previewGeneratedExtensions as $generatedExtension) {
        $destinationFile = $previewGeneratedDirectoryFS . $tabId . '/' . $previewGeneratedBaseName . '.' . $generatedExtension;
        $previewImage = new image_manipulation(
          $sourceFile,
          (int)$tabConfig['width'],
          (int)$tabConfig['height'],
          $destinationFile,
          null,
          (string)$tabConfig['transform']
        );
        $previewImage->create();

        if (is_file($destinationFile)) {
          $generatedCount++;
        }
      }
    }

    if ($generatedCount > 0) {
      $messageStack->add_session(sprintf(TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_GENERATED, $requestedPreviewFile, $generatedCount), 'success');
    } else {
      $messageStack->add_session(TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_GENERATE_FAILED, 'error');
    }

    xtc_redirect(xtc_href_link(FILENAME_BX_IMAGE_MAGICK));
  }

  if ($submitAction !== 'save_settings') {
    xtc_redirect(xtc_href_link(FILENAME_BX_IMAGE_MAGICK));
  }

  $updated = 0;
  $skipped = 0;

  foreach ($imageSizeTabs as $tab) {
    $mergeKey     = $tab['merge_const'];
    $transformKey = $tab['transform_const'];

    $mergeInputName             = 'merge_' . $tab['id'];
    $transformInputName         = 'transform_' . $tab['id'];
    $greyscaleInputName         = 'greyscale_' . $tab['id'];
    $roundEdgesInputName        = 'round_edges_' . $tab['id'];
    $roundEdgesColorInputName   = 'round_edges_color_' . $tab['id'];
    $dropShadowInputName        = 'drop_shadow_' . $tab['id'];
    $dropShadowColorInputName   = 'drop_shadow_color_' . $tab['id'];
    $dropShadowBgColorInputName = 'drop_shadow_bg_color_' . $tab['id'];
    $dropShadowFadeInputName    = 'drop_shadow_fade_' . $tab['id'];

    $mergeValue = isset($_POST[$mergeInputName]) ? trim((string)$_POST[$mergeInputName]) : '';
    if (strlen($mergeValue) > 512) {
      $mergeValue = substr($mergeValue, 0, 512);
    }

    $transformRaw = isset($_POST[$transformInputName]) ? (string)$_POST[$transformInputName] : '';
    $transformValue = bx_imagemagick_normalize_transform($transformRaw);
    if ($transformRaw !== '' && $transformValue === '') {
      $skipped++;
    }

    $greyscaleRaw = isset($_POST[$greyscaleInputName]) ? (string)$_POST[$greyscaleInputName] : '';
    $greyscaleValue = bx_imagemagick_normalize_greyscale_triplet($greyscaleRaw);
    if ($greyscaleRaw !== '' && $greyscaleValue === '') {
      $skipped++;
    }

    $roundEdgesRadius = isset($_POST[$roundEdgesInputName]) ? (int)$_POST[$roundEdgesInputName] : 0;
    $roundEdgesRadius = max(0, min(100, $roundEdgesRadius));
    $roundEdgesBackgroundColor = bx_imagemagick_normalize_hex_color(isset($_POST[$roundEdgesColorInputName]) ? (string)$_POST[$roundEdgesColorInputName] : '', 'FFFFFF');

    $dropShadowWidth = isset($_POST[$dropShadowInputName]) ? (int)$_POST[$dropShadowInputName] : 0;
    $dropShadowWidth = max(0, min(50, $dropShadowWidth));
    $dropShadowColor = bx_imagemagick_normalize_hex_color(isset($_POST[$dropShadowColorInputName]) ? (string)$_POST[$dropShadowColorInputName] : '', '000000');
    $dropShadowBgColor = bx_imagemagick_normalize_hex_color(isset($_POST[$dropShadowBgColorInputName]) ? (string)$_POST[$dropShadowBgColorInputName] : '', 'FFFFFF');
    
    $dropShadowFade = isset($_POST[$dropShadowFadeInputName]) ? (int)$_POST[$dropShadowFadeInputName] : 0;
    $dropShadowFade = max(0, min(100, $dropShadowFade));

    $transformValue = bx_imagemagick_upsert_round_edges_transform($transformValue, $roundEdgesRadius, $roundEdgesBackgroundColor);
    $transformValue = bx_imagemagick_upsert_drop_shadow_transform($transformValue, $dropShadowWidth, $dropShadowColor, $dropShadowBgColor, $dropShadowFade);
    $transformValue = bx_imagemagick_upsert_greyscale_transform($transformValue, $greyscaleValue);

    if (bx_imagemagick_save_configuration($mergeKey, $mergeValue)) {
      $updated++;
    }

    if (bx_imagemagick_save_configuration($transformKey, $transformValue)) {
      $updated++;
    }
  }

  if ($updated > 0) {
    $messageStack->add_session(TEXT_BX_IMAGE_MAGICK_MESSAGE_SETTINGS_SAVED, 'success');
  }
  if ($skipped > 0) {
    $messageStack->add_session(TEXT_BX_IMAGE_MAGICK_MESSAGE_INVALID_TRANSFORMS_RESET, 'warning');
  }

  xtc_redirect(xtc_href_link(FILENAME_BX_IMAGE_MAGICK));
}


require_once (DIR_WS_INCLUDES.'head.php');

$messageStack->output();
?>
</head>
<!-- header //-->
<?php require(DIR_WS_INCLUDES.'header.php'); ?>

<!-- header_eof //-->
<!-- body //-->
<table class="tableBody">
  <tr>
    <?php //left_navigation
    if (!defined('USE_ADMIN_TOP_MENU') || USE_ADMIN_TOP_MENU == 'false') {
      echo '<td class="columnLeft2">'.PHP_EOL;
      echo '<!-- left_navigation //-->'.PHP_EOL;
      require_once(DIR_WS_INCLUDES.'column_left.php');
      echo '<!-- left_navigation eof //-->'.PHP_EOL;
      echo '</td>'.PHP_EOL;
    }
    ?>
    <!-- body_text //-->
    <td class="boxCenter">
      <div class="pageHeadingImage" style="width: 42px;">
        <?php echo xtc_image(DIR_WS_ICONS.'heading/bx_image_magick.png', BX_IMAGE_MAGICK_TITLE, '', '', 'style="height: 42px;"'); ?>
      </div>
      <div class="pageHeading" style="margin-bottom: 5px;">
        <?php echo BX_IMAGE_MAGICK_TITLE; ?>
      </div>
      <div class="main flt-l">
        <?php echo BX_IMAGE_MAGICK_SHORT_DESCRIPTION; ?>
      </div>
      <div class="clear"></div>

      <table class="tableCenter" style="margin-top: 5px;">
        <tr>
          <td class="boxCenterLeft">
            <div id="bx_header">
              <div class="main"><?php echo BX_IMAGE_MAGICK_LONG_DESCRIPTION; ?></div>
            </div>
            
            <div class="magick-tabs">
              <?php echo xtc_draw_form('bx_image_magick_settings', FILENAME_BX_IMAGE_MAGICK, 'action=save', 'post', 'enctype="multipart/form-data"'); ?>
              <input type="hidden" name="save_image_magick_settings" value="1" />
              <ul class="tab-nav">
                <?php foreach ($imageSizeTabs as $tab) { 
                  $tabIdLeft    = 'tab-' . $tab['id'] . '-left';
                  $tabIdRight   = 'tab-' . $tab['id'] . '-right';
                  $image_width  = constant('PRODUCT_IMAGE_' . strtoupper($tab['id']) . '_WIDTH');
                  $image_height = constant('PRODUCT_IMAGE_' . strtoupper($tab['id']) . '_HEIGHT');
                ?>
                  <li>
                    <a href="#<?php echo $tabIdLeft; ?>" 
                      class="tab-link"
                      data-tab-id="<?php echo htmlspecialchars($tab['id'], ENT_QUOTES, 'UTF-8'); ?>"
                      data-target-left="#<?php echo $tabIdLeft; ?>" 
                      data-preview-width="<?php echo $image_width; ?>" 
                      data-preview-height="<?php echo $image_height; ?>">
                      <span style="font-size: 14px;">🖼️</span> <?php echo $tab['title']; ?>
                    </a>
                  </li>
                <?php } ?>
                <li>
                  <a href="#tab-preview_file-left" 
                    class="tab-link"
                    data-tab-id="preview_file"
                    data-target-left="#tab-preview_file-left">
                    <span style="font-size: 14px;">🧪</span> <?php echo TEXT_BX_IMAGE_MAGICK_TAB_PREVIEW_FILE; ?>
                  </a>
                </li>                
                <li>
                  <a href="#tab-support-left" 
                    class="tab-link"
                    data-tab-id="support"
                    data-target-left="#tab-support-left">
                    <span style="font-size: 14px;">🛠️</span> <?php echo TEXT_BX_IMAGE_MAGICK_TAB_SUPPORT; ?>
                  </a>
                </li>
              </ul>

              <div class="tab-content">
                
                <?php foreach ($imageSizeTabs as $tab) { ?>
                  <div id="tab-<?php echo $tab['id']; ?>-left">
                    <div class="magick-settings-wrap">
                      <div class="main" style="padding: 2px 0 8px 0;">
                        <strong><?php echo $tab['title']; ?></strong>
                        <div style="margin-top: 4px; color: #666;">
                          <?php echo TEXT_BX_IMAGE_MAGICK_IMAGE_TAB_INTRO; ?>
                        </div>
                      </div>
                      <?php
                        $previewSamples = bx_image_magick_get_preview_samples((string)$tab['id']);
                        $inlinePreviewSrc = '';
                        if (!empty($previewSamples) && isset($previewSamples[0]['src'])) {
                          $inlinePreviewSrc = (string)$previewSamples[0]['src'];
                        }
                      ?>
                      <div class="magick-workspace-tabs">
                        <div class="magick-workspace-left">
                          <div class="magick-inline-preview-panel">
                            <div class="magick-inline-preview-title"><strong>🧪 <?php echo TEXT_BX_IMAGE_MAGICK_PREVIEW_PANEL; ?></strong></div>
                            <div class="magick-preview-placeholder">
                              <div class="magick-preview-image-area">
                                <?php if ($inlinePreviewSrc !== '') { ?>
                                  <img src="<?php echo htmlspecialchars($inlinePreviewSrc, ENT_QUOTES, 'UTF-8'); ?>" data-original-src="<?php echo htmlspecialchars($inlinePreviewSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="" />
                                <?php } else { ?>
                                  <div class="magick-preview-empty">Keine Beispielbilder vorhanden.</div>
                                <?php } ?>
                              </div>
                            </div>
                            <div class="magick-size-warning">⚠️ <?php echo TEXT_BX_IMAGE_MAGICK_MESSAGE_IMAGE_SCALED_DOWN; ?></div>
                            <?php bx_image_magick_render_preview_sample_gallery((string)$tab['id']); ?>
                          </div>
                        </div>

                        <div class="magick-workspace-right">
                          <div class="magick-settings-grid">
                        <?php
                          $mergeFieldValue = (string)bx_imagemagick_const_value($tab['merge_const']);
                          $mergeFileValue = '';
                          $mergeXValue = 0;
                          $mergeYValue = 0;
                          $mergeOpacityValue = '60';
                          $mergeColorValue = 'FF0000';

                          if (preg_match('/^\(\s*([^,]+)\s*,\s*(-?\d+)\s*,\s*(-?\d+)\s*,\s*([^,]+)\s*,\s*([^)]+)\s*\)$/', $mergeFieldValue, $mergeMatches)) {
                            $mergeFileValue = trim((string)$mergeMatches[1]);
                            $mergeXValue = (int)$mergeMatches[2];
                            $mergeYValue = (int)$mergeMatches[3];
                            $mergeOpacityValue = trim((string)$mergeMatches[4]);
                            $mergeColorValue = strtoupper(trim((string)$mergeMatches[5]));
                            if (strcasecmp($mergeFileValue, 'none') === 0) {
                              $mergeFileValue = '';
                            }
                          }

                          $mergeXValue = max(0, $mergeXValue);
                          $mergeYValue = max(0, $mergeYValue);
                          $mergeCanvasWidth = max(180, (int)$image_width);
                          $mergeCanvasHeight = max(120, (int)$image_height);
                          $mergeOverlaySelectOptions = array(
                            '' => TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_OVERLAY_NONE,
                          ) + $mergeOverlayOptions;
                          if ($mergeFileValue !== '' && !isset($mergeOverlaySelectOptions[$mergeFileValue])) {
                            $mergeOverlaySelectOptions[$mergeFileValue] = $mergeFileValue;
                          }
                        ?>
                        <label><?php echo TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_TITLE; ?><span class="magick-label-help" tabindex="0" aria-label="Info">i<span class="magick-label-help-popup"><?php echo TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_HINT; ?></span></span></label>
                        <div class="magick-merge-positioner js-merge-positioner"
                             data-tab-id="<?php echo htmlspecialchars($tab['id'], ENT_QUOTES, 'UTF-8'); ?>"
                             data-canvas-width="<?php echo (int)$mergeCanvasWidth; ?>"
                             data-canvas-height="<?php echo (int)$mergeCanvasHeight; ?>"
                             data-merge-file="<?php echo htmlspecialchars($mergeFileValue, ENT_QUOTES, 'UTF-8'); ?>"
                             data-merge-opacity="<?php echo htmlspecialchars($mergeOpacityValue, ENT_QUOTES, 'UTF-8'); ?>"
                             data-merge-color="<?php echo htmlspecialchars($mergeColorValue, ENT_QUOTES, 'UTF-8'); ?>">
                          <div class="magick-merge-positioner-handle" id="merge_positioner_handle_<?php echo $tab['id']; ?>"><?php echo TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_OVERLAY; ?></div>
                          <div class="magick-merge-positioner-controls">
                            <div class="magick-merge-positioner-row magick-merge-positioner-row-overlay">
                              <label for="merge_overlay_select_<?php echo $tab['id']; ?>"><?php echo TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_OVERLAY_FILE; ?></label>
                              <select id="merge_overlay_select_<?php echo $tab['id']; ?>" class="js-merge-overlay-select">
                                <?php foreach ($mergeOverlaySelectOptions as $overlayValue => $overlayLabel) {
                                  $overlayValueString = (string)$overlayValue;
                                  $overlayPreviewSrc = '';
                                  if (strpos($overlayValueString, 'bx-image-magick/overlay/') === 0) {
                                    $overlayPreviewSrc = $mergeOverlayDirectoryWS . rawurlencode((string)basename($overlayValueString));
                                  }
                                ?>
                                  <option value="<?php echo htmlspecialchars($overlayValueString, ENT_QUOTES, 'UTF-8'); ?>" data-preview-src="<?php echo htmlspecialchars($overlayPreviewSrc, ENT_QUOTES, 'UTF-8'); ?>"<?php if ((string)$mergeFileValue === $overlayValueString) { ?> selected="selected"<?php } ?>><?php echo htmlspecialchars((string)$overlayLabel, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php } ?>
                              </select>
                              <span></span>
                            </div>
                            <div class="magick-merge-positioner-row">
                              <label for="merge_positioner_x_<?php echo $tab['id']; ?>"><?php echo TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_X; ?></label>
                              <input type="range" id="merge_positioner_x_<?php echo $tab['id']; ?>" class="js-merge-positioner-x" min="0" max="<?php echo (int)$mergeCanvasWidth; ?>" value="<?php echo (int)$mergeXValue; ?>" />
                              <span class="magick-merge-positioner-value js-merge-positioner-x-value"><?php echo (int)$mergeXValue; ?></span>
                            </div>
                            <div class="magick-merge-positioner-row">
                              <label for="merge_positioner_y_<?php echo $tab['id']; ?>"><?php echo TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_Y; ?></label>
                              <input type="range" id="merge_positioner_y_<?php echo $tab['id']; ?>" class="js-merge-positioner-y" min="0" max="<?php echo (int)$mergeCanvasHeight; ?>" value="<?php echo (int)$mergeYValue; ?>" />
                              <span class="magick-merge-positioner-value js-merge-positioner-y-value"><?php echo (int)$mergeYValue; ?></span>
                            </div>
                          </div>
                          <input type="text" class="w100" name="merge_<?php echo $tab['id']; ?>" value="<?php echo htmlspecialchars($mergeFieldValue, ENT_QUOTES, 'UTF-8'); ?>" />
                        </div>
                        <label><?php echo TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_ORDER; ?><span class="magick-label-help" tabindex="0" aria-label="Info">i<span class="magick-label-help-popup"><?php echo TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_ORDER_HINT; ?></span></span></label>
                        <input type="text" class="w100" name="transform_<?php echo $tab['id']; ?>" value="<?php echo htmlspecialchars(bx_imagemagick_const_value($tab['transform_const']), ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_PLACEHOLDER; ?>" readonly />

                        <?php
                          $roundEdgesMin = 0;
                          $roundEdgesMax = 100;
                          $roundEdgesListId = 'tickmarks_round_edges_' . $tab['id'];
                          $roundEdgesConfig = bx_imagemagick_extract_round_edges_values(bx_imagemagick_const_value($tab['transform_const']));
                          $roundEdgesCurrentRadius = max($roundEdgesMin, min($roundEdgesMax, (int)$roundEdgesConfig['radius']));
                          $roundEdgesCurrentColor = '#' . strtolower((string)$roundEdgesConfig['background_color']);
                        ?>
                        <label for="round_edges_id_<?php echo $tab['id']; ?>"><?php echo TEXT_BX_IMAGE_MAGICK_FIELD_ROUND_EDGES; ?></label>
                        <div class="range-control">
                          <table class="range-table" cellspacing="0" cellpadding="0">
                            <tr>
                              <td class="range-table-slider">
                                <input type="range" min="<?php echo $roundEdgesMin; ?>" max="<?php echo $roundEdgesMax; ?>" id="round_edges_id_<?php echo $tab['id']; ?>" name="round_edges_<?php echo $tab['id']; ?>" value="<?php echo $roundEdgesCurrentRadius; ?>" list="<?php echo $roundEdgesListId; ?>" />
                                <datalist id="<?php echo $roundEdgesListId; ?>">
                                  <?php for ($i = $roundEdgesMin; $i <= $roundEdgesMax; $i++) { ?>
                                    <option value="<?php echo $i; ?>"<?php if ($i === $roundEdgesMin || $i === $roundEdgesMax) { ?> label="<?php echo $i; ?>"<?php } ?>></option>
                                  <?php } ?>
                                </datalist>
                                <div class="range-minmax">
                                  <span><?php echo $roundEdgesMin; ?></span>
                                  <span><?php echo $roundEdgesMax; ?></span>
                                </div>
                              </td>
                              <td class="range-table-color">
                                <input type="color" id="round_edges_color_<?php echo $tab['id']; ?>" name="round_edges_color_<?php echo $tab['id']; ?>" value="<?php echo htmlspecialchars($roundEdgesCurrentColor, ENT_QUOTES, 'UTF-8'); ?>" title="Background Color" />
                              </td>
                              <td class="range-table-current">
                                <span class="range-current" data-for="round_edges_id_<?php echo $tab['id']; ?>"><?php echo $roundEdgesCurrentRadius; ?></span> px
                              </td>
                            </tr>
                          </table>
                        </div>

                        <?php
                          $dropShadowMin            = 0;
                          $dropShadowMax            = 50;
                          $dropShadowListId         = 'tickmarks_drop_shadow_' . $tab['id'];
                          $dropShadowConfig         = bx_imagemagick_extract_drop_shadow_values(bx_imagemagick_const_value($tab['transform_const']));
                          $dropShadowCurrentWidth   = max($dropShadowMin, min($dropShadowMax, (int)$dropShadowConfig['width']));
                          $dropShadowCurrentColor   = '#' . strtolower((string)$dropShadowConfig['shadow_color']);
                          $dropShadowCurrentBgColor = '#' . strtolower((string)$dropShadowConfig['background_color']);
                          $dropShadowCurrentFade    = max(0, min(100, isset($dropShadowConfig['fade']) ? (int)$dropShadowConfig['fade'] : 0));
                          $dropShadowFadeListId     = 'tickmarks_drop_shadow_fade_' . $tab['id'];
                        ?>
                        <label for="drop_shadow_id_<?php echo $tab['id']; ?>"><?php echo TEXT_BX_IMAGE_MAGICK_FIELD_DROP_SHADOW; ?><span class="magick-label-help" tabindex="0" aria-label="Info">i<span class="magick-label-help-popup"><?php echo TEXT_BX_IMAGE_MAGICK_DROP_SHADOW_COLOR_HINT; ?></span></span></label>
                        <div class="range-control">
                          <table class="range-table" cellspacing="0" cellpadding="0">
                            <tr>
                              <td class="range-table-slider">
                                <input type="range" min="<?php echo $dropShadowMin; ?>" max="<?php echo $dropShadowMax; ?>" id="drop_shadow_id_<?php echo $tab['id']; ?>" name="drop_shadow_<?php echo $tab['id']; ?>" value="<?php echo $dropShadowCurrentWidth; ?>" list="<?php echo $dropShadowListId; ?>" />
                                <datalist id="<?php echo $dropShadowListId; ?>">
                                  <?php for ($i = $dropShadowMin; $i <= $dropShadowMax; $i++) { ?>
                                    <option value="<?php echo $i; ?>"<?php if ($i === $dropShadowMin || $i === $dropShadowMax) { ?> label="<?php echo $i; ?>"<?php } ?>></option>
                                  <?php } ?>
                                </datalist>
                                <div class="range-minmax">
                                  <span><?php echo $dropShadowMin; ?></span>
                                  <span><?php echo $dropShadowMax; ?></span>
                                </div>
                              </td>
                              <td class="range-table-color">
                                <input type="color" id="drop_shadow_color_<?php echo $tab['id']; ?>" name="drop_shadow_color_<?php echo $tab['id']; ?>" value="<?php echo htmlspecialchars($dropShadowCurrentColor, ENT_QUOTES, 'UTF-8'); ?>" title="Shadow Color" />
                                <input type="color" id="drop_shadow_bg_color_<?php echo $tab['id']; ?>" name="drop_shadow_bg_color_<?php echo $tab['id']; ?>" value="<?php echo htmlspecialchars($dropShadowCurrentBgColor, ENT_QUOTES, 'UTF-8'); ?>" title="Shadow Background" />
                              </td>
                              <td class="range-table-current">
                                <div class="current-range"><span class="range-current" data-for="drop_shadow_id_<?php echo $tab['id']; ?>"><?php echo $dropShadowCurrentWidth; ?></span> px</div>
                              </td>
                            </tr>
                          </table>
                        </div>

                        <label for="drop_shadow_fade_id_<?php echo $tab['id']; ?>"><?php echo TEXT_BX_IMAGE_MAGICK_FIELD_DROP_SHADOW_FADE; ?><span class="magick-label-help" tabindex="0" aria-label="Info">i<span class="magick-label-help-popup"><?php echo TEXT_BX_IMAGE_MAGICK_DROP_SHADOW_FADE_HINT; ?></span></span></label>
                        <div class="range-control">
                          <table class="range-table" cellspacing="0" cellpadding="0">
                            <tr>
                              <td class="range-table-slider">
                                <input type="range" min="0" max="100" id="drop_shadow_fade_id_<?php echo $tab['id']; ?>" name="drop_shadow_fade_<?php echo $tab['id']; ?>" value="<?php echo $dropShadowCurrentFade; ?>" list="<?php echo $dropShadowFadeListId; ?>" />
                                <datalist id="<?php echo $dropShadowFadeListId; ?>">
                                  <?php for ($i = 0; $i <= 100; $i++) { ?>
                                    <option value="<?php echo $i; ?>"<?php if ($i === 0 || $i === 100) { ?> label="<?php echo $i; ?>"<?php } ?>></option>
                                  <?php } ?>
                                </datalist>
                                <div class="range-minmax">
                                  <span>0</span>
                                  <span>100</span>
                                </div>
                              </td>
                              <td class="range-table-current">
                                <div class="current-range"><span class="range-current" data-for="drop_shadow_fade_id_<?php echo $tab['id']; ?>"><?php echo $dropShadowCurrentFade; ?></span> %</div>
                            </tr>
                          </table>
                        </div>

                        <?php
                          $greyscaleTriplet = bx_imagemagick_extract_greyscale_triplet(bx_imagemagick_const_value($tab['transform_const']));
                          $greyscaleOptions = array(
                            'none' => TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_NONE,
                            '85,85,85' => TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_NEUTRAL,
                            '77,151,28' => TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_STANDARD,
                            '54,183,18' => TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_GREEN_STRONG,
                            '95,135,25' => TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_PORTRAIT,
                            '122,104,30' => TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_WARM,
                            '60,120,75' => TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_COOL,
                            '255,0,0' => TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_RED_CHANNEL,
                            '0,255,0' => TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_GREEN_CHANNEL,
                            '0,0,255' => TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_BLUE_CHANNEL,
                          );
                          if ($greyscaleTriplet !== 'none' && !isset($greyscaleOptions[$greyscaleTriplet])) {
                            $greyscaleOptions[$greyscaleTriplet] = TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_CUSTOM . ' ' . $greyscaleTriplet;
                          }
                        ?>
                        <label for="greyscale_id_<?php echo $tab['id']; ?>"><?php echo TEXT_BX_IMAGE_MAGICK_FIELD_GREYSCALE; ?><span class="magick-label-help" tabindex="0" aria-label="Info">i<span class="magick-label-help-popup"><?php echo TEXT_BX_IMAGE_MAGICK_FIELD_GREYSCALE_HINT; ?></span></span></label>
                        <select id="greyscale_id_<?php echo $tab['id']; ?>" name="greyscale_<?php echo $tab['id']; ?>">
                          <?php foreach ($greyscaleOptions as $rgbValue => $rgbLabel) {
                            if (preg_match('/^\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*$/', (string)$rgbValue, $rgbMatches)) {
                              $r = max(0, min(255, (int)$rgbMatches[1]));
                              $g = max(0, min(255, (int)$rgbMatches[2]));
                              $b = max(0, min(255, (int)$rgbMatches[3]));
                              $avg = (int)round(($r + $g + $b) / 3);
                              $textColor = ($avg < 128) ? '#ffffff' : '#000000';
                              $style = 'background-color: rgb(' . $r . ',' . $g . ',' . $b . '); color: ' . $textColor . ';';
                            } else {
                              $style = 'background-color: #f2f2f2; color: #000000;';
                            }
                          ?>
                            <option value="<?php echo htmlspecialchars($rgbValue, ENT_QUOTES, 'UTF-8'); ?>" style="<?php echo $style; ?>"<?php if ($greyscaleTriplet === $rgbValue) { ?> selected="selected"<?php } ?>><?php echo htmlspecialchars($rgbLabel, ENT_QUOTES, 'UTF-8'); ?></option>
                          <?php } ?>
                        </select>
                          </div>

                          <div class="magick-settings-actions">
                            <button type="submit" name="submit_action" value="save_settings" class="button"><?php echo TEXT_BX_IMAGE_MAGICK_ACTION_SAVE; ?></button>
                            <button type="button" class="button but_red js-magick-reset-form"><?php echo TEXT_BX_IMAGE_MAGICK_BUTTON_RESET; ?></button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php } ?>

                <!-- TAB 3: UPLOAD-AKTIONEN //-->
                <div id="tab-preview_file-left">
                  <?php
                  $searchPattern = $previewUploadDirectoryFS . '*.{jpg,jpeg,png,gif,webp,JPG,JPEG,PNG,GIF,WEBP}';
                  $files = glob($searchPattern, GLOB_BRACE);
                  $files = is_array($files) ? $files : array();
                  $initialOriginalPreviewSrc = '';
                  $initialOriginalPreviewName = '';

                  if (!empty($files)) {
                    usort($files, function($a, $b) {
                      return filemtime($b) <=> filemtime($a);
                    });

                    $firstFileName = basename((string)$files[0]);
                    $initialOriginalPreviewSrc = $previewUploadDirectoryWS . rawurlencode($firstFileName);
                    $initialOriginalPreviewName = $firstFileName;
                  }
                  ?>
                  <div class="magick-preview-workspace js-preview-file-workspace"
                       data-generated-preview-src="<?php echo htmlspecialchars((string)$previewGeneratedInfoImageWS, ENT_QUOTES, 'UTF-8'); ?>"
                       data-original-preview-src="<?php echo htmlspecialchars((string)$initialOriginalPreviewSrc, ENT_QUOTES, 'UTF-8'); ?>"
                       data-original-preview-name="<?php echo htmlspecialchars((string)$initialOriginalPreviewName, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="magick-preview-upload-panel">
                      <div class="main" style="padding: 4px 0;">
                        <?php echo TEXT_BX_IMAGE_MAGICK_PREVIEW_FILE_INTRO; ?>
                      </div>
                      <div class="main" style="margin-top: 8px;">
                        <?php echo xtc_draw_file_field('preview_original_file',  false, 'id="file-input" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp"'); ?>
                        <div style="margin-top: 8px;">
                          <button type="submit" name="submit_action" value="upload_preview" class="button"><?php echo TEXT_BX_IMAGE_MAGICK_PREVIEW_FILE_UPLOAD_BUTTON; ?></button>
                        </div>
                        <div style="margin-top: 8px; color: #666;">
                          <?php echo TEXT_BX_IMAGE_MAGICK_PREVIEW_FILE_ALLOWED_TYPES; ?>
                        </div>
                      </div>
                    </div>

                    <div class="magick-preview-main-panel">
                      <div class="magick-preview-stage" id="preview-file-stage" aria-live="polite">
                        <div class="magick-preview-stage-toolbar">
                          <strong><?php echo TEXT_BX_IMAGE_MAGICK_GALLERY_LIVE_PREVIEW_TITLE; ?></strong>
                          <div class="magick-preview-source-switch">
                            <button type="button" class="button js-preview-file-toggle is-active" data-preview-source="original">Original</button>
                            <button type="button" class="button js-preview-file-toggle" data-preview-source="generated">Generiert</button>
                          </div>
                        </div>
                        <div class="magick-preview-stage-canvas">
                          <img id="preview-file-stage-image" src="" alt="" hidden="hidden" />
                          <div id="preview-file-stage-empty" class="magick-preview-stage-empty"><?php echo TEXT_BX_IMAGE_MAGICK_GALLERY_EMPTY; ?></div>
                        </div>
                        <div id="preview-file-stage-caption" class="magick-preview-stage-caption"></div>
                      </div>

                      <div id="gallery-container" class="magick-gallery-panel">
                        <h3 style="margin-top: 0;"><?php echo TEXT_BX_IMAGE_MAGICK_GALLERY_TITLE; ?></h3>
                        <div class="magick-gallery-grid">
                          <?php
                          if (!empty($files)) {
                            foreach ($files as $file) {
                              $filename = basename((string)$file);
                              $imgUrl = $previewUploadDirectoryWS . rawurlencode($filename);
                              $escapedImgUrl = htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8');
                              $escapedFilename = htmlspecialchars($filename, ENT_QUOTES, 'UTF-8');
                              $deleteConfirmText = htmlspecialchars(TEXT_BX_IMAGE_MAGICK_GALLERY_DELETE_CONFIRM, ENT_QUOTES, 'UTF-8');

                              echo '<div class="magick-gallery-item js-preview-file-item" data-preview-src="' . $escapedImgUrl . '" data-preview-name="' . $escapedFilename . '">';
                              echo '<button type="button" class="magick-gallery-thumb" aria-label="' . $escapedFilename . '"><img src="' . $escapedImgUrl . '" alt="' . $escapedFilename . '" /></button>';
                              echo '<div class="magick-gallery-name">' . $escapedFilename . '</div>';
                              echo '<button type="submit" name="generate_preview_image" value="' . $escapedFilename . '" class="button magick-gallery-action">' . TEXT_BX_IMAGE_MAGICK_GALLERY_GENERATE_BUTTON . '</button>';
                              echo '<button type="submit" name="delete_preview_image" value="' . $escapedFilename . '" class="button but_red magick-gallery-action" onclick="return confirm(\'' . $deleteConfirmText . '\');">' . TEXT_BX_IMAGE_MAGICK_GALLERY_DELETE_BUTTON . '</button>';
                              echo '</div>';
                            }
                          } else {
                            echo '<p style="color: #666;">' . TEXT_BX_IMAGE_MAGICK_GALLERY_EMPTY . '</p>';
                          }
                          ?>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                </div>
                <!-- end tab-preview_file //-->

                <!-- TAB 4: SUPPORT-AKTIONEN //-->
                <div id="tab-support-left">
                  <div class="main" style="padding: 4px 0;">
                    <?php echo TEXT_BX_IMAGE_MAGICK_SUPPORT_INTRO; ?>
                  </div>
                  <div class="main" style="margin-top: 8px;">
                    <ul style="margin: 0 0 0 18px; padding: 0;">
                      <li><?php echo TEXT_BX_IMAGE_MAGICK_SUPPORT_HINT_1; ?></li>
                      <li><?php echo TEXT_BX_IMAGE_MAGICK_SUPPORT_HINT_2; ?></li>
                      <li><?php echo TEXT_BX_IMAGE_MAGICK_SUPPORT_HINT_3; ?></li>
                    </ul>
                  </div>
                </div>
                <!-- end tab-support //-->

              </div>
              </form>
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES.'footer.php'); ?>
<!-- footer_eof //-->

</body>
</html>
<?php require(DIR_WS_INCLUDES.'application_bottom.php'); ?>