<?php
/* -----------------------------------------------------------------------------------------
   bx-image-magick - CSRF Exclusion fuer Live-Preview

   Grund:
   - bx_image_magick_preview.php wird im Admin per GET fuer die Live-Bildvorschau aufgerufen
   - Modified rotiert auf solchen Requests sonst den CSRF-Token in der Session
   - Das geoeffnete Formular verliert dadurch seinen gueltigen Hidden-Token und Save schlaegt fehl

   Loesung:
   - Nur bx_image_magick_preview wird zur Exclusion-Liste hinzugefuegt
   - Die eigentliche Admin-Seite bx_image_magick.php bleibt weiter CSRF-geschuetzt
   -----------------------------------------------------------------------------------------*/

if (!isset($module_exclusions) || !is_array($module_exclusions)) {
    $module_exclusions = array();
}

$module_exclusions[] = 'bx_image_magick_preview';
