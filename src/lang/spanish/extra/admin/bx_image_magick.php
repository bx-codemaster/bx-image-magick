<?php
/**
 * Archivo de idioma español para el módulo del sistema bx_image_magick.
 * \lang\spanish\extra\admin\bx_image_magick.php
 *
 * Este archivo define los textos del módulo en español para la configuración,
 * instrucciones de instalación y mensajes de error en el área de administración modificada.
 *
 * @file        bx_image_magick.php
 * @package     bx-image-magick
 * @author      bx-codemaster (benax)
 * @website     www.bx-coding.de
 * @license     GNU General Public License (GPL)
 * @since       2026-06-10
 */
	
define('BX_IMAGE_MAGICK_TITLE', 'BX Image Magick');
define('BX_IMAGE_MAGICK_SHORT_DESCRIPTION', 'Módulo para la edición de imágenes con ImageMagick.');
define('BX_IMAGE_MAGICK_LONG_DESCRIPTION', 'Módulo para la edición de imágenes con ImageMagick, incluyendo soporte para perfiles de color ICC para conversiones CMYK y RGB.');

define('TEXT_BX_IMAGE_MAGICK_TAB_DASHBOARD', 'Dashboard');
define('TEXT_BX_IMAGE_MAGICK_TAB_FUNCTIONS', 'Funciones');
define('TEXT_BX_IMAGE_MAGICK_TAB_SUPPORT', 'Soporte');
define('TEXT_BX_IMAGE_MAGICK_TAB_INFO', 'Imágenes de información');
define('TEXT_BX_IMAGE_MAGICK_TAB_MIDI', 'Imágenes Midi');
define('TEXT_BX_IMAGE_MAGICK_TAB_MINI', 'Imágenes Mini');
define('TEXT_BX_IMAGE_MAGICK_TAB_POPUP', 'Imágenes Popup');
define('TEXT_BX_IMAGE_MAGICK_TAB_THUMBNAIL', 'Imágenes en miniatura');

define('TEXT_BX_IMAGE_MAGICK_DASHBOARD_INTRO', 'Aquí se pueden gestionar las opciones centrales de procesamiento de imágenes para este módulo.');
define('TEXT_BX_IMAGE_MAGICK_FUNCTIONS_INTRO', 'Opciones de efectos planificadas: bisel, escala de grises, elipse, bordes redondeados, marco, sombra proyectada, desenfoque de movimiento.');
define('TEXT_BX_IMAGE_MAGICK_SUPPORT_INTRO', 'Las funciones de soporte y diagnóstico se agregarán gradualmente.');
define('TEXT_BX_IMAGE_MAGICK_IMAGE_TAB_INTRO', 'Configura las opciones de fusión y efectos para este tamaño de imagen.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_MERGE_STRING', 'Cadena de fusión');
define('TEXT_BX_IMAGE_MAGICK_FIELD_MERGE_STRING_HINT', 'Este campo define las opciones de fusión para el formato de imagen seleccionado.<br>Valor predeterminado: (overlay.gif,10,-50,60,FF0000).<br>Uso: (merge_image, x start [neg = desde la derecha], y start [neg = desde la parte inferior], opacidad, color transparente en la imagen fusionada).');
define('TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_ORDER', 'Orden de efectos');
define('TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_ORDER_HINT', 'Este campo se completa automáticamente con los efectos seleccionados y define el orden en que se aplicarán los efectos.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_PLACEHOLDER', 'Este campo se completa automáticamente.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_ROUND_EDGES', 'Radio de bordes redondeados');
define('TEXT_BX_IMAGE_MAGICK_FIELD_DROP_SHADOW', 'Sombra proyectada');
define('TEXT_BX_IMAGE_MAGICK_FIELD_DROP_SHADOW_FADE', 'Desvanecimiento de la sombra proyectada');
define('TEXT_BX_IMAGE_MAGICK_FIELD_GREYSCALE', 'Escala de grises (r,g,b)');
define('TEXT_BX_IMAGE_MAGICK_DROP_SHADOW_COLOR_HINT', 'El color de la sombra define el color de la sombra proyectada. El color de fondo solo se utiliza en JPG/JPEG como color de relleno para áreas transparentes.');
define('TEXT_BX_IMAGE_MAGICK_DROP_SHADOW_FADE_HINT', 'Nota: Valores de desvanecimiento pequeños generan una sombra más compacta con un desvanecimiento temprano. Valores de desvanecimiento grandes extienden el gradiente y hacen que la sombra se mezcle más suavemente con la transparencia.');
define('TEXT_BX_IMAGE_MAGICK_ACTION_SAVE', 'Guardar');
define('TEXT_BX_IMAGE_MAGICK_ACTION_PREVIEW', 'Generar vista previa');
define('TEXT_BX_IMAGE_MAGICK_SUPPORT_HINT_1', 'Planeado: Vista previa en vivo con archivos temporales (tmp).');
define('TEXT_BX_IMAGE_MAGICK_SUPPORT_HINT_2', 'Planeado: Ejecución de prueba para el tamaño de imagen seleccionado.');
define('TEXT_BX_IMAGE_MAGICK_SUPPORT_HINT_3', 'Planeado: Validación del orden de los efectos.');

define('TEXT_BX_IMAGE_MAGICK_QUICK_ACTIONS', 'Acciones rápidas');
define('TEXT_BX_IMAGE_MAGICK_MODULE_SETTINGS', 'Configuración del módulo');
define('TEXT_BX_IMAGE_MAGICK_CONFIGURATION', 'Configuración');
define('TEXT_BX_IMAGE_MAGICK_IMAGE_PROCESSING', 'Procesamiento de imágenes');
define('TEXT_BX_IMAGE_MAGICK_RUN_IMAGE_PROCESSING', 'Regenerar imágenes');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_PANEL', 'Vista previa');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_HINT', 'Aquí aparecerá más adelante la vista previa en vivo para la pestaña de tamaño seleccionada actualmente.');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_PLACEHOLDER', 'Aún no se ha cargado ninguna vista previa');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_SETTINGS_SAVED', 'Configuración de BX Image Magick guardada.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_INVALID_TRANSFORMS_RESET', 'Cadenas de transformación no válidas fueron eliminadas.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_IMAGE_SCALED_DOWN', 'La imagen original es más grande que la pantalla y se ha reducido.');