<?php
/**
 * Hilfsfunktionen für die Admin-Konfiguration von bx_image_magick.
 *
 * Diese Datei bündelt Parser-, Normalisierungs- und Persistenzhelfer für
 * Transform-Strings der Bildbearbeitung im Adminbereich. Die Funktionen sind
 * bewusst zustandslos gehalten, damit sie sowohl in Modulquellen als auch in
 * der gespiegelten Live-Datei identisch verwendet werden können.
 *
 * @file        bx_image_magick.php
 * @package     bx-image-magick
 * @author      bx-codemaster (benax)
 * @website     www.bx-coding.de
 * @license     GNU General Public License (GPL)
 * @since       2026-06-10
 */

/**
 * Liest den Wert einer definierten Konstante und gibt ihn als String zurück.
 *
 * Die Funktion wird als kleiner Adapter für dynamische Konfigurationsschlüssel
 * verwendet, wenn Konstantennamen erst zur Laufzeit feststehen.
 *
 * @param string $key Name der bereits definierten Konstante.
 * @return string String-Repräsentation des Konstantenwerts.
 */
function bx_imagemagick_const_value(string $key): string {
	return (string)constant($key);
}

/**
 * Bereinigt und validiert einen kompletten Transform-String für die Admin-UI.
 *
 * Leere Eingaben werden verworfen, die Länge wird auf 512 Zeichen begrenzt und
 * es bleiben nur Zeichen erhalten, die für den internen Transform-Parser
 * relevant sind. Enthält der String andere Zeichen, wird ein leerer String
 * zurückgegeben, um ungültige Konfigurationen nicht weiterzuverarbeiten.
 *
 * @param string $value Rohwert aus Formular, Konfiguration oder Vorschau.
 * @return string Validierter Transform-String oder ein leerer String.
 */
function bx_imagemagick_normalize_transform(string $value): string {
	$value = trim($value);
	if ($value === '') {
		return '';
	}

	if (strlen($value) > 512) {
		$value = substr($value, 0, 512);
	}

	// Erlaubt nur Zeichen, die fuer den Transform-Parser relevant sind.
	if (!preg_match('/^[a-zA-Z0-9_(),.\-\s#\'\"]+$/', $value)) {
		return '';
	}

	return $value;
}

/**
 * Normalisiert einen Greyscale-Tripelwert im Format r,g,b.
 *
 * Erlaubt entweder den Spezialwert none oder drei Ganzzahlen, die anschließend
 * auf den Bereich 0 bis 255 begrenzt werden. Ungültige Eingaben werden mit
 * einem leeren String quittiert.
 *
 * @param string $value Eingabewert wie 120,140,160 oder none.
 * @return string Normalisierter Tripelwert, none oder ein leerer String.
 */
function bx_imagemagick_normalize_greyscale_triplet(string $value): string {
	$value = trim($value);
	if ($value === '') {
		return '';
	}

	if (strcasecmp($value, 'none') === 0) {
		return 'none';
	}

	if (!preg_match('/^\s*(-?\d+)\s*,\s*(-?\d+)\s*,\s*(-?\d+)\s*$/', $value, $matches)) {
		return '';
	}

	$r = max(0, min(255, (int)$matches[1]));
	$g = max(0, min(255, (int)$matches[2]));
	$b = max(0, min(255, (int)$matches[3]));

	return $r . ',' . $g . ',' . $b;
}

/**
 * Normalisiert eine Hex-Farbe auf das Format RRGGBB ohne führendes #.
 *
 * Drei- und sechsstellige Farbwerte werden akzeptiert. Dreistellige Werte
 * werden auf sechs Stellen expandiert. Bei leerer oder ungültiger Eingabe wird
 * der angegebene Fallback unverändert zurückgegeben.
 *
 * @param string $value Rohwert der Farbe, optional mit führendem #.
 * @param string $fallback Rückgabewert bei ungültiger Eingabe.
 * @return string Normalisierte Hex-Farbe oder der Fallback.
 */
function bx_imagemagick_normalize_hex_color(string $value, string $fallback = ''): string {
	$value = trim($value);
	if ($value === '') {
		return $fallback;
	}

	$hex = strtoupper(ltrim($value, '#'));
	if (strlen($hex) === 3) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	if (!preg_match('/^[0-9A-F]{6}$/', $hex)) {
		return $fallback;
	}

	return $hex;
}

