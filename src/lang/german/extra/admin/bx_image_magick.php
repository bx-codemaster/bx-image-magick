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
define('TEXT_BX_IMAGE_MAGICK_TAB_PREVIEW_FILE', 'Aktuelle Vorschau-Datei');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_FILE_INTRO', 'Originalbild für die Vorschau hochladen.');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_FILE_UPLOAD_BUTTON', 'Upload starten');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_FILE_ALLOWED_TYPES', 'Erlaubt: JPG, JPEG, PNG, GIF, WebP.');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_LIVE_PREVIEW_TITLE', 'Ihre Auswahl (Vorschau):');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_TITLE', 'Hochgeladene Bilder:');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_EMPTY', 'Noch keine Bilder hochgeladen.');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_GENERATE_BUTTON', 'Vorschau erstellen');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_DELETE_BUTTON', 'Löschen');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_DELETE_CONFIRM', 'Bild wirklich löschen?');

define('TEXT_BX_IMAGE_MAGICK_DASHBOARD_INTRO', 'Hier können zentrale Bildverarbeitungsoptionen für das Modul verwaltet werden.');
define('TEXT_BX_IMAGE_MAGICK_FUNCTIONS_INTRO', 'Geplante Effektoptionen: bevel, greyscale, ellipse, round_edges, frame, drop_shadow, motion_blur.');
define('TEXT_BX_IMAGE_MAGICK_SUPPORT_INTRO', 'Support- und Diagnosefunktionen werden schrittweise ergänzt.');
define('TEXT_BX_IMAGE_MAGICK_IMAGE_TAB_INTRO', 'Konfiguriere Merge und Effekt-Optionen für diese Bildgröße.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_MERGE_STRING', 'Merge-String');
define('TEXT_BX_IMAGE_MAGICK_FIELD_MERGE_STRING_HINT', 'Dieses Feld definiert die Merge-Optionen für das gewählte Bildformat.<br>Standardwert: (overlay.gif,10,-50,60,FF0000).<br> Verwendung: (merge_image, x start [neg = von rechts], y start [neg = von unten], Deckkraft, Transparente Farbe im zusammengesetzten Bild).');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_TITLE', 'Merge-Positioner');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_OVERLAY', 'Overlay');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_OVERLAY_FILE', 'Overlay-Datei');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_OVERLAY_NONE', 'Kein Overlay');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_X', 'X');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_Y', 'Y');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_HINT', 'Position direkt per Drag-and-Drop oder mit den X/Y-Slidern setzen. Der Merge-String wird automatisch synchronisiert.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_ORDER', 'Effekt-Reihenfolge');
define('TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_ORDER_HINT', 'Dieses Feld wird automatisch aus den gewählten Effekten befüllt und definiert die auszuführende Effekt-Reihenfolge.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_PLACEHOLDER', 'Dieses Feld wird automatisch ausgefüllt.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_ROUND_EDGES', 'Round Edges Radius');
define('TEXT_BX_IMAGE_MAGICK_FIELD_DROP_SHADOW', 'Drop Shadow');
define('TEXT_BX_IMAGE_MAGICK_FIELD_DROP_SHADOW_FADE', 'Drop Shadow Fade');
define('TEXT_BX_IMAGE_MAGICK_FIELD_GREYSCALE', 'Greyscale (r,g,b)');
define('TEXT_BX_IMAGE_MAGICK_FIELD_GREYSCALE_HINT', 'Vordefinierte Gewichtungen für die Graustufenberechnung.');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_NONE', 'Aus');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_NEUTRAL', 'Neutral 33/33/33');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_STANDARD', 'Standard 30/59/11');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_GREEN_STRONG', 'Grünstark 21/72/7');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_PORTRAIT', 'Portrait 37/53/10');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_WARM', 'Warm 48/41/11');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_COOL', 'Kühl 24/47/29');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_RED_CHANNEL', 'Nur Rotkanal');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_GREEN_CHANNEL', 'Nur Grünkanal');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_BLUE_CHANNEL', 'Nur Blaukanal');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_CUSTOM', 'Benutzerdefiniert');
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
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_UPLOAD_DIR_NOT_WRITABLE', 'Upload-Verzeichnis ist nicht beschreibbar: %s');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_UPLOAD_SUCCESS', 'Originalbild wurde hochgeladen. Nächster Schritt: Vorschauen generieren.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_UPLOAD_FAILED', 'Kein gültiges Bild ausgewählt oder Upload fehlgeschlagen.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_NOT_FOUND', 'Das ausgewählte Bild wurde nicht gefunden.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_DELETED', 'Bild gelöscht: %s');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_DELETE_FAILED', 'Bild konnte nicht gelöscht werden.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_GENERATED', 'Vorschaubilder für %s wurden erstellt (%s Größen).');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_GENERATE_FAILED', 'Vorschaubilder konnten nicht erstellt werden.');

define('TEXT_BX_IMAGE_MAGICK_BUTTON_RESET', 'Zurücksetzen');