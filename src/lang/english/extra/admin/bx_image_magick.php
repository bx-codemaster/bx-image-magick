<?php
/**
 * English language file for the bx_image_magick system module.
 * \lang\english\extra\admin\bx_image_magick.php
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

define('BX_IMAGE_MAGICK_TITLE', 'BX Image Magick');
define('BX_IMAGE_MAGICK_SHORT_DESCRIPTION', 'Module for image editing with ImageMagick.');
define('BX_IMAGE_MAGICK_LONG_DESCRIPTION', 'Module for image editing with ImageMagick, including ICC color profile support for CMYK and RGB conversions.');

define('TEXT_BX_IMAGE_MAGICK_TAB_DASHBOARD', 'Dashboard');
define('TEXT_BX_IMAGE_MAGICK_TAB_FUNCTIONS', 'Functions');
define('TEXT_BX_IMAGE_MAGICK_TAB_SUPPORT', 'Support');
define('TEXT_BX_IMAGE_MAGICK_TAB_INFO', 'Info Images');
define('TEXT_BX_IMAGE_MAGICK_TAB_MIDI', 'Midi Images');
define('TEXT_BX_IMAGE_MAGICK_TAB_MINI', 'Mini Images');
define('TEXT_BX_IMAGE_MAGICK_TAB_POPUP', 'Popup Images');
define('TEXT_BX_IMAGE_MAGICK_TAB_THUMBNAIL', 'Thumbnail Images');
define('TEXT_BX_IMAGE_MAGICK_TAB_PREVIEW_FILE', 'Current Preview File');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_FILE_INTRO', 'Upload original image for preview.');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_FILE_UPLOAD_BUTTON', 'Start upload');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_FILE_ALLOWED_TYPES', 'Allowed: JPG, JPEG, PNG, GIF, WebP.');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_LIVE_PREVIEW_TITLE', 'Your selection (preview):');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_TITLE', 'Uploaded images:');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_EMPTY', 'No images uploaded yet.');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_GENERATE_BUTTON', 'Generate preview');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_DELETE_BUTTON', 'Delete');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_DELETE_CONFIRM', 'Delete this image?');

define('TEXT_BX_IMAGE_MAGICK_DASHBOARD_INTRO', 'Central image processing options for this module can be managed here.');
define('TEXT_BX_IMAGE_MAGICK_FUNCTIONS_INTRO', 'Planned effect options: bevel, greyscale, ellipse, round_edges, frame, drop_shadow, motion_blur.');
define('TEXT_BX_IMAGE_MAGICK_SUPPORT_INTRO', 'Support and diagnostic functions will be added step by step.');
define('TEXT_BX_IMAGE_MAGICK_IMAGE_TAB_INTRO', 'Configure merge and effect options for this image size.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_MERGE_STRING', 'Merge string');
define('TEXT_BX_IMAGE_MAGICK_FIELD_MERGE_STRING_HINT', 'This field defines the merge options for the selected image format.<br>Default value: (overlay.gif,10,-50,60,FF0000).<br>Usage: (merge_image, x start [neg = from right], y start [neg = from bottom], opacity, transparent color in the merged image).');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_TITLE', 'Merge positioner');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_OVERLAY', 'Overlay');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_OVERLAY_FILE', 'Overlay file');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_OVERLAY_NONE', 'No overlay');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_X', 'X');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_Y', 'Y');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_HINT', 'Set position via drag-and-drop or with X/Y sliders. The merge string is synchronized automatically.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_ORDER', 'Effect order');
define('TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_ORDER_HINT', 'This field is automatically populated from the selected effects and defines the order in which the effects will be applied.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_PLACEHOLDER', 'This field is automatically populated.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_ROUND_EDGES', 'Round edges radius');
define('TEXT_BX_IMAGE_MAGICK_FIELD_DROP_SHADOW', 'Drop shadow');
define('TEXT_BX_IMAGE_MAGICK_FIELD_DROP_SHADOW_FADE', 'Drop shadow fade');
define('TEXT_BX_IMAGE_MAGICK_FIELD_GREYSCALE', 'Greyscale (r,g,b)');
define('TEXT_BX_IMAGE_MAGICK_FIELD_GREYSCALE_HINT', 'Predefined weights for greyscale calculation.');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_NONE', 'Off');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_NEUTRAL', 'Neutral 33/33/33');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_STANDARD', 'Standard 30/59/11');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_GREEN_STRONG', 'Green strong 21/72/7');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_PORTRAIT', 'Portrait 37/53/10');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_WARM', 'Warm 48/41/11');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_COOL', 'Cool 24/47/29');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_RED_CHANNEL', 'Red channel only');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_GREEN_CHANNEL', 'Green channel only');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_BLUE_CHANNEL', 'Blue channel only');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_CUSTOM', 'Custom');
define('TEXT_BX_IMAGE_MAGICK_DROP_SHADOW_COLOR_HINT', 'Shadow color defines the shadow itself. Background color is only used for JPG/JPEG as the fill color for transparent areas.');
define('TEXT_BX_IMAGE_MAGICK_DROP_SHADOW_FADE_HINT', 'Note: Smaller fade values create a more compact shadow with an earlier falloff. Larger fade values extend the gradient and let the shadow transition more softly into transparency.');
define('TEXT_BX_IMAGE_MAGICK_ACTION_SAVE', 'Save');
define('TEXT_BX_IMAGE_MAGICK_ACTION_PREVIEW', 'Generate preview');
define('TEXT_BX_IMAGE_MAGICK_SUPPORT_HINT_1', 'Planned: live preview with temporary files (tmp).');
define('TEXT_BX_IMAGE_MAGICK_SUPPORT_HINT_2', 'Planned: test run for selected image size.');
define('TEXT_BX_IMAGE_MAGICK_SUPPORT_HINT_3', 'Planned: validation of effect order.');

define('TEXT_BX_IMAGE_MAGICK_QUICK_ACTIONS', 'Quick actions');
define('TEXT_BX_IMAGE_MAGICK_MODULE_SETTINGS', 'Module settings');
define('TEXT_BX_IMAGE_MAGICK_CONFIGURATION', 'Configuration');
define('TEXT_BX_IMAGE_MAGICK_IMAGE_PROCESSING', 'Image processing');
define('TEXT_BX_IMAGE_MAGICK_RUN_IMAGE_PROCESSING', 'Regenerate images');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_PANEL', 'Preview');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_HINT', 'The live preview for the currently selected size tab will appear here later.');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_PLACEHOLDER', 'No preview loaded yet');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_SETTINGS_SAVED', 'BX Image Magick settings saved.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_INVALID_TRANSFORMS_RESET', 'Invalid transform strings were cleared.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_IMAGE_SCALED_DOWN', 'The original image is larger than the display and has been scaled down.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_UPLOAD_DIR_NOT_WRITABLE', 'Upload directory is not writable: %s');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_UPLOAD_SUCCESS', 'Original image uploaded. Next step: generate previews.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_UPLOAD_FAILED', 'No valid image selected or upload failed.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_NOT_FOUND', 'The selected image was not found.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_DELETED', 'Image deleted: %s');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_DELETE_FAILED', 'Image could not be deleted.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_GENERATED', 'Preview images created for %s (%s sizes).');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_GENERATE_FAILED', 'Preview images could not be created.');
define('TEXT_BX_IMAGE_MAGICK_BUTTON_RESET', 'Reset');