/**
 * Trennt einen Transform-String an Top-Level-Kommas in einzelne Tokens.
 *
 * Kommas innerhalb von Klammern oder innerhalb quotierter Strings bleiben
 * erhalten. Dadurch können verschachtelte Effekte oder Stringargumente sicher
 * zerlegt werden, ohne die syntaktische Struktur zu beschädigen.
 *
 * @param string $value Kommagetrennter Transform-String.
 * @return array<int, string> Liste der obersten Transform-Tokens ohne Leerwerte.
 */
function bx_imagemagick_split_top_level(string $value): array {
	$parts = array();
	$buffer = '';
	$depth = 0;
	$quote = null;
	$length = strlen((string)$value);

	for ($i = 0; $i < $length; $i++) {
		$char = $value[$i];
		if ($quote !== null) {
			if ($char === '\\' && $i + 1 < $length) {
				$buffer .= $char . $value[$i + 1];
				$i++;
				continue;
			}
			if ($char === $quote) {
				$quote = null;
			}
			$buffer .= $char;
			continue;
		}

		if ($char === '\'' || $char === '"') {
			$quote = $char;
			$buffer .= $char;
			continue;
		}

		if ($char === '(') {
			$depth++;
			$buffer .= $char;
			continue;
		}

		if ($char === ')') {
			$depth = max(0, $depth - 1);
			$buffer .= $char;
			continue;
		}

		if ($char === ',' && $depth === 0) {
			$token = trim($buffer);
			if ($token !== '') {
				$parts[] = $token;
			}
			$buffer = '';
			continue;
		}

		$buffer .= $char;
	}

	$token = trim($buffer);
	if ($token !== '') {
		$parts[] = $token;
	}

	return $parts;
}

/**
 * Fügt einen Greyscale-Effekt in einen Transform-String ein oder ersetzt ihn.
 *
 * Vorhandene greyscale()-Einträge werden entfernt und durch den neuen Wert an
 * derselben Position ersetzt. Die Spezialwerte none und 0,0,0 entfernen den
 * Effekt vollständig aus der Transform-Kette.
 *
 * @param string $transform Bestehender Transform-String.
 * @param string $triplet Neuer Greyscale-Tripelwert oder none.
 * @return string Aktualisierter Transform-String ohne Duplikate.
 */
function bx_imagemagick_upsert_greyscale_transform(string $transform, string $triplet): string {
	if (trim($triplet) === '0,0,0' || strcasecmp(trim($triplet), 'none') === 0) {
		$triplet = '';
	}

	$tokens = bx_imagemagick_split_top_level(trim($transform));
	$next = array();
	$insertAt = -1;

	foreach ($tokens as $token) {
		if (preg_match('/^greyscale\s*\(/i', $token)) {
			if ($insertAt === -1) {
				$insertAt = count($next);
			}
			continue;
		}
		$next[] = $token;
	}

	if ($insertAt === -1) {
		$insertAt = count($next);
	}

	if ($triplet !== '') {
		array_splice($next, $insertAt, 0, array('greyscale(' . $triplet . ')'));
	}

	return implode(',', $next);
}

/**
 * Liest den ersten Greyscale-Tripelwert aus einem Transform-String aus.
 *
 * Wird kein gültiger greyscale()-Aufruf gefunden oder ist dessen Inhalt nicht
 * verwertbar, liefert die Funktion den übergebenen Fallback zurück.
 *
 * @param string $transform Vollständiger Transform-String.
 * @param string $fallback Rückgabewert, wenn kein gültiger Tripelwert gefunden wird.
 * @return string Normalisierter Greyscale-Tripelwert oder der Fallback.
 */
function bx_imagemagick_extract_greyscale_triplet(string $transform, string $fallback = 'none'): string {
	if (preg_match('/greyscale\s*\(\s*(-?\d+)\s*,\s*(-?\d+)\s*,\s*(-?\d+)\s*\)/i', $transform, $matches)) {
		$normalized = bx_imagemagick_normalize_greyscale_triplet($matches[1] . ',' . $matches[2] . ',' . $matches[3]);
		return $normalized !== '' ? $normalized : $fallback;
	}

	if (preg_match('/\bgreyscale\s*\(\s*\)/i', $transform)) {
		return $fallback;
	}

	return $fallback;
}

/**
 * Extrahiert Radius und Hintergrundfarbe eines round_edges()-Effekts.
 *
 * Die Funktion akzeptiert optionale Fallback-Werte und gibt immer eine
 * vollständige Konfiguration zurück. Der Radius wird niemals negativ, die
 * Hintergrundfarbe immer als normalisierte Hex-Farbe geliefert.
 *
 * @param string $transform Vollständiger Transform-String.
 * @param int $fallbackRadius Fallback-Radius, wenn kein Effekt vorhanden ist.
 * @param string $fallbackBackgroundColor Fallback-Farbe für transparente Bereiche.
 * @return array{radius:int,background_color:string} Normalisierte Effektkonfiguration.
 */
