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
require_once(DIR_FS_ADMIN . 'includes/extra/functions/bx_image_magick.php');

$imageSizeTabs = array(
  array(
    'id'              => 'info',
    'title'           => TEXT_BX_IMAGE_MAGICK_TAB_INFO,
    'merge_const'     => 'PRODUCT_IMAGE_INFO_MERGE',
    'transform_const' => 'PRODUCT_IMAGE_INFO_TRANSFORM',
  ),
  array(
    'id'              => 'midi',
    'title'           => TEXT_BX_IMAGE_MAGICK_TAB_MIDI,
    'merge_const'     => 'PRODUCT_IMAGE_MIDI_MERGE',
    'transform_const' => 'PRODUCT_IMAGE_MIDI_TRANSFORM',
  ),
  array(
    'id'              => 'mini',
    'title'           => TEXT_BX_IMAGE_MAGICK_TAB_MINI,
    'merge_const'     => 'PRODUCT_IMAGE_MINI_MERGE',
    'transform_const' => 'PRODUCT_IMAGE_MINI_TRANSFORM',
  ),
  array(
    'id'              => 'popup',
    'title'           => TEXT_BX_IMAGE_MAGICK_TAB_POPUP,
    'merge_const'     => 'PRODUCT_IMAGE_POPUP_MERGE',
    'transform_const' => 'PRODUCT_IMAGE_POPUP_TRANSFORM',
  ),
  array(
    'id'              => 'thumbnail',
    'title'           => TEXT_BX_IMAGE_MAGICK_TAB_THUMBNAIL,
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
    $greyscaleInputName = 'greyscale_' . $tab['id'];
    $roundEdgesInputName = 'round_edges_' . $tab['id'];
    $roundEdgesColorInputName = 'round_edges_color_' . $tab['id'];
    $dropShadowInputName = 'drop_shadow_' . $tab['id'];
    $dropShadowColorInputName = 'drop_shadow_color_' . $tab['id'];
    $dropShadowBgColorInputName = 'drop_shadow_bg_color_' . $tab['id'];

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
    $dropShadowFadeInputName = 'drop_shadow_fade_' . $tab['id'];
    $dropShadowFade = isset($_POST[$dropShadowFadeInputName]) ? (int)$_POST[$dropShadowFadeInputName] : 65;
    $dropShadowFade = max(20, min(100, $dropShadowFade));

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
                <li><a href="#tab-support"><span style="font-size: 14px;">🛠️</span> <?php echo TEXT_BX_IMAGE_MAGICK_TAB_SUPPORT; ?></a></li>
              </ul>

              <div class="tab-content">
                <?php foreach ($imageSizeTabs as $tab) { ?>
                  <div id="tab-<?php echo $tab['id']; ?>">
                    <div class="magick-settings-wrap">
                      <div class="main" style="padding: 2px 0 8px 0;">
                        <strong><?php echo $tab['title']; ?></strong>
                        <div style="margin-top: 4px; color: #666;">
                          <?php echo TEXT_BX_IMAGE_MAGICK_IMAGE_TAB_INTRO; ?>
                        </div>
                      </div>

                      <div class="magick-settings-grid">
                        <label><?php echo TEXT_BX_IMAGE_MAGICK_FIELD_MERGE_STRING; ?></label>
                        <input type="text" class="w100" name="merge_<?php echo $tab['id']; ?>" value="<?php echo htmlspecialchars(bx_imagemagick_const_value($tab['merge_const']), ENT_QUOTES, 'UTF-8'); ?>" />
                        <div>
                          Standard Wert: (overlay.gif,10,-50,60,FF0000)
                          Verwendung:
                          (merge image,x start [neg = from right],y start [neg = from base],opacity, transparent colour on merge image)
                        </div>

                        <label><?php echo TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_ORDER; ?></label>
                        <input type="text" class="w100" name="transform_<?php echo $tab['id']; ?>" value="<?php echo htmlspecialchars(bx_imagemagick_const_value($tab['transform_const']), ENT_QUOTES, 'UTF-8'); ?>" placeholder="round_edges(4),drop_shadow(3)" />
                        <div>Dieses Feld wird automatisch aus den gewählten Effekten befüllt und definiert die auszuführende Effekt-Reihenfolge.</div>

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
                            </tr>
                          </table>
                        </div>
                        <div><div class="current-range"><span class="range-current" data-for="round_edges_id_<?php echo $tab['id']; ?>"><?php echo $roundEdgesCurrentRadius; ?></span> px</div></div>

                        <?php
                          $dropShadowMin = 0;
                          $dropShadowMax = 50;
                          $dropShadowListId = 'tickmarks_drop_shadow_' . $tab['id'];
                          $dropShadowConfig = bx_imagemagick_extract_drop_shadow_values(bx_imagemagick_const_value($tab['transform_const']));
                          $dropShadowCurrentWidth = max($dropShadowMin, min($dropShadowMax, (int)$dropShadowConfig['width']));
                          $dropShadowCurrentColor = '#' . strtolower((string)$dropShadowConfig['shadow_color']);
                          $dropShadowCurrentBgColor = '#' . strtolower((string)$dropShadowConfig['background_color']);
                          $dropShadowCurrentFade = max(20, min(100, isset($dropShadowConfig['fade']) ? (int)$dropShadowConfig['fade'] : 65));
                          $dropShadowFadeListId = 'tickmarks_drop_shadow_fade_' . $tab['id'];
                        ?>
                        <label for="drop_shadow_id_<?php echo $tab['id']; ?>"><?php echo TEXT_BX_IMAGE_MAGICK_FIELD_DROP_SHADOW; ?></label>
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
                            </tr>
                          </table>
                        </div>
                        <div>
                          <div class="current-range"><span class="range-current" data-for="drop_shadow_id_<?php echo $tab['id']; ?>"><?php echo $dropShadowCurrentWidth; ?></span> px</div>
                          <div class="magick-inline-note"><?php echo TEXT_BX_IMAGE_MAGICK_DROP_SHADOW_COLOR_HINT; ?></div>
                        </div>

                        <label for="drop_shadow_fade_id_<?php echo $tab['id']; ?>"><?php echo TEXT_BX_IMAGE_MAGICK_FIELD_DROP_SHADOW_FADE; ?></label>
                        <div class="range-control">
                          <table class="range-table" cellspacing="0" cellpadding="0">
                            <tr>
                              <td class="range-table-slider">
                                <input type="range" min="20" max="100" id="drop_shadow_fade_id_<?php echo $tab['id']; ?>" name="drop_shadow_fade_<?php echo $tab['id']; ?>" value="<?php echo $dropShadowCurrentFade; ?>" list="<?php echo $dropShadowFadeListId; ?>" />
                                <datalist id="<?php echo $dropShadowFadeListId; ?>">
                                  <?php for ($i = 20; $i <= 100; $i++) { ?>
                                    <option value="<?php echo $i; ?>"<?php if ($i === 20 || $i === 100) { ?> label="<?php echo $i; ?>"<?php } ?>></option>
                                  <?php } ?>
                                </datalist>
                                <div class="range-minmax">
                                  <span>20</span>
                                  <span>100</span>
                                </div>
                              </td>
                            </tr>
                          </table>
                        </div>
                        <div>
                          <div class="current-range"><span class="range-current" data-for="drop_shadow_fade_id_<?php echo $tab['id']; ?>"><?php echo $dropShadowCurrentFade; ?></span> %</div>
                          <div class="magick-inline-note"><?php echo TEXT_BX_IMAGE_MAGICK_DROP_SHADOW_FADE_HINT; ?></div>
                        </div>

                        <?php
                          $greyscaleTriplet = bx_imagemagick_extract_greyscale_triplet(bx_imagemagick_const_value($tab['transform_const']));
                          $rgb_greyscale_array = array(
                            'none',
                            '8,8,8',
                            '16,16,16',
                            '24,24,24',
                            '32,32,32',
                            '40,40,40',
                            '48,48,48',
                            '56,56,56',
                            '64,64,64',
                            '72,72,72',
                            '80,80,80',
                            '88,88,88',
                            '96,96,96',
                            '104,104,104',
                            '112,112,112',
                            '120,120,120',
                            '128,128,128',
                            '136,136,136',
                            '144,144,144',
                            '152,152,152',
                            '160,160,160',
                            '168,168,168',
                            '176,176,176',
                            '184,184,184',
                            '192,192,192',
                            '200,200,200',
                            '208,208,208',
                            '216,216,216',
                            '224,224,224',
                            '232,232,232',
                            '240,240,240',
                            '248,248,248',
                          );
                          if (!in_array($greyscaleTriplet, $rgb_greyscale_array, true)) {
                            $rgb_greyscale_array[] = $greyscaleTriplet;
                          }
                        ?>
                        <label for="greyscale_id_<?php echo $tab['id']; ?>"><?php echo TEXT_BX_IMAGE_MAGICK_FIELD_GREYSCALE; ?></label>
                        <select id="greyscale_id_<?php echo $tab['id']; ?>" name="greyscale_<?php echo $tab['id']; ?>">
                          <?php foreach ($rgb_greyscale_array as $rgbValue) {
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
                            <option value="<?php echo htmlspecialchars($rgbValue, ENT_QUOTES, 'UTF-8'); ?>" style="<?php echo $style; ?>"<?php if ($greyscaleTriplet === $rgbValue) { ?> selected="selected"<?php } ?>><?php echo htmlspecialchars($rgbValue, ENT_QUOTES, 'UTF-8'); ?></option>
                          <?php } ?>
                        </select>
                        <div>Vordefinierte Graustufen (RGB) als Auswahl.</div>
                      </div>

                      <div class="magick-settings-actions">
                        <button type="submit" class="button"><?php echo TEXT_BX_IMAGE_MAGICK_ACTION_SAVE; ?></button>
                        <a href="#" class="button but_green"><?php echo TEXT_BX_IMAGE_MAGICK_ACTION_PREVIEW; ?></a>
                      </div>
                    </div>
                  </div>
                <?php } ?>

                <!-- TAB 3: SUPPORT-AKTIONEN //-->
                <div id="tab-support">
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
          </td>
          <td class="boxRight">
<?php

  $heading  = array();
  $contents = array();

  $heading[]  = array('text' => '<strong>🧪 ' . TEXT_BX_IMAGE_MAGICK_PREVIEW_PANEL . '</strong>');
  $contents[] = array('text' => '<div class="main">' . TEXT_BX_IMAGE_MAGICK_PREVIEW_HINT . '</div>
                                <div class="magick-preview-placeholder">' . TEXT_BX_IMAGE_MAGICK_PREVIEW_PLACEHOLDER . '</div>');

  $heading[]  = array('text' => '<strong>⚡ ' . TEXT_BX_IMAGE_MAGICK_QUICK_ACTIONS . '</strong>');
  $contents[] = array('text' => '<strong>' . TEXT_BX_IMAGE_MAGICK_MODULE_SETTINGS . '</strong><br>
                                <a href="'.xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=bx_image_magick&action=edit').'" class="button but_green" style="line-height: 24px; padding: 6px 15px 6px 10px; min-width: 105px;"><span style="font-size: 18px; vertical-align: middle;">⚙️</span> ' . TEXT_BX_IMAGE_MAGICK_CONFIGURATION . '</a>');
  $contents[] = array('text' => '<strong>' . TEXT_BX_IMAGE_MAGICK_IMAGE_PROCESSING . '</strong><br>
                                <a href="'.xtc_href_link(FILENAME_IMAGEMANIPULATOR).'" class="button but_green" style="line-height: 24px; padding: 6px 15px 6px 10px; min-width: 105px;"><span style="font-size: 18px; vertical-align: middle;">🖼️</span> ' . TEXT_BX_IMAGE_MAGICK_RUN_IMAGE_PROCESSING . '</a>');
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