<?php
/**
 * English language file for the bx_image_magick system module.
 * \lang\english\modules\system\bx_image_magick.php
 *
 * This file defines the English module texts for configuration,
 * installation instructions, and error messages in the modified admin area.
 *
 * @file        bx_image_magick.php
 * @package     bx-image-magick
 * @author      bx-codemaster (benax)
 * @website     www.bx-coding.de
 * @license     GNU General Public License (GPL)
 * @since       2026-06-10
 */

define('MODULE_BX_IMAGE_MAGICK_TITLE', 'BX Image Magick - <span style="font-weight: normal;">Image Editing with ImageMagick</span>');
define('MODULE_BX_IMAGE_MAGICK_DESCRIPTION', '
<details class="bxac-card">
	<summary class="bxac-summary" style="list-style: none; display: inline-flex; align-items: center; gap: 8px; width: 100%;">
    <span class="bxac-arrow" style="font-size: 2rem;">▸</span>
    <span class="bxac-title">' . xtc_image(DIR_WS_ICONS.'heading/bx_image_magick.png', 'BX Image Magick', '', '', 'style="max-height: 40px; vertical-align: middle; margin-right: 8px; cursor: pointer;"') . '<strong>BX Image Magick</strong></span>
  </summary>
  <div class="bxac-body">
    <h3 style="margin-top: 0;">Module for Image Editing with ImageMagick</h3>
    <p>Enables the creation and editing of images using the ImageMagick library, including ICC color profile support for CMYK and RGB conversions.</p>
		<h4>ICC Color Profiles</h4>
		<table class="admin_table">
			<thead>
				<tr>
					<th style="vertical-align: top;">Profile</th>
					<th style="vertical-align: top;">Age<br>(As of 06/2026)</th>
					<th style="vertical-align: top;">Typical Purpose</th>
					<th style="vertical-align: top;">Practical Impact</th>
					<th style="vertical-align: top;">Recommended Use in Shop</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="vertical-align: top;">PSOcoated_v3.icc</td>
					<td style="vertical-align: top;">ca. 10–11 years (Copyright 2015)</td>
					<td style="vertical-align: top;">Modern CMYK source profile for coated offset printing</td>
					<td style="vertical-align: top;">Generally provides a more neutral, contemporary CMYK interpretation than older FOGRA39 profiles</td>
					<td style="vertical-align: top;"><strong>Default recommended</strong> for CMYK sources in current workflows</td>
				</tr>
				<tr>
					<td style="vertical-align: top;">CoatedFOGRA39.icc</td>
					<td style="vertical-align: top;">ca. 17–18 years (File 2008, Description ISO 12647-2:2004)</td>
					<td style="vertical-align: top;">Older CMYK reference profile (Legacy/Fallback)</td>
					<td style="vertical-align: top;">May appear slightly different in gray axis and saturation compared to v3; often still suitable for older workflows</td>
					<td style="vertical-align: top;"><strong>Fallback</strong> for legacy print data or when v3 visibly differs</td>
				</tr>
				<tr>
					<td style="vertical-align: top;">sRGB2014.icc</td>
					<td style="vertical-align: top;">ca. 10–11 years (Copyright 2015)</td>
					<td style="vertical-align: top;">RGB target profile for web/standard display</td>
					<td style="vertical-align: top;">Solid, current sRGB output for browser and shop images</td>
					<td style="vertical-align: top;"><strong>Default recommended</strong> as RGB target profile for the live shop</td>
				</tr>
				<tr>
					<td style="vertical-align: top;">ColorMatchRGB.icc</td>
					<td style="vertical-align: top;">very old (Copyright 2000, file 2008)</td>
					<td style="vertical-align: top;">Older RGB profile (Fallback)</td>
					<td style="vertical-align: top;">For web today mostly less ideal; can lead to different brightness/color effects</td>
					<td style="vertical-align: top;"><strong>Not as Default</strong>; only as fallback for legacy assets</td>
				</tr>
			</tbody>
		</table>
		<h5>Conclusion:</h5>
		<p>The current default combination PSOcoated_v3 → sRGB2014 is sensible and modern.
		The legacy profiles CoatedFOGRA39 and ColorMatchRGB are good fallback options, but not the first choice for new setups.
		The most noticeable difference almost always occurs with the CMYK source profile; incorrect source profile quickly leads to color casts or dull colors.</p>
  </div>
</details>');

define('MODULE_BX_IMAGE_MAGICK_STATUS_TITLE', 'Status');
define('MODULE_BX_IMAGE_MAGICK_STATUS_DESC', 'Enable module?');

define('MODULE_BX_IMAGE_MAGICK_AUTO_CREATE_ON_CONSTRUCT_TITLE', 'Automatically create images on construction');
define('MODULE_BX_IMAGE_MAGICK_AUTO_CREATE_ON_CONSTRUCT_DESC', 'Should the image be automatically created when the class is instantiated and the source and target files are valid? This setting can impact performance if many instances of the class are created, as image processing will be performed each time. It is recommended to enable this option only if you are sure that the images are needed immediately upon construction and the number of instances is manageable.');

define('IMAGEMANIPULATOR_ICC_PROFILE_CMYK_TITLE', 'ICC Profile CMYK (Filename)');
define('IMAGEMANIPULATOR_ICC_PROFILE_CMYK_DESC', 'Filename of the CMYK source profile in the directory admin/includes/classes/ICC/. Example: PSOcoated_v3.icc');
define('IMAGEMANIPULATOR_ICC_PROFILE_RGB_TITLE', 'ICC Profile RGB (Filename)');
define('IMAGEMANIPULATOR_ICC_PROFILE_RGB_DESC', 'Filename of the RGB target profile in the directory admin/includes/classes/ICC/. Example: sRGB2014.icc');

define('MODULE_BX_IMAGE_MAGICK_CONFIG_GROUP_ID_TITLE', 'Internal configuration group ID');
define('MODULE_BX_IMAGE_MAGICK_CONFIG_GROUP_ID_DESC', 'Internal technical value for the module configuration group. Do not change manually.');

define('PRODUCT_IMAGE_INFO_TRANSFORM_TITLE', 'Transform string for info images');
define('PRODUCT_IMAGE_INFO_TRANSFORM_DESC', 'Effect order for info images, e.g. round_edges(4),drop_shadow(3).');
define('PRODUCT_IMAGE_MIDI_TRANSFORM_TITLE', 'Transform string for midi images');
define('PRODUCT_IMAGE_MIDI_TRANSFORM_DESC', 'Effect order for midi images, e.g. round_edges(4),drop_shadow(3).');
define('PRODUCT_IMAGE_MINI_TRANSFORM_TITLE', 'Transform string for mini images');
define('PRODUCT_IMAGE_MINI_TRANSFORM_DESC', 'Effect order for mini images, e.g. round_edges(4),drop_shadow(3).');
define('PRODUCT_IMAGE_POPUP_TRANSFORM_TITLE', 'Transform string for popup images');
define('PRODUCT_IMAGE_POPUP_TRANSFORM_DESC', 'Effect order for popup images, e.g. round_edges(4),drop_shadow(3).');
define('PRODUCT_IMAGE_THUMBNAIL_TRANSFORM_TITLE', 'Transform string for thumbnail images');
define('PRODUCT_IMAGE_THUMBNAIL_TRANSFORM_DESC', 'Effect order for thumbnail images, e.g. round_edges(4),drop_shadow(3).');

define('MODULE_BX_IMAGE_MAGICK_IMAGICK_ERROR', 'ERROR! Module <strong>' . constant('MODULE_BX_IMAGE_MAGICK_TITLE') . '</strong> cannot be installed because the Imagick library is missing!');
