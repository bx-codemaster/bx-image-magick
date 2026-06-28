<?php
/**
 * Deutsche Sprachdatei für das Systemmodul bx_image_magick.
 * \lang\german\extra\admin\bx_image_magick.php
 *
 * Diese Datei definiert die deutschen Modultexte für die Konfiguration,
 * Installationshinweise und Fehlermeldungen im modified-Adminbereich.
 *
 * @file        bx_image_magick.php
 * @package     bx-image-magick
 * @author      bx-codemaster (benax)
 * @website     www.bx-coding.de
 * @license     GNU General Public License (GPL)
 * @since       2026-06-10
 */
	
define('BX_IMAGE_MAGICK_TITLE', 'BX Image Magick');
define('BX_IMAGE_MAGICK_SHORT_DESCRIPTION', 'Modul zur Bildbearbeitung mit ImageMagick.');
define('BX_IMAGE_MAGICK_LONG_DESCRIPTION', 'Modul zur Bildbearbeitung mit ImageMagick, einschließlich Unterstützung von ICC-Farbprofilen für CMYK- und RGB-Konvertierungen.');

define('TEXT_BX_IMAGE_MAGICK_TAB_DASHBOARD', 'Dashboard');
define('TEXT_BX_IMAGE_MAGICK_TAB_FUNCTIONS', 'Funktionen');
define('TEXT_BX_IMAGE_MAGICK_TAB_SUPPORT', 'Support');
define('TEXT_BX_IMAGE_MAGICK_TAB_INFO', 'Info-Bilder');
define('TEXT_BX_IMAGE_MAGICK_TAB_MIDI', 'Midi-Bilder');
define('TEXT_BX_IMAGE_MAGICK_TAB_MINI', 'Mini-Bilder');
define('TEXT_BX_IMAGE_MAGICK_TAB_POPUP', 'Popup-Bilder');
define('TEXT_BX_IMAGE_MAGICK_TAB_THUMBNAIL', 'Thumbnail-Bilder');

define('TEXT_BX_IMAGE_MAGICK_DASHBOARD_INTRO', 'Hier können zentrale Bildverarbeitungsoptionen für das Modul verwaltet werden.');
define('TEXT_BX_IMAGE_MAGICK_FUNCTIONS_INTRO', 'Geplante Effektoptionen: bevel, greyscale, ellipse, round_edges, frame, drop_shadow, motion_blur.');
define('TEXT_BX_IMAGE_MAGICK_SUPPORT_INTRO', 'Support- und Diagnosefunktionen werden schrittweise ergänzt.');
define('TEXT_BX_IMAGE_MAGICK_IMAGE_TAB_INTRO', 'Konfiguriere Merge und Effekt-Optionen für diese Bildgröße.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_MERGE_STRING', 'Merge-String');
define('TEXT_BX_IMAGE_MAGICK_FIELD_MERGE_STRING_HINT', 'Dieses Feld definiert die Merge-Optionen für das gewählte Bildformat.<br>Standardwert: (overlay.gif,10,-50,60,FF0000).<br> Verwendung: (merge_image, x start [neg = von rechts], y start [neg = von unten], Deckkraft, Transparente Farbe im zusammengesetzten Bild).');
define('TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_ORDER', 'Effekt-Reihenfolge');
define('TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_ORDER_HINT', 'Dieses Feld wird automatisch aus den gewählten Effekten befüllt und definiert die auszuführende Effekt-Reihenfolge.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_PLACEHOLDER', 'Dieses Feld wird automatisch ausgefüllt.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_ROUND_EDGES', 'Round Edges Radius');
define('TEXT_BX_IMAGE_MAGICK_FIELD_DROP_SHADOW', 'Drop Shadow');
define('TEXT_BX_IMAGE_MAGICK_FIELD_DROP_SHADOW_FADE', 'Drop Shadow Fade');
define('TEXT_BX_IMAGE_MAGICK_FIELD_GREYSCALE', 'Greyscale (r,g,b)');
define('TEXT_BX_IMAGE_MAGICK_DROP_SHADOW_COLOR_HINT', 'Schattenfarbe definiert die Farbe des Schattenwurfs. Die Hintergrundfarbe wird nur bei JPG/JPEG als Füllfarbe für transparente Bereiche verwendet.');
define('TEXT_BX_IMAGE_MAGICK_DROP_SHADOW_FADE_HINT', 'Hinweis: Kleine Fade-Werte erzeugen einen kompakteren Schatten mit frühem Auslauf. Große Fade-Werte verlängern den Verlauf und lassen den Schatten weicher in die Transparenz übergehen.');
define('TEXT_BX_IMAGE_MAGICK_ACTION_SAVE', 'Speichern');
define('TEXT_BX_IMAGE_MAGICK_ACTION_PREVIEW', 'Vorschau erzeugen');
define('TEXT_BX_IMAGE_MAGICK_SUPPORT_HINT_1', 'Geplant: Live-Preview mit temporären Dateien (tmp).');
define('TEXT_BX_IMAGE_MAGICK_SUPPORT_HINT_2', 'Geplant: Testlauf für ausgewählte Bildgröße.');
define('TEXT_BX_IMAGE_MAGICK_SUPPORT_HINT_3', 'Geplant: Validierung der Effekt-Reihenfolge.');

define('TEXT_BX_IMAGE_MAGICK_QUICK_ACTIONS', 'Schnellaktionen');
define('TEXT_BX_IMAGE_MAGICK_MODULE_SETTINGS', 'Moduleinstellungen');
define('TEXT_BX_IMAGE_MAGICK_CONFIGURATION', 'Konfiguration');
define('TEXT_BX_IMAGE_MAGICK_IMAGE_PROCESSING', 'Bildverarbeitung');
define('TEXT_BX_IMAGE_MAGICK_RUN_IMAGE_PROCESSING', 'Bilder neu erzeugen');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_PANEL', 'Vorschau');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_HINT', 'Hier erscheint später die Live-Vorschau für den aktuell gewählten Größen-Tab.');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_PLACEHOLDER', 'Noch keine Vorschau geladen');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_SETTINGS_SAVED', 'BX Image Magick Einstellungen gespeichert.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_INVALID_TRANSFORMS_RESET', 'Ungültige Transform-Strings wurden geleert.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_IMAGE_SCALED_DOWN', 'Das Originalbild ist größer als die Anzeige und wurde herunterskaliert.');