function bx_imagemagick_extract_round_edges_values(string $transform, int $fallbackRadius = 0, string $fallbackBackgroundColor = 'FFFFFF'): array {
	$fallbackBackgroundColor = bx_imagemagick_normalize_hex_color($fallbackBackgroundColor, 'FFFFFF');

	if (preg_match('/round_edges\s*\(\s*(-?\d+)\s*(?:,\s*([#A-Fa-f0-9]{3,6})\s*)?(?:,\s*(-?\d+)\s*)?\)/i', $transform, $matches)) {
		$radius = max(0, (int)$matches[1]);
		$backgroundColor = bx_imagemagick_normalize_hex_color(isset($matches[2]) ? (string)$matches[2] : '', $fallbackBackgroundColor);

		return array(
			'radius' => $radius,
			'background_color' => $backgroundColor,
		);
	}

	return array(
		'radius' => max(0, $fallbackRadius),
		'background_color' => $fallbackBackgroundColor,
	);
}

/**
 * Fügt einen round_edges()-Effekt ein oder ersetzt einen vorhandenen Eintrag.
 *
 * Bestehende round_edges()-Tokens werden entfernt und bei positivem Radius an
 * der bisherigen Position neu eingesetzt. Ist der Radius 0 oder kleiner, wird
 * der Effekt vollständig aus dem Transform-String entfernt.
 *
 * @param string $transform Bestehender Transform-String.
 * @param int $radius Gewünschter Radius für abgerundete Ecken.
 * @param string $backgroundColor Hintergrundfarbe für Formate ohne Alpha-Fläche.
 * @return string Aktualisierter Transform-String.
 */
function bx_imagemagick_upsert_round_edges_transform(string $transform, int $radius, string $backgroundColor): string {
	$tokens = bx_imagemagick_split_top_level(trim($transform));
	$next = array();
	$insertAt = -1;

	foreach ($tokens as $token) {
		if (preg_match('/^round_edges\s*\(/i', $token)) {
			if ($insertAt === -1) {
				$insertAt = count($next);
			}
			continue;
		}
		$next[] = $token;
	}

	if ($insertAt === -1) {
		$insertAt = count($next);
	}

	$radius = max(0, (int)$radius);
	if ($radius > 0) {
		$backgroundColor = bx_imagemagick_normalize_hex_color($backgroundColor, 'FFFFFF');
		array_splice($next, $insertAt, 0, array('round_edges(' . $radius . ',' . $backgroundColor . ')'));
	}

	return implode(',', $next);
}

/**
 * Extrahiert die Konfiguration eines drop_shadow()-Effekts aus dem Transform.
 *
 * Neben Breite und Farben wird auch der optionale Fade-Wert eingelesen und auf
 * den unterstützten Bereich 20 bis 100 begrenzt. Die Rückgabe enthält immer
 * eine vollständige und normalisierte Konfiguration.
 *
 * @param string $transform Vollständiger Transform-String.
 * @param int $fallbackWidth Fallback-Breite, wenn kein Effekt gefunden wird.
 * @param string $fallbackShadowColor Fallback-Farbe des Schattens.
 * @param string $fallbackBackgroundColor Fallback-Hintergrundfarbe.
 * @param int $fallbackFade Fallback-Intensität für den Schattenverlauf.
 * @return array{width:int,shadow_color:string,background_color:string,fade:int} Normalisierte Schattenkonfiguration.
 */
