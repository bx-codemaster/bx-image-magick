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
 * Unter Mitwirkung von CADDY entwickelt
 * CADDY: Computer-Aided Development & Deployment Yield (AI)
 */

require ('includes/application_top.php');

$imageSizeTabs = array(
  array(
    'id'              => 'info',
    'title'           => bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_TAB_INFO', 'Info-Bilder'),
    'merge_const'     => 'PRODUCT_IMAGE_INFO_MERGE',
    'transform_const' => 'PRODUCT_IMAGE_INFO_TRANSFORM',
  ),
  array(
    'id'              => 'midi',
    'title'           => bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_TAB_MIDI', 'Midi-Bilder'),
    'merge_const'     => 'PRODUCT_IMAGE_MIDI_MERGE',
    'transform_const' => 'PRODUCT_IMAGE_MIDI_TRANSFORM',
  ),
  array(
    'id'              => 'mini',
    'title'           => bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_TAB_MINI', 'Mini-Bilder'),
    'merge_const'     => 'PRODUCT_IMAGE_MINI_MERGE',
    'transform_const' => 'PRODUCT_IMAGE_MINI_TRANSFORM',
  ),
  array(
    'id'              => 'popup',
    'title'           => bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_TAB_POPUP', 'Popup-Bilder'),
    'merge_const'     => 'PRODUCT_IMAGE_POPUP_MERGE',
    'transform_const' => 'PRODUCT_IMAGE_POPUP_TRANSFORM',
  ),
  array(
    'id'              => 'thumbnail',
    'title'           => bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_TAB_THUMBNAIL', 'Thumbnail-Bilder'),
    'merge_const'     => 'PRODUCT_IMAGE_THUMBNAIL_MERGE',
    'transform_const' => 'PRODUCT_IMAGE_THUMBNAIL_TRANSFORM',
  ),
);

