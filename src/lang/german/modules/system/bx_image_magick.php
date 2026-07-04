<?php
/**
 * Deutsche Sprachdatei für das Systemmodul bx_image_magick.
 * \lang\german\modules\system\bx_image_magick.php
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

define('MODULE_BX_IMAGE_MAGICK_TITLE', 'BX Image Magick - <span style="font-weight: normal;">Bildbearbeitung mit ImageMagick</span>');

$description = '
<details class="bxac-card">
	<summary class="bxac-summary" style="list-style: none; display: inline-flex; align-items: center; gap: 8px; width: 100%;">
    <span class="bxac-arrow" style="font-size: 2rem;">▸</span>
    <span class="bxac-title">' . xtc_image(DIR_WS_ICONS.'heading/bx_image_magick.png', 'BX Image Magick', '', '', 'style="max-height: 40px; vertical-align: middle; margin-right: 8px; cursor: pointer;"') . '<strong>BX Image Magick</strong></span>
  </summary>
  <div class="bxac-body">
    <h3 style="margin-top: 0;">Modul zur Bildbearbeitung mit ImageMagick</h3>
    <p>Ermöglicht die Erstellung und Bearbeitung von Bildern mit der ImageMagick Bibliothek inklusive ICC-Farbprofil-Unterstützung für CMYK- und RGB-Konvertierungen.</p>
		<h4>ICC-Farbprofile</h4>
		<table class="admin_table">
			<thead>
				<tr>
					<th style="vertical-align: top;">Profil</th>
					<th style="vertical-align: top;">Alter<br>(Stand 06/2026)</th>
					<th style="vertical-align: top;">Typischer Zweck</th>
					<th style="vertical-align: top;">Praktische Auswirkung</th>
					<th style="vertical-align: top;">Empfohlene Verwendung im Shop</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="vertical-align: top;">PSOcoated_v3.icc</td>
					<td style="vertical-align: top;">ca. 10–11 Jahre (Copyright 2015)</td>
					<td style="vertical-align: top;">Modernes CMYK-Quellprofil für gestrichenen Offsetdruck</td>
					<td style="vertical-align: top;">Meist neutralere, zeitgemäßere CMYK-Interpretation als ältere FOGRA39-Profile</td>
					<td style="vertical-align: top;"><strong>Default empfohlen</strong> für CMYK-Quellen in aktuellen Workflows</td>
				</tr>
				<tr>
					<td style="vertical-align: top;">CoatedFOGRA39.icc</td>
					<td style="vertical-align: top;">ca. 17–18 Jahre (Datei 2008, Beschreibung ISO 12647-2:2004)</td>
					<td style="vertical-align: top;">Älteres CMYK-Referenzprofil (Legacy/Fallback)</td>
					<td style="vertical-align: top;">Kann im Vergleich zu v3 etwas anders in Grauachse und Sättigung wirken; für ältere Workflows oft noch passend</td>
					<td style="vertical-align: top;"><strong>Fallback</strong> für Legacy-Druckdaten oder wenn v3 sichtbar abweicht</td>
				</tr>
				<tr>
					<td style="vertical-align: top;">sRGB2014.icc</td>
					<td style="vertical-align: top;">ca. 10–11 Jahre (Copyright 2015)</td>
					<td style="vertical-align: top;">RGB-Zielprofil für Web/Standarddarstellung</td>
					<td style="vertical-align: top;">Solider, aktueller sRGB-Output für Browser- und Shopbilder</td>
					<td style="vertical-align: top;"><strong>Default empfohlen</strong> als RGB-Zielprofil für den Live-Shop</td>
				</tr>
				<tr>
					<td style="vertical-align: top;">ColorMatchRGB.icc</td>
					<td style="vertical-align: top;">sehr alt (Copyright 2000, Datei 2008)</td>
					<td style="vertical-align: top;">Älteres RGB-Profil (Fallback)</td>
					<td style="vertical-align: top;">Für Web heute meist weniger ideal; kann zu abweichender Helligkeit/Farbwirkung führen</td>
					<td style="vertical-align: top;"><strong>Nicht als Default</strong>; nur als Fallback bei Altbeständen</td>
				</tr>
			</tbody>
		</table>
		<h5>Fazit:</h5>
		<p>Die aktuelle Default-Kombination PSOcoated_v3 → sRGB2014 ist sinnvoll und modern.
		Die Legacy-Profile CoatedFOGRA39 und ColorMatchRGB sind gute Rückfalloptionen, aber nicht erste Wahl für neue Setups.
		Der größte sichtbare Unterschied entsteht fast immer beim CMYK-Quellprofil; falsches Quellprofil führt schnell zu Farbstichen oder flauen Farben.</p>
  </div>
</details>';

if((!defined('MODULE_BX_IMAGE_MAGICK_STATUS')) || (MODULE_BX_IMAGE_MAGICK_STATUS != 'True') && basename($_SERVER['PHP_SELF']) == 'module_export.php') {
	$description .= '<p><a class="button btnbox but_red" style="text-align:center;" onclick="return confirmLink(\'Alle Dateien löschen?\', \'\' ,this);" href="'.xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=bx_image_magick&action=custom').'">Alle Moduldateien löschen</a></p>';
}

define('MODULE_BX_IMAGE_MAGICK_DESCRIPTION', $description);

define('MODULE_BX_IMAGE_MAGICK_STATUS_TITLE', 'Status');
define('MODULE_BX_IMAGE_MAGICK_STATUS_DESC', 'Modul aktivieren?');

define('MODULE_BX_IMAGE_MAGICK_AUTO_CREATE_ON_CONSTRUCT_TITLE', 'Automatisch Bilder erstellen bei Konstruktion');
define('MODULE_BX_IMAGE_MAGICK_AUTO_CREATE_ON_CONSTRUCT_DESC', 'Soll das Bild automatisch erstellt werden, wenn die Klasse instanziiert wird und die Quelldatei sowie die Zieldatei gültig sind? Diese Einstellung kann die Leistung beeinträchtigen, wenn viele Instanzen der Klasse erstellt werden, da jedes Mal eine Bildbearbeitung durchgeführt wird. Es wird empfohlen, diese Option nur zu aktivieren, wenn Sie sicher sind, dass die Bilder sofort bei der Konstruktion benötigt werden und die Anzahl der Instanzen überschaubar ist.');

define('IMAGEMANIPULATOR_ICC_PROFILE_CMYK_TITLE', 'ICC-Profil CMYK (Dateiname)');
define('IMAGEMANIPULATOR_ICC_PROFILE_CMYK_DESC', 'Dateiname des CMYK-Quellprofils im Verzeichnis admin/includes/classes/ICC/. Beispiel: PSOcoated_v3.icc');
define('IMAGEMANIPULATOR_ICC_PROFILE_RGB_TITLE', 'ICC-Profil RGB (Dateiname)');
define('IMAGEMANIPULATOR_ICC_PROFILE_RGB_DESC', 'Dateiname des RGB-Zielprofils im Verzeichnis admin/includes/classes/ICC/. Beispiel: sRGB2014.icc');

define('MODULE_BX_IMAGE_MAGICK_CONFIG_GROUP_ID_TITLE', 'Interne Konfigurationsgruppen-ID');
define('MODULE_BX_IMAGE_MAGICK_CONFIG_GROUP_ID_DESC', 'Interner technischer Wert für die Konfigurationsgruppe des Moduls. Nicht manuell ändern.');

define('PRODUCT_IMAGE_INFO_TRANSFORM_TITLE', 'Transform-String für Info-Bilder');
define('PRODUCT_IMAGE_INFO_TRANSFORM_DESC', 'Effekt-Reihenfolge für Info-Bilder, z. B. round_edges(4),drop_shadow(3).');
define('PRODUCT_IMAGE_MIDI_TRANSFORM_TITLE', 'Transform-String für Midi-Bilder');
define('PRODUCT_IMAGE_MIDI_TRANSFORM_DESC', 'Effekt-Reihenfolge für Midi-Bilder, z. B. round_edges(4),drop_shadow(3).');
define('PRODUCT_IMAGE_MINI_TRANSFORM_TITLE', 'Transform-String für Mini-Bilder');
define('PRODUCT_IMAGE_MINI_TRANSFORM_DESC', 'Effekt-Reihenfolge für Mini-Bilder, z. B. round_edges(4),drop_shadow(3).');
define('PRODUCT_IMAGE_POPUP_TRANSFORM_TITLE', 'Transform-String für Popup-Bilder');
define('PRODUCT_IMAGE_POPUP_TRANSFORM_DESC', 'Effekt-Reihenfolge für Popup-Bilder, z. B. round_edges(4),drop_shadow(3).');
define('PRODUCT_IMAGE_THUMBNAIL_TRANSFORM_TITLE', 'Transform-String für Thumbnail-Bilder');
define('PRODUCT_IMAGE_THUMBNAIL_TRANSFORM_DESC', 'Effekt-Reihenfolge für Thumbnail-Bilder, z. B. round_edges(4),drop_shadow(3).');

define('MODULE_BX_IMAGE_MAGICK_IMAGICK_ERROR', 'FEHLER! Modul <strong>' . constant('MODULE_BX_IMAGE_MAGICK_TITLE') . '</strong> kann nicht installiert werden, weil die Imagick Bibliothek fehlt!');

define('MODULE_BX_IMAGE_MAGICK_TEXT_COULD_NOT_BE_DELETED', 'FEHLER! Modul <strong>' . constant('MODULE_BX_IMAGE_MAGICK_TITLE') . '</strong> konnte nicht gelöscht werden.');
define('MODULE_BX_IMAGE_MAGICK_TEXT_SUCCESSFULLY_REMOVED', 'Erfolg! Modul <strong>' . constant('MODULE_BX_IMAGE_MAGICK_TITLE') . '</strong> wurde erfolgreich entfernt.');
define('MODULE_BX_IMAGE_MAGICK_TEXT_REMOVAL_INCOMPLETE', 'FEHLER! Modul <strong>' . constant('MODULE_BX_IMAGE_MAGICK_TITLE') . '</strong> konnte nicht vollständig entfernt werden.');
