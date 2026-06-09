<?php

function bx_imagemagick_const_value(string $key): string {
	return (string)constant($key);
}

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

function bx_imagemagick_extract_drop_shadow_values(string $transform, int $fallbackWidth = 0, string $fallbackShadowColor = '000000', string $fallbackBackgroundColor = 'FFFFFF', int $fallbackFade = 65): array {
	$fallbackShadowColor = bx_imagemagick_normalize_hex_color($fallbackShadowColor, '000000');
	$fallbackBackgroundColor = bx_imagemagick_normalize_hex_color($fallbackBackgroundColor, 'FFFFFF');
	$fallbackFade = max(20, min(100, (int)$fallbackFade));

	if (preg_match('/drop_shadow\s*\(\s*(-?\d+)\s*(?:,\s*([#A-Fa-f0-9]{3,6})\s*)?(?:,\s*([#A-Fa-f0-9]{3,6})\s*)?(?:,\s*(-?\d{1,3})\s*)?\)/i', $transform, $matches)) {
		$width = max(0, (int)$matches[1]);
		$shadowColor = bx_imagemagick_normalize_hex_color(isset($matches[2]) ? (string)$matches[2] : '', $fallbackShadowColor);
		$backgroundColor = bx_imagemagick_normalize_hex_color(isset($matches[3]) ? (string)$matches[3] : '', $fallbackBackgroundColor);
		$fade = max(20, min(100, isset($matches[4]) ? (int)$matches[4] : $fallbackFade));

		return array(
			'width' => $width,
			'shadow_color' => $shadowColor,
			'background_color' => $backgroundColor,
			'fade' => $fade,
		);
	}

	return array(
		'width' => max(0, $fallbackWidth),
		'shadow_color' => $fallbackShadowColor,
		'background_color' => $fallbackBackgroundColor,
		'fade' => $fallbackFade,
	);
}

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
		$fade = max(20, min(100, (int)$fade));
		array_splice($next, $insertAt, 0, array('drop_shadow(' . $width . ',' . $shadowColor . ',' . $backgroundColor . ',' . $fade . ')'));
	}

	return implode(',', $next);
}

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
