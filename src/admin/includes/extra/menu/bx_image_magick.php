<?php
/* --------------------------------------------------------------
   $ $Id: bx_image_magick.php 2026-06-03 12:00:00Z BENAX $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce; www.oscommerce.com 
   (c) 2003      nextcommerce; www.nextcommerce.org
   (c) 2006      xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

if( defined("MODULE_BX_IMAGE_MAGICK_STATUS") && 'True' === MODULE_BX_IMAGE_MAGICK_STATUS) {
	//Sprachabhängiger Menüeintrag, kann für weitere Sprachen ergänzt werden
	switch ($_SESSION['language_code']) {
		case 'de':
			if(!defined('MENU_NAME_BX_IMAGE_MAGICK')) 
        define('MENU_NAME_BX_IMAGE_MAGICK','BX Image Magick');
			break;
		default:
			if(!defined('MENU_NAME_BX_IMAGE_MAGICK')) 
        define('MENU_NAME_BX_IMAGE_MAGICK','BX Image Magick');
			break;
	}
	
	//BOX_HEADING_TOOLS = Name der box in der der neue Menueeintrag erscheinen soll
	$add_contents[BOX_HEADING_BX_MODULES][] = array(
		'admin_access_name' => 'bx_image_magick',         //Eintrag fuer Adminrechte
		'filename'          => 'bx_image_magick.php',     //Dateiname der neuen Admindatei
		'boxname'           => MENU_NAME_BX_IMAGE_MAGICK, //Anzeigename im Menue
		'parameters'        => '',                        //zusaetzliche Parameter z.B. 'set=export'
		'ssl'               => 'SSL'                      //SSL oder NONSSL, kein Eintrag = NONSSL
	);
}