function bx_imagemagick_extract_drop_shadow_values(string $transform, int $fallbackWidth = 0, string $fallbackShadowColor = '000000', string $fallbackBackgroundColor = 'FFFFFF', int $fallbackFade = 0): array {
	$fallbackShadowColor     = bx_imagemagick_normalize_hex_color($fallbackShadowColor, '000000');
	$fallbackBackgroundColor = bx_imagemagick_normalize_hex_color($fallbackBackgroundColor, 'FFFFFF');
	$fallbackFade            = max(0, min(100, (int)$fallbackFade));

	// 1. Finde den drop_shadow(...) Aufruf und extrahiere nur den Inhalt der Klammer
	if (preg_match('/drop_shadow\s*\(([^)]+)\)/i', $transform, $matches)) {
		// Teile den Inhalt an den Kommas auf
		$args = explode(',', $matches[1]);
		$args = array_map('trim', $args);

		// Breite ist immer das 1. Argument
		$width = isset($args[0]) ? max(0, (int)$args[0]) : max(0, $fallbackWidth);
		
		// Farben und Fade anhand der Array-Positionen sauber auslesen
		$shadowColor     = bx_imagemagick_normalize_hex_color(isset($args[1]) ? $args[1] : '', $fallbackShadowColor);
		$backgroundColor = bx_imagemagick_normalize_hex_color(isset($args[2]) ? $args[2] : '', $fallbackBackgroundColor);
		$fade            = max(0, min(100, isset($args[3]) ? (int)$args[3] : $fallbackFade));

		return array(
			'width'            => $width,
			'shadow_color'     => $shadowColor,
			'background_color' => $backgroundColor,
			'fade'             => $fade,
		);
	}

	return array(
		'width'            => max(0, $fallbackWidth),
		'shadow_color'     => $fallbackShadowColor,
		'background_color' => $fallbackBackgroundColor,
		'fade'             => $fallbackFade,
	);
}

/**
 * Fügt einen drop_shadow()-Effekt ein oder ersetzt einen vorhandenen Eintrag.
 *
 * Die Funktion entfernt zuerst alle existierenden drop_shadow()-Tokens. Ist die
 * Breite positiv, wird ein einzelner normalisierter Eintrag mit Schattenfarbe,
 * Hintergrundfarbe und Fade-Wert eingefügt. Andernfalls wird der Effekt aus
 * der Transform-Kette entfernt.
 *
 * @param string $transform Bestehender Transform-String.
 * @param int $width Breite des Schatteneffekts.
 * @param string $shadowColor Schattenfarbe als Hex-Wert.
 * @param string $backgroundColor Hintergrundfarbe für transparente Zielbereiche.
 * @param int $fade Verlauf des Schattens im Bereich 20 bis 100.
 * @return string Aktualisierter Transform-String.
 */
function bx_imagemagick_upsert_drop_shadow_transform(string $transform, int $width, string $shadowColor, string $backgroundColor, int $fade = 65): string {
	$tokens = bx_imagemagick_split_top_level(trim($transform));
	$next = array();
	$insertAt = -1;

	foreach ($tokens as $token) {
		if (preg_match('/^drop_shadow\s*\(/i', $token)) {
			if ($insertAt === -1) {
				$insertAt = count($next);
			}
			continue;
		}
		$next[] = $token;
	}

	if ($insertAt === -1) {
		$insertAt = count($next);
	}

	$width = max(0, (int)$width);
	if ($width > 0) {
		$shadowColor = bx_imagemagick_normalize_hex_color($shadowColor, '000000');
		$backgroundColor = bx_imagemagick_normalize_hex_color($backgroundColor, 'FFFFFF');
		$fade = max(0, min(100, (int)$fade));
		array_splice($next, $insertAt, 0, array('drop_shadow(' . $width . ',' . $shadowColor . ',' . $backgroundColor . ',' . $fade . ')'));
	}

	return implode(',', $next);
}

/**
 * Speichert einen Konfigurationswert in der modified-Konfigurationstabelle.
 *
 * Die Funktion escaped Schlüssel und Wert über xtc_db_input() und führt ein
 * einfaches UPDATE gegen TABLE_CONFIGURATION aus. Sie erwartet, dass der
 * Konfigurationsschlüssel bereits existiert; ein Upsert erfolgt hier bewusst
 * nicht.
 *
 * @param string $key Konfigurationsschlüssel in TABLE_CONFIGURATION.
 * @param string $value Zu speichernder Konfigurationswert.
 * @return bool True nach Ausführen des Updates, sonst false bei fehlender Tabellenkonstante.
 */
function bx_imagemagick_save_configuration(string $key, string $value): bool {
	if (!defined('TABLE_CONFIGURATION')) {
		return false;
	}

	$key = xtc_db_input($key);
	$value = xtc_db_input($value);

	xtc_db_query("UPDATE " . constant('TABLE_CONFIGURATION') . "
								 SET configuration_value = '" . $value . "'
							 WHERE configuration_key = '" . $key . "'");
	return true;
}

/**
 * Konfigurationseingabefeld für die Modulversion (read-only)
 */
if (!function_exists('bx_configuration_field_version')) {
  function bx_configuration_field_version(string $value, string $constant): string {
    return xtc_draw_input_field( 'configuration['.$constant.']', $value, 'readonly="true" style="opacity: 0.4;"');
  }
}