<?php
/*
 * --------------------------------------------------------------------------
 * @file      bx_image_magick.php
 * @date      04.06.18
 *
 * @copyright modulfux https://www.modulfux.de
 *
 * LICENSE:   Released under the GNU General Public License
 * --------------------------------------------------------------------------
 */

class bx_image_magick {
  /** @var string */
  public $code;

  /** @var string */
  public $version;

  /** @var string */
  public $title;

  /** @var string */
  public $description;

  /** @var int */
  public $sort_order;

  /** @var bool */
  public $enabled;

  /** @var string 'p' = production ready, 'd' = in development */
  public $development_status;

  /** @var bool Kennzeichnung als "Hot Module" fuer besondere Hervorhebung in der Admin-Oberflaeche */
  public $is_hot;

  /** @var int|null */
  protected $_check = null;

  /**
   * bx_image_magick constructor.
   */
  public function __construct() {
    $this->code        = 'bx_image_magick';
    $this->version     = '1.0.0';
    $this->title       = MODULE_BX_IMAGE_MAGICK_TITLE;
    $this->description = MODULE_BX_IMAGE_MAGICK_DESCRIPTION;
    $this->sort_order  = defined('MODULE_BX_IMAGE_MAGICK_SORT_ORDER') ? MODULE_BX_IMAGE_MAGICK_SORT_ORDER : 0;
    $this->enabled     = (defined('MODULE_BX_IMAGE_MAGICK_STATUS') && MODULE_BX_IMAGE_MAGICK_STATUS == 'True');
    $this->development_status = 'd';
    $this->is_hot      = false;
  }

  /**
   * process
   * @param mixed $file
   * @return void
   */
  public function process($file) {
  }