if (isset($_GET['action']) && $_GET['action'] === 'save' && isset($_POST['save_image_magick_settings'])) {
  $updated = 0;
  $skipped = 0;

  foreach ($imageSizeTabs as $tab) {
    $mergeKey = $tab['merge_const'];
    $transformKey = $tab['transform_const'];

    $mergeInputName = 'merge_' . $tab['id'];
    $transformInputName = 'transform_' . $tab['id'];

    $mergeValue = isset($_POST[$mergeInputName]) ? trim((string)$_POST[$mergeInputName]) : '';
    if (strlen($mergeValue) > 512) {
      $mergeValue = substr($mergeValue, 0, 512);
    }

    $transformRaw = isset($_POST[$transformInputName]) ? (string)$_POST[$transformInputName] : '';
    $transformValue = bx_imagemagick_normalize_transform($transformRaw);
    if ($transformRaw !== '' && $transformValue === '') {
      $skipped++;
    }

    if (bx_imagemagick_save_configuration($mergeKey, $mergeValue)) {
      $updated++;
    }

    if (bx_imagemagick_save_configuration($transformKey, $transformValue)) {
      $updated++;
    }
  }

  if ($updated > 0) {
    $messageStack->add_session('BX Image Magick Einstellungen gespeichert.', 'success');
  }
  if ($skipped > 0) {
    $messageStack->add_session('Ungültige Transform-Strings wurden geleert.', 'warning');
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
        <?php echo xtc_image(DIR_WS_ICONS.'heading/bx_image_magick.png', BX_IMAGE_MAGICK_TITLE, '', '', 'style="height: 32px;"'); ?>
      </div>
      <div class="pageHeading flt-l">
        <?php echo BX_IMAGE_MAGICK_TITLE; ?>
        <div class="main pdg2">
          <?php echo BX_IMAGE_MAGICK_DESCRIPTION; ?>
        </div>
      </div>
      <div class="clear"></div>

      <table class="tableCenter" style="margin-top: 5px;">
        <tr>
          <td class="boxCenterLeft">
            <div class="main" style="display: flex; flex-direction: row; justify-content: left; align-items: center; background: #AF417E; color: #ffffff; border-radius: 4px; margin: 0 0 5px 0; padding: 4px 0 2px 0;">
              <div class="main" style="margin: 5px 10px;"><strong><span style="font-size: 1.5em;">🔑</span> <?php echo BX_IMAGE_MAGICK_DESCRIPTION; ?></strong></div>
            </div>
            
            <div class="magick-tabs">
              <?php echo xtc_draw_form('bx_image_magick_settings', FILENAME_BX_IMAGE_MAGICK, 'action=save', 'post'); ?>
              <input type="hidden" name="save_image_magick_settings" value="1" />
              <ul class="tab-nav">
                <?php foreach ($imageSizeTabs as $tab) { ?>
                  <li>
                    <a href="#tab-<?php echo $tab['id']; ?>">
                      <span style="font-size: 14px;">🖼️</span> <?php echo $tab['title']; ?>
                    </a>
                  </li>
                <?php } ?>
                <li><a href="#tab-support"><span style="font-size: 14px;">🛠️</span> <?php echo bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_TAB_SUPPORT', 'Support'); ?></a></li>
              </ul>

              <div class="tab-content">
                <?php foreach ($imageSizeTabs as $tab) { ?>
                  <div id="tab-<?php echo $tab['id']; ?>">
                    <div class="magick-settings-wrap">
                      <div class="main" style="padding: 2px 0 8px 0;">
                        <strong><?php echo $tab['title']; ?></strong>
                        <div style="margin-top: 4px; color: #666;">
                          <?php echo bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_IMAGE_TAB_INTRO', 'Konfiguriere Merge und Effekt-Optionen für diese Bildgröße.'); ?>
                        </div>
                      </div>

                      <div class="magick-settings-grid">
                        <label><?php echo bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_FIELD_MERGE_STRING', 'Merge-String'); ?></label>
                        <input type="text" class="w100" name="merge_<?php echo $tab['id']; ?>" value="<?php echo htmlspecialchars(bx_imagemagick_const_value($tab['merge_const']), ENT_QUOTES, 'UTF-8'); ?>" />

                        <label><?php echo bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_ORDER', 'Effekt-Reihenfolge (Transform-String)'); ?></label>
                        <input type="text" class="w100" name="transform_<?php echo $tab['id']; ?>" value="<?php echo htmlspecialchars(bx_imagemagick_const_value($tab['transform_const']), ENT_QUOTES, 'UTF-8'); ?>" placeholder="round_edges(4),drop_shadow(3)" />

                        <label><?php echo bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_FIELD_ROUND_EDGES', 'Round Edges Radius'); ?></label>
                        <input type="text" name="round_edges_<?php echo $tab['id']; ?>" value="3" />

                        <label><?php echo bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_FIELD_DROP_SHADOW', 'Drop Shadow Width'); ?></label>
                        <input type="text" name="drop_shadow_<?php echo $tab['id']; ?>" value="3" />

                        <label><?php echo bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_FIELD_GREYSCALE', 'Greyscale (r,g,b)'); ?></label>
                        <input type="text" name="greyscale_<?php echo $tab['id']; ?>" value="38,36,26" />
                      </div>

                      <div class="magick-settings-actions">
                        <button type="submit" class="button"><?php echo bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_ACTION_SAVE', 'Speichern'); ?></button>
                        <a href="#" class="button but_green"><?php echo bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_ACTION_PREVIEW', 'Vorschau erzeugen'); ?></a>
                      </div>
                    </div>
                  </div>
                <?php } ?>

                <!-- TAB 3: SUPPORT-AKTIONEN //-->
                <div id="tab-support">
                  <div class="main" style="padding: 4px 0;">
                    <?php echo bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_SUPPORT_INTRO', 'Support- und Diagnosefunktionen werden schrittweise ergänzt.'); ?>
                  </div>
                  <div class="main" style="margin-top: 8px;">
                    <ul style="margin: 0 0 0 18px; padding: 0;">
                      <li><?php echo bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_SUPPORT_HINT_1', 'Geplant: Live-Preview mit temporären Dateien (tmp).'); ?></li>
                      <li><?php echo bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_SUPPORT_HINT_2', 'Geplant: Testlauf für ausgewählte Bildgröße.'); ?></li>
                      <li><?php echo bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_SUPPORT_HINT_3', 'Geplant: Validierung der Effekt-Reihenfolge.'); ?></li>
                    </ul>
                  </div>
                </div>
                <!-- end tab-support //-->

              </div>
              </form>
            </div>

          </td>
          <td class="boxRight">
<?php

  $heading  = array();
  $contents = array();

  $heading[]  = array('text' => '<strong>🧪 ' . bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_PREVIEW_PANEL', 'Vorschau') . '</strong>');
  $contents[] = array('text' => '<div class="main">' . bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_PREVIEW_HINT', 'Hier erscheint später die Live-Vorschau für den aktuell gewählten Größen-Tab.') . '</div>
                                <div class="magick-preview-placeholder">' . bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_PREVIEW_PLACEHOLDER', 'Noch keine Vorschau geladen') . '</div>');

  $heading[]  = array('text' => '<strong>⚡ ' . bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_QUICK_ACTIONS', 'Schnellaktionen') . '</strong>');
  $contents[] = array('text' => '<strong>' . bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_MODULE_SETTINGS', 'Moduleinstellungen') . '</strong><br>
                                <a href="'.xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=bx_image_magick&action=edit').'" class="button but_green" style="line-height: 24px; padding: 6px 15px 6px 10px; min-width: 105px;"><span style="font-size: 18px; vertical-align: middle;">⚙️</span> ' . bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_CONFIGURATION', 'Konfiguration') . '</a>');
  $contents[] = array('text' => '<strong>' . bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_IMAGE_PROCESSING', 'Bildverarbeitung') . '</strong><br>
                                <a href="'.xtc_href_link(FILENAME_IMAGEMANIPULATOR).'" class="button but_green" style="line-height: 24px; padding: 6px 15px 6px 10px; min-width: 105px;"><span style="font-size: 18px; vertical-align: middle;">🖼️</span> ' . bx_imagemagick_text('TEXT_BX_IMAGE_MAGICK_RUN_IMAGE_PROCESSING', 'Bilder neu erzeugen') . '</a>');
  if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
    $box = new box;
    echo $box->infoBox($heading, $contents);
  }
?>
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