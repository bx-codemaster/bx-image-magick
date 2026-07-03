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
define('TEXT_BX_IMAGE_MAGICK_TAB_PREVIEW_FILE', 'Archivo de vista previa actual');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_FILE_INTRO', 'Subir imagen original para la vista previa.');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_FILE_UPLOAD_BUTTON', 'Iniciar carga');
define('TEXT_BX_IMAGE_MAGICK_PREVIEW_FILE_ALLOWED_TYPES', 'Permitido: JPG, JPEG, PNG, GIF, WebP.');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_LIVE_PREVIEW_TITLE', 'Su selección (vista previa):');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_TITLE', 'Imágenes subidas:');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_EMPTY', 'Aún no se han subido imágenes.');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_GENERATE_BUTTON', 'Generar vista previa');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_DELETE_BUTTON', 'Eliminar');
define('TEXT_BX_IMAGE_MAGICK_GALLERY_DELETE_CONFIRM', '¿Eliminar esta imagen?');

define('TEXT_BX_IMAGE_MAGICK_DASHBOARD_INTRO', 'Aquí se pueden gestionar las opciones centrales de procesamiento de imágenes para este módulo.');
define('TEXT_BX_IMAGE_MAGICK_FUNCTIONS_INTRO', 'Opciones de efectos planificadas: bisel, escala de grises, elipse, bordes redondeados, marco, sombra proyectada, desenfoque de movimiento.');
define('TEXT_BX_IMAGE_MAGICK_SUPPORT_INTRO', 'Las funciones de soporte y diagnóstico se agregarán gradualmente.');
define('TEXT_BX_IMAGE_MAGICK_IMAGE_TAB_INTRO', 'Configura las opciones de fusión y efectos para este tamaño de imagen.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_MERGE_STRING', 'Cadena de fusión');
define('TEXT_BX_IMAGE_MAGICK_FIELD_MERGE_STRING_HINT', 'Este campo define las opciones de fusión para el formato de imagen seleccionado.<br>Valor predeterminado: (overlay.gif,10,-50,60,FF0000).<br>Uso: (merge_image, x start [neg = desde la derecha], y start [neg = desde la parte inferior], opacidad, color transparente en la imagen fusionada).');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_TITLE', 'Posicionador de fusión');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_OVERLAY', 'Superposición');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_OVERLAY_FILE', 'Archivo de superposición');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_OVERLAY_NONE', 'Sin superposición');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_X', 'X');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_Y', 'Y');
define('TEXT_BX_IMAGE_MAGICK_MERGE_POSITIONER_HINT', 'Define la posición por arrastrar y soltar o con los deslizadores X/Y. La cadena de fusión se sincroniza automáticamente.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_ORDER', 'Orden de efectos');
define('TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_ORDER_HINT', 'Este campo se completa automáticamente con los efectos seleccionados y define el orden en que se aplicarán los efectos.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_EFFECT_PLACEHOLDER', 'Este campo se completa automáticamente.');
define('TEXT_BX_IMAGE_MAGICK_FIELD_ROUND_EDGES', 'Radio de bordes redondeados');
define('TEXT_BX_IMAGE_MAGICK_FIELD_DROP_SHADOW', 'Sombra proyectada');
define('TEXT_BX_IMAGE_MAGICK_FIELD_DROP_SHADOW_FADE', 'Desvanecimiento de la sombra proyectada');
define('TEXT_BX_IMAGE_MAGICK_FIELD_GREYSCALE', 'Escala de grises (r,g,b)');
define('TEXT_BX_IMAGE_MAGICK_FIELD_GREYSCALE_HINT', 'Ponderaciones predefinidas para el cálculo en escala de grises.');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_NONE', 'Desactivado');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_NEUTRAL', 'Neutro 33/33/33');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_STANDARD', 'Estándar 30/59/11');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_GREEN_STRONG', 'Verde fuerte 21/72/7');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_PORTRAIT', 'Retrato 37/53/10');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_WARM', 'Cálido 48/41/11');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_COOL', 'Frío 24/47/29');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_RED_CHANNEL', 'Solo canal rojo');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_GREEN_CHANNEL', 'Solo canal verde');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_BLUE_CHANNEL', 'Solo canal azul');
define('TEXT_BX_IMAGE_MAGICK_GREYSCALE_OPTION_CUSTOM', 'Personalizado');
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
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_UPLOAD_DIR_NOT_WRITABLE', 'El directorio de carga no tiene permisos de escritura: %s');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_UPLOAD_SUCCESS', 'Imagen original subida. Siguiente paso: generar vistas previas.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_UPLOAD_FAILED', 'No se seleccionó una imagen válida o la carga falló.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_NOT_FOUND', 'No se encontró la imagen seleccionada.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_DELETED', 'Imagen eliminada: %s');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_DELETE_FAILED', 'No se pudo eliminar la imagen.');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_GENERATED', 'Vistas previas creadas para %s (%s tamaños).');
define('TEXT_BX_IMAGE_MAGICK_MESSAGE_PREVIEW_IMAGE_GENERATE_FAILED', 'No se pudieron crear las vistas previas.');
define('TEXT_BX_IMAGE_MAGICK_BUTTON_RESET', 'Restablecer');