  /**
   * display
   * @return array<string, string>
   */
  public function display() {
    return array(
      'text' => '<br>' . xtc_button(BUTTON_SAVE) . '&nbsp;' . xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code)),
    );
  }

  /**
   * check
   * @return int
   */
  public function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value
                                     FROM " . TABLE_CONFIGURATION . "
                                    WHERE configuration_key = 'MODULE_BX_IMAGE_MAGICK_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }

    return (int)$this->_check;
  }

  /**
   * keys
   * @return array<int, string>
   */
  public function keys() {
    return array(
      'MODULE_BX_IMAGE_MAGICK_STATUS',
      'MODULE_BX_IMAGE_MAGICK_AUTO_CREATE_ON_CONSTRUCT',
      'MODULE_BX_IMAGE_MAGICK_CONFIG_GROUP_ID',
      'IMAGEMANIPULATOR_ICC_PROFILE_CMYK',
      'IMAGEMANIPULATOR_ICC_PROFILE_RGB',
    );
  }

  /**
   * keys2
   * @return array<int, string>
   */
  public function keys2() {
    return array(
      'PRODUCT_IMAGE_INFO_TRANSFORM',
      'PRODUCT_IMAGE_MIDI_TRANSFORM',
      'PRODUCT_IMAGE_MINI_TRANSFORM',
      'PRODUCT_IMAGE_POPUP_TRANSFORM',
      'PRODUCT_IMAGE_THUMBNAIL_TRANSFORM',
    );
  }

  /**
   * install
   * @return void
   */
  public function install() {

    xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." ADD bx_image_magick INTEGER(1)");
    xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET bx_image_magick = 1");

	  $freeId_query = xtc_db_query("SELECT MIN(configuration_group_id+1) AS id 
			                          					FROM ".TABLE_CONFIGURATION_GROUP." 
									  						         WHERE (configuration_group_id+1) NOT IN 
															             (SELECT configuration_group_id FROM ".TABLE_CONFIGURATION_GROUP." WHERE configuration_group_id IS NOT NULL);");
	  $freeId = xtc_db_fetch_array($freeId_query);

 	  $freeSort_query = xtc_db_query("SELECT MIN(sort_order+1) AS sort_order 
	                                          FROM ".TABLE_CONFIGURATION_GROUP." 
                                         	 WHERE (sort_order+1) NOT IN (SELECT sort_order FROM ".TABLE_CONFIGURATION_GROUP." WHERE sort_order IS NOT NULL)");
		$freeSort = xtc_db_fetch_array($freeSort_query);

    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION_GROUP." ( configuration_group_id, 
                                                                    configuration_group_title, 
                                                                    configuration_group_description, 
                                                                    sort_order, 
                                                                    visible ) 
                                                          VALUES ( '" . (int)$freeId['id'] . "', 
                                                                    '" . xtc_db_input('BX Image Magick') . "', 
                                                                    '" . xtc_db_input('Settings for the BX Image Magick module') . "', 
                                                                    '" . (int)$freeSort['sort_order'] . "',
                                                                    1 );");

		xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." ( configuration_id, 
	                                                      			configuration_key, 
																															configuration_value, 
																															configuration_group_id, 
																															sort_order, 
																															set_function, 
																															date_added ) 
																										 VALUES ( '', 
																															'MODULE_BX_IMAGE_MAGICK_STATUS', 
																															'True',  
																															'6', 
																															'1', 
																															'xtc_cfg_select_option(array(\'True\', \'False\'), ',
																															now() );");

		xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." ( configuration_id, 
	                                                      			configuration_key, 
																															configuration_value, 
																															configuration_group_id, 
																															sort_order, 
																															set_function, 
																															date_added ) 
																										 VALUES ( '', 
																															'MODULE_BX_IMAGE_MAGICK_AUTO_CREATE_ON_CONSTRUCT', 
																															'False',  
																															'6', 
																															'2', 
																															'xtc_cfg_select_option(array(\'True\', \'False\'), ',
																															now() );");

    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, 
                                                                  configuration_value, 
                                                                  configuration_group_id, 
                                                                  sort_order, 
                                                                  use_function, 
                                                                  set_function, 
                                                                  date_added)
                                                                VALUES ('IMAGEMANIPULATOR_ICC_PROFILE_CMYK', 
                                                                  'PSOcoated_v3.icc', 
                                                                  6, 
                                                                  3, 
                                                                  NULL, 
                                                                  'xtc_cfg_select_option(array(\'PSOcoated_v3.icc\', \'CoatedFOGRA39.icc\'), ',
                                                                  now())");

    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, 
                                                                  configuration_value, 
                                                                  configuration_group_id, 
                                                                  sort_order, 
                                                                  use_function, 
                                                                  set_function, 
                                                                  date_added)
                                                              VALUES ('IMAGEMANIPULATOR_ICC_PROFILE_RGB', 
                                                                  'sRGB2014.icc', 
                                                                  6, 
                                                                  4, 
                                                                  NULL, 
                                                                  'xtc_cfg_select_option(array(\'sRGB2014.icc\', \'ColorMatchRGB.icc\'), ',
                                                                  now())");

                                  xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key,
                                                                  configuration_value,
                                                                  configuration_group_id,
                                                                  sort_order,
                                                                  use_function,
                                                                  set_function,
                                                                  date_added)
                                                                VALUES ('MODULE_BX_IMAGE_MAGICK_CONFIG_GROUP_ID',
                                                                  '".(int)$freeId['id']."',
                                                                  6,
                                                                  5,
                                                                  NULL,
                                                                  'bx_configuration_field_version(',
                                                                  now())");

                                  xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key,
                                                                  configuration_value,
                                                                  configuration_group_id,
                                                                  sort_order,
                                                                  use_function,
                                                                  set_function,
                                                                  date_added)
                                                                VALUES ('PRODUCT_IMAGE_INFO_TRANSFORM',
                                                                  '',
                                                                  ".(int)$freeId['id'].",
                                                                  1,
                                                                  NULL,
                                                                  NULL,
                                                                  now())");

                                  xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key,
                                                                  configuration_value,
                                                                  configuration_group_id,
                                                                  sort_order,
                                                                  use_function,
                                                                  set_function,
                                                                  date_added)
                                                                VALUES ('PRODUCT_IMAGE_MIDI_TRANSFORM',
                                                                  '',
                                                                  ".(int)$freeId['id'].",
                                                                  2,
                                                                  NULL,
                                                                  NULL,
                                                                  now())");

                                  xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key,
                                                                  configuration_value,
                                                                  configuration_group_id,
                                                                  sort_order,
                                                                  use_function,
                                                                  set_function,
                                                                  date_added)
                                                                VALUES ('PRODUCT_IMAGE_MINI_TRANSFORM',
                                                                  '',
                                                                  ".(int)$freeId['id'].",
                                                                  3,
                                                                  NULL,
                                                                  NULL,
                                                                  now())");

                                  xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key,
                                                                  configuration_value,
                                                                  configuration_group_id,
                                                                  sort_order,
                                                                  use_function,
                                                                  set_function,
                                                                  date_added)
                                                                VALUES ('PRODUCT_IMAGE_POPUP_TRANSFORM',
                                                                  '',
                                                                  ".(int)$freeId['id'].",
                                                                  4,
                                                                  NULL,
                                                                  NULL,
                                                                  now())");

                                  xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key,
                                                                  configuration_value,
                                                                  configuration_group_id,
                                                                  sort_order,
                                                                  use_function,
                                                                  set_function,
                                                                  date_added)
                                                                VALUES ('PRODUCT_IMAGE_THUMBNAIL_TRANSFORM',
                                                                  '',
                                                                  ".(int)$freeId['id'].",
                                                                  5,
                                                                  NULL,
                                                                  NULL,
                                                                  now())");

    xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                           SET configuration_value = 'bx_image_magick.php'
                         WHERE configuration_key = 'IMAGE_MANIPULATOR'");
  }

  /**
   * remove
   * @return void
   */
  public function remove() {
		xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." DROP bx_image_magick;");
    xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key in ('".implode("', '", $this->keys())."')");
    xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key in ('".implode("', '", $this->keys2())."')");
		xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION_GROUP." WHERE configuration_group_title = 'BX Image Magick'");
    xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                           SET configuration_value = 'image_manipulator.php'
                         WHERE configuration_key = 'IMAGE_MANIPULATOR'");
  }

}
