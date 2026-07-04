<?php
/**
 * Archivo de idioma español para el módulo del sistema bx_image_magick.
 * \lang\spanish\modules\system\bx_image_magick.php
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

define('MODULE_BX_IMAGE_MAGICK_TITLE', 'BX Image Magick - <span style="font-weight: normal;">Edición de imágenes con ImageMagick</span>');

$description = '
<details class="bxac-card">
	<summary class="bxac-summary" style="list-style: none; display: inline-flex; align-items: center; gap: 8px; width: 100%;">
    <span class="bxac-arrow" style="font-size: 2rem;">▸</span>
    <span class="bxac-title">' . xtc_image(DIR_WS_ICONS.'heading/bx_image_magick.png', 'BX Image Magick', '', '', 'style="max-height: 40px; vertical-align: middle; margin-right: 8px; cursor: pointer;"') . '<strong>BX Image Magick</strong></span>
  </summary>
  <div class="bxac-body">
    <h3 style="margin-top: 0;">ódulo para la edición de imágenes con ImageMagick</h3>
    <p>Permite la creación y edición de imágenes con la biblioteca ImageMagick, incluyendo soporte para perfiles de color ICC para conversiones CMYK y RGB.</p>
		<h4>Perfiles de color ICC</h4>
		<table class="admin_table">
			<thead>
				<tr>
					<th style="vertical-align: top;">Perfil</th>
					<th style="vertical-align: top;">Modificar<br>(Stand 06/2026)</th>
					<th style="vertical-align: top;">Propósito típico</th>
					<th style="vertical-align: top;">Impacto práctico</th>
					<th style="vertical-align: top;">Uso recomendado en la tienda</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="vertical-align: top;">PSOcoated_v3.icc</td>
					<td style="vertical-align: top;">ca. 10–11 años (Copyright 2015)</td>
					<td style="vertical-align: top;">Perfil CMYK moderno para impresión offset estucada</td>
					<td style="vertical-align: top;">Interpretación CMYK más neutral y actualizada en comparación con perfiles FOGRA39 más antiguos</td>
					<td style="vertical-align: top;"><strong>Recomendado por defecto</strong> para fuentes CMYK en flujos de trabajo actuales</td>
				</tr>
				<tr>
					<td style="vertical-align: top;">CoatedFOGRA39.icc</td>
					<td style="vertical-align: top;">ca. 17–18 años (Archivo 2008, Descripción ISO 12647-2:2004)</td>
					<td style="vertical-align: top;">Perfil de referencia CMYK más antiguo (Legacy/Fallback)</td>
					<td style="vertical-align: top;">Puede diferir ligeramente en el eje de gris y saturación en comparación con v3; a menudo aún adecuado para flujos de trabajo más antiguos</td>
					<td style="vertical-align: top;"><strong>Fallback</strong> para datos de impresión heredados o cuando v3 difiere visiblemente</td>
				</tr>
				<tr>
					<td style="vertical-align: top;">sRGB2014.icc</td>
					<td style="vertical-align: top;">ca. 10–11 años (Copyright 2015)</td>
					<td style="vertical-align: top;">Perfil RGB para web/representación estándar</td>
					<td style="vertical-align: top;">Salida sRGB sólida y actualizada para imágenes de navegador y tienda</td>
					<td style="vertical-align: top;"><strong>Recomendado por defecto</strong> como perfil RGB para la tienda en vivo</td>
				</tr>
				<tr>
					<td style="vertical-align: top;">ColorMatchRGB.icc</td>
					<td style="vertical-align: top;">muy antiguo (Copyright 2000, Archivo 2008)</td>
					<td style="vertical-align: top;">Perfil RGB más antiguo (Fallback)</td>
					<td style="vertical-align: top;">Para la web hoy en día, generalmente menos ideal; puede llevar a diferencias en brillo/efecto de color</td>
					<td style="vertical-align: top;"><strong>No como predeterminado</strong>; solo como Fallback para datos antiguos</td>
				</tr>
			</tbody>
		</table>
		<h5>Conclusión:</h5>
		<p>La combinación predeterminada actual PSOcoated_v3 → sRGB2014 es sensata y moderna.
		Los perfiles Legacy CoatedFOGRA39 y ColorMatchRGB son buenas opciones de respaldo, pero no la primera elección para nuevas configuraciones.
		La mayor diferencia visible casi siempre se produce en el perfil de origen CMYK; un perfil de origen incorrecto conduce rápidamente a desviaciones de color o colores apagados.</p>
  </div>
</details>';

if((!defined('MODULE_BX_IMAGE_MAGICK_STATUS')) || (MODULE_BX_IMAGE_MAGICK_STATUS != 'True') && basename($_SERVER['PHP_SELF']) == 'module_export.php') {
	$description .= '<p><a class="button btnbox but_red" style="text-align:center;" onclick="return confirmLink(\'¿Deseas eliminar todos los archivos?\', \'\' ,this);" href="'.xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=bx_image_magick&action=custom').'">Eliminar todos los archivos del módulo</a></p>';
}

define('MODULE_BX_IMAGE_MAGICK_DESCRIPTION', $description);

define('MODULE_BX_IMAGE_MAGICK_STATUS_TITLE', 'Estado');
define('MODULE_BX_IMAGE_MAGICK_STATUS_DESC', '¿Activar módulo?');

define('MODULE_BX_IMAGE_MAGICK_AUTO_CREATE_ON_CONSTRUCT_TITLE', 'Crear imágenes automáticamente al construir');
define('MODULE_BX_IMAGE_MAGICK_AUTO_CREATE_ON_CONSTRUCT_DESC', '¿Se debe crear la imagen automáticamente cuando se instancia la clase y los archivos de origen y destino son válidos? Esta configuración puede afectar el rendimiento si se crean muchas instancias de la clase, ya que cada vez se realiza un procesamiento de imagen. Se recomienda activar esta opción solo si está seguro de que las imágenes se necesitan inmediatamente al construir y la cantidad de instancias es manejable.');

define('IMAGEMANIPULATOR_ICC_PROFILE_CMYK_TITLE', 'Perfil ICC CMYK (Nombre de archivo)');
define('IMAGEMANIPULATOR_ICC_PROFILE_CMYK_DESC', 'Nombre de archivo del perfil CMYK de origen en el directorio admin/includes/classes/ICC/. Ejemplo: PSOcoated_v3.icc');
define('IMAGEMANIPULATOR_ICC_PROFILE_RGB_TITLE', 'Perfil ICC RGB (Nombre de archivo)');
define('IMAGEMANIPULATOR_ICC_PROFILE_RGB_DESC', 'Nombre de archivo del perfil RGB de destino en el directorio admin/includes/classes/ICC/. Ejemplo: sRGB2014.icc');

define('MODULE_BX_IMAGE_MAGICK_CONFIG_GROUP_ID_TITLE', 'ID de grupo de configuración interna');
define('MODULE_BX_IMAGE_MAGICK_CONFIG_GROUP_ID_DESC', 'Valor técnico interno para el grupo de configuración del módulo. No cambiar manualmente.');

define('PRODUCT_IMAGE_INFO_TRANSFORM_TITLE', 'Cadena de transformación para imágenes de información');
define('PRODUCT_IMAGE_INFO_TRANSFORM_DESC', 'Orden de efectos para imágenes de información, por ejemplo, round_edges(4),drop_shadow(3).');
define('PRODUCT_IMAGE_MIDI_TRANSFORM_TITLE', 'Cadena de transformación para imágenes Midi');
define('PRODUCT_IMAGE_MIDI_TRANSFORM_DESC', 'Orden de efectos para imágenes Midi, por ejemplo, round_edges(4),drop_shadow(3).');
define('PRODUCT_IMAGE_MINI_TRANSFORM_TITLE', 'Cadena de transformación para imágenes Mini');
define('PRODUCT_IMAGE_MINI_TRANSFORM_DESC', 'Orden de efectos para imágenes Mini, por ejemplo, round_edges(4),drop_shadow(3).');
define('PRODUCT_IMAGE_POPUP_TRANSFORM_TITLE', 'Cadena de transformación para imágenes Popup');
define('PRODUCT_IMAGE_POPUP_TRANSFORM_DESC', 'Orden de efectos para imágenes Popup, por ejemplo, round_edges(4),drop_shadow(3).');
define('PRODUCT_IMAGE_THUMBNAIL_TRANSFORM_TITLE', 'Cadena de transformación para imágenes Thumbnail');
define('PRODUCT_IMAGE_THUMBNAIL_TRANSFORM_DESC', 'Orden de efectos para imágenes Thumbnail, por ejemplo, round_edges(4),drop_shadow(3).');

define('MODULE_BX_IMAGE_MAGICK_IMAGICK_ERROR', '¡ERROR! El módulo <strong>' . constant('MODULE_BX_IMAGE_MAGICK_TITLE') . '</strong> no se puede instalar porque falta la biblioteca Imagick.');

define('MODULE_BX_IMAGE_MAGICK_TEXT_COULD_NOT_BE_DELETED', '¡ERROR! El módulo <strong>' . constant('MODULE_BX_IMAGE_MAGICK_TITLE') . '</strong> no se pudo eliminar.');
define('MODULE_BX_IMAGE_MAGICK_TEXT_SUCCESSFULLY_REMOVED', '¡ÉXITO! El módulo <strong>' . constant('MODULE_BX_IMAGE_MAGICK_TITLE') . '</strong> se eliminó correctamente.');
define('MODULE_BX_IMAGE_MAGICK_TEXT_REMOVAL_INCOMPLETE', '¡ERROR! El módulo <strong>' . constant('MODULE_BX_IMAGE_MAGICK_TITLE') . '</strong> no se pudo eliminar completamente.');
