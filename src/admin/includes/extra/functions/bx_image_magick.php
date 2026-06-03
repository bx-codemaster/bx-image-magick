<?php

function bx_imagemagick_text(string $key, string $fallback = ''): string {
	if (defined($key)) {
		return (string)constant($key);
	}
	return (string)$fallback;
}

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

function bx_imagemagick_save_configuration(string $key, string $value): bool {
	$key = xtc_db_input($key);
	$value = xtc_db_input($value);

	xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
								 SET configuration_value = '" . $value . "'
							 WHERE configuration_key = '" . $key . "'");
	return true;
}
