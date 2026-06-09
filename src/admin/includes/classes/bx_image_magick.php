<?php
/**
 * Bildmanipulation via Imagick für modified eCommerce.
 *
 * @file        bx_image_magick.php
 * @package     bx-image-magick
 * @author      Blade
 * @idea        modulfux (Ideengeber)
 * @copyright   modulfux
 * @copyright   Blade
 * @license     GNU General Public License (GPL)
 * @since       2018-06-04
 */


class image_manipulation {
  /** @var array<int, string> */
  protected const ALLOWED_MANIPULATIONS = array(
    'bevel',
    'greyscale',
    'ellipse',
    'round_edges',
    'merge',
    'frame',
    'drop_shadow',
    'motion_blur',
  );

  /** @var string|null */
  public ?string $resource_file = null;
   
  /** @var int|null */
  public ?int $max_width = null; 

  /** @var int|null */
  public ?int $max_height = null;
  
  /** @var string|null */
  public ?string $destination_file = null;
  
  /** @var int|null */
  public ?int $compression = null;

  /** @var string|null */
  public ?string $transform = null;
  
  /** @var array<int, array<string, mixed>> */
  public array $merge_data = array();

  /** @var array<int, array{name:string,args:array<int,mixed>}> */
  public array $effects_queue = array();

  /** @var int */
  protected int $image_type = IMAGETYPE_JPEG;

  /** @var string|null */
  protected ?string $temporary_source = null;

  /** @var bool */
  protected bool $webpWrittenInCreate = false;

  /** @var string */
  protected string $jpegFlattenBackgroundColor = '#FFFFFF';

  /**
   * __construct
   *
   * bx_image_magick constructor.
    * @param string $resource_file
    * @param int $max_width
    * @param int $max_height
    * @param string $destination_file
    * @param int|null $compression
    * @param string $transform
   */
  function __construct($resource_file, $max_width, $max_height, $destination_file = '', $compression = null, $transform = '') {
    $this->resource_file    = $resource_file;
    $this->max_width        = (int)$max_width;
    $this->max_height       = (int)$max_height;
    $this->destination_file = $destination_file;

    if ($compression === null) {
      $compression = defined('IMAGE_QUALITY') ? (int)IMAGE_QUALITY : 85;
    }
    
    $this->compression      = (int)$compression;
    $this->transform        = $transform;

    if ($this->transform === null || trim((string)$this->transform) === '') {
      $this->transform = $this->resolveAutoTransformForDestination((string)$this->destination_file);
    }

    $autoCreateOnConstruct = false;
    if (defined('MODULE_BX_IMAGE_MAGICK_AUTO_CREATE_ON_CONSTRUCT')) {
      $autoCreateRaw = trim((string)MODULE_BX_IMAGE_MAGICK_AUTO_CREATE_ON_CONSTRUCT);
      $autoCreateParsed = filter_var($autoCreateRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
      $autoCreateOnConstruct = ($autoCreateParsed === null) ? false : (bool)$autoCreateParsed;
    }

    if ($autoCreateOnConstruct && is_file((string)$this->resource_file) && $this->destination_file !== '') {
      $this->create();
    }
  }

  /**
   * Kompatible Merge-Signatur wie im Shop aufgerufen.
    *
    * @param string $merge_file
    * @param int $x_pos
    * @param int $y_pos
    * @param int $opacity
    * @param int|string $scale_or_trans_colour
    * @return void
   */
  public function merge($merge_file, $x_pos = 0, $y_pos = 0, $opacity = 100, $scale_or_trans_colour = 'FF0000') {
    $scale = 100;
    $trans_colour = null;

    if (is_numeric($scale_or_trans_colour)) {
      $scale = max(1, (int)$scale_or_trans_colour);
    } elseif (is_string($scale_or_trans_colour) && $scale_or_trans_colour !== '') {
      $trans_colour = ltrim($this->normalizeHexColor($scale_or_trans_colour, '#FF0000'), '#');
    }

    $this->merge_data[] = array(
      'file'         => $merge_file,
      'x'            => (int)$x_pos,
      'y'            => (int)$y_pos,
      'opacity'      => max(0, min(100, (int)$opacity)),
      'scale'        => $scale,
      'trans_colour' => $trans_colour,
    );
  }

  /**
   * create
   *
   * @throws \ImagickException
    * @return void
   */
  public function create() {
    if (!class_exists('Imagick')) {
      return;
    }

    if (!is_file($this->resource_file) || $this->destination_file === '') {
      return;
    }

    $this->webpWrittenInCreate = false;
    $this->jpegFlattenBackgroundColor = '#FFFFFF';

    $img = $this->createImagickInstance();
    try {
      $source_file = $this->correctImageOrientation($this->resource_file);
      $img->readImage($source_file);
      $dim    = $img->getImageGeometry();
      $width  = isset($dim['width']) ? (int)$dim['width'] : 0;
      $height = isset($dim['height']) ? (int)$dim['height'] : 0;

      if ($width <= 0 || $height <= 0) {
        return;
      }

      $target     = $this->calculateTargetSize($width, $height);
      $new_width  = $target['width'];
      $new_height = $target['height'];

      if ($new_width <= 0 || $new_height <= 0) {
        return;
      }

      $img->resizeImage($new_width, $new_height, $this->imagickConst('FILTER_LANCZOS', 22), 1.0, true);

      if ($img->getImageColorspace() == $this->imagickConst('COLORSPACE_CMYK', 12)) {
        $this->convertCmykToRgb($img);
      }

      // Interne Verarbeitung immer mit Alpha-Pipeline (RGBA), finales Flattening erst je nach Zielformat.
      $this->prepareImageForAlphaPipeline($img);

      $this->runProcessingPipeline($img);

      $this->image_type = $this->detectImageType($img, $this->destination_file);
      $write_target = $this->configureOutputFormatPolicy($img, $this->image_type, $this->destination_file);

      $this->finalizeImageForWrite($img, $this->image_type);

      $dir = dirname($this->destination_file);
      if (!$this->ensureDirectoryExists($dir, __METHOD__, (string)$this->destination_file)) {
        return;
      }

      $img->writeImage($write_target);
      if ($this->shouldWriteWebp() && $this->isImageTypeWebp($this->image_type)) {
        $this->webpWrittenInCreate = true;
      }
      $this->writeModernFormatsFromImage($img, __METHOD__);
    } catch (Exception $e) {
      $this->logImagickException($e, __METHOD__, (string)$this->resource_file);
      return;
    } finally {
      $img->clear();
      $img->destroy();
      $this->cleanupTemporarySource();
    }
  }

  /**
   * @param object $img
   * @return void
   */
  protected function runProcessingPipeline(&$img) {
    if ($this->transform !== '') {
      $this->manipulate();
    }

    if (!empty($this->effects_queue)) {
      $this->applyEffectOperations($img);
    }

    if (!empty($this->merge_data)) {
      $this->applyMergeOperations($img);
    }

    if (method_exists($img, 'setImagePage')) {
      $img->setImagePage(0, 0, 0, 0);
    }
  }

  /**
   * @param object $img
   * @param int $imageType
   * @param string $destination
   * @return string
   */
  protected function configureOutputFormatPolicy(&$img, $imageType, $destination) {
    $writeTarget = (string)$destination;

    if ((int)$imageType === IMAGETYPE_JPEG) {
      $img->setImageBackgroundColor($this->createImagickPixelInstance($this->jpegFlattenBackgroundColor));
      $flattened = $img->mergeImageLayers($this->imagickConst('LAYERMETHOD_FLATTEN', 11));
      if (is_object($flattened) && method_exists($flattened, 'clear') && method_exists($flattened, 'destroy')) {
        $img->clear();
        $img->destroy();
        $img = $flattened;
      }
      $img->setSamplingFactors(array('2x2', '1x1', '1x1'));
      $img->setInterlaceScheme($this->imagickConst('INTERLACE_PLANE', 4));
      $img->setImageFormat('jpeg');
      $img->setImageCompression($this->imagickConst('COMPRESSION_JPEG', 8));
      $writeTarget = 'jpeg:' . (string)$destination;
    } elseif ((int)$imageType === IMAGETYPE_PNG) {
      $img->setImageFormat('png');
      $img->setImageBackgroundColor($this->createImagickPixelInstance('transparent'));
      $img->setImageAlphaChannel($this->imagickConst('ALPHACHANNEL_SET', 1));
      $img->setImageCompression($this->imagickConst('COMPRESSION_ZIP', 14));
      $writeTarget = 'png:' . (string)$destination;
    } elseif ($this->isImageTypeWebp($imageType)) {
      $img->setImageFormat('webp');
      $img->setImageAlphaChannel($this->imagickConst('ALPHACHANNEL_SET', 1));
      $writeTarget = 'webp:' . (string)$destination;
    } elseif ($this->isImageTypeAvif($imageType)) {
      $img->setImageFormat('avif');
      $img->setImageAlphaChannel($this->imagickConst('ALPHACHANNEL_SET', 1));
      $writeTarget = 'avif:' . (string)$destination;
    } else {
      $img->setImageFormat('gif');
      $img->setImageAlphaChannel($this->imagickConst('ALPHACHANNEL_SET', 1));
      $writeTarget = 'gif:' . (string)$destination;
    }

    return $writeTarget;
  }

  /**
   * @param object $img
   * @param int $imageType
   * @return void
   */
  protected function finalizeImageForWrite($img, $imageType) {
    $this->applySharpen($img, $imageType);
    $img->setImageCompressionQuality($this->normalizeQuality($this->compression));
    $img->stripImage();
    if (method_exists($img, 'setImagePage')) {
      $img->setImagePage(0, 0, 0, 0);
    }
  }

  /**
   * Wird im Shop nach create() aufgerufen.
    *
    * @return void
   */
  public function createWebp() {
    if (!$this->shouldWriteWebp()) {
      return;
    }

    if ($this->webpWrittenInCreate) {
      return;
    }

    if (!class_exists('Imagick') || $this->destination_file === '' || !is_file($this->destination_file)) {
      return;
    }

    $img = $this->createImagickInstance();
    try {
      $img->readImage($this->destination_file);
      $this->writeDerivedFormatImage($img, 'webp', __METHOD__);
    } catch (Exception $e) {
      $this->logImagickException($e, __METHOD__, (string)$this->destination_file);
      return;
    } finally {
      $img->clear();
      $img->destroy();
    }
  }

  /**
   * Kompatibel zur GD-Klasse: parst den Transform-String in eine Effekt-Queue.
   *
   * @return void
   */
  public function manipulate() {
    if ($this->transform === null || trim($this->transform) === '') {
      return;
    }

    foreach ($this->splitTopLevel($this->transform) as $expression) {
      $this->applyManipulationExpression($expression);
    }
  }

  /**
   * @param int $edge_width
   * @param string $light_colour
   * @param string $dark_colour
   * @return void
   */
  public function bevel($edge_width = 10, $light_colour = 'FFFFFF', $dark_colour = '000000') {
    $this->effects_queue[] = array(
      'name' => 'bevel',
      'args' => array((int)$edge_width, (string)$light_colour, (string)$dark_colour),
    );
  }

  /**
   * @param int $rv
   * @param int $gv
   * @param int $bv
   * @return void
   */
  public function greyscale($rv = 0, $gv = 0, $bv = 0) {
    $this->effects_queue[] = array(
      'name' => 'greyscale',
      'args' => array((int)$rv, (int)$gv, (int)$bv),
    );
  }

  /**
   * @param string $bg_colour
   * @return void
   */
  public function ellipse($bg_colour = 'FFFFFF') {
    $this->effects_queue[] = array(
      'name' => 'ellipse',
      'args' => array((string)$bg_colour),
    );
  }

  /**
   * @param int $edge_rad
   * @param string $bg_colour
   * @param int $anti_alias
   * @return void
   */
  public function round_edges($edge_rad = 3, $bg_colour = 'FFFFFF', $anti_alias = 1) {
    $this->effects_queue[] = array(
      'name' => 'round_edges',
      'args' => array((int)$edge_rad, (string)$bg_colour, (int)$anti_alias),
    );
  }

  /**
   * @param string $light_colour
   * @param string $dark_colour
   * @param int $mid_width
   * @param string $frame_colour
   * @return void
   */
  public function frame($light_colour = 'FFFFFF', $dark_colour = '000000', $mid_width = 4, $frame_colour = '') {
    $this->effects_queue[] = array(
      'name' => 'frame',
      'args' => array((string)$light_colour, (string)$dark_colour, (int)$mid_width, (string)$frame_colour),
    );
  }

  /**
   * @param int $shadow_width
   * @param string $shadow_colour
   * @param string $shadow_backgroundcolor
   * @return void
   */
  public function drop_shadow($shadow_width, $shadow_colour = '000000', $shadow_backgroundcolor = 'FFFFFF') {
    $this->effects_queue[] = array(
      'name' => 'drop_shadow',
      'args' => array((int)$shadow_width, (string)$shadow_colour, (string)$shadow_backgroundcolor),
    );
  }

  /**
   * @param int $num_blur_lines
   * @param string $background_colour
   * @return void
   */
  public function motion_blur($num_blur_lines, $background_colour = 'FFFFFF') {
    $this->effects_queue[] = array(
      'name' => 'motion_blur',
      'args' => array((int)$num_blur_lines, (string)$background_colour),
    );
  }

  /**
   * Kompatibilitaetsmethode zur GD-Klasse.
   *
   * @param string $resource_file
   * @return string
   */
  public function correctImageOrientation($resource_file) {
    if (!class_exists('Imagick') || !is_file($resource_file)) {
      return $resource_file;
    }

    if (!function_exists('exif_imagetype')) {
      return $resource_file;
    }

    $imageType = $this->safeExifImageType($resource_file, __METHOD__, (string)$resource_file);
    if ($imageType !== IMAGETYPE_JPEG) {
      return $resource_file;
    }

    $img = $this->createImagickInstance();
    try {
      $img->readImage($resource_file);
      if (method_exists($img, 'autoOrient')) {
        $img->autoOrient();
      } elseif (method_exists($img, 'autoOrientImage')) {
        $img->autoOrientImage();
      } else {
        return $resource_file;
      }

      $temp = tempnam(sys_get_temp_dir(), 'imgim_');
      if ($temp === false) {
        return $resource_file;
      }

      $target = $temp . '.jpg';
      $this->safeUnlink($temp, __METHOD__, 'cleanup temp placeholder');
      $img->setImageFormat('jpeg');
      $img->setImageCompressionQuality(100);
      $img->writeImage($target);

      $this->temporary_source = $target;
      return $target;
    } catch (Exception $e) {
      $this->logImagickException($e, __METHOD__, (string)$resource_file);
      return $resource_file;
    } finally {
      $img->clear();
      $img->destroy();
    }
  }

  /**
   * Kompatibilitaetsmethode zur GD-Klasse.
   *
   * @return void
   */
  public function sharpen() {
    return;
  }

  /**
   * @param int $width
   * @param int $height
   * @return array{width:int,height:int}
   */
  protected function calculateTargetSize($width, $height) {
    $maxWidth = (int)$this->max_width;
    $maxHeight = (int)$this->max_height;

    if ($maxWidth <= 0) {
      $maxWidth = (int)$width;
    }
    if ($maxHeight <= 0) {
      $maxHeight = (int)$height;
    }

    if (defined('PRODUCT_IMAGE_NO_ENLARGE_UNDER_DEFAULT') && PRODUCT_IMAGE_NO_ENLARGE_UNDER_DEFAULT == 'false') {
      if ($width < $maxWidth) {
        $maxWidth = (int)$width;
      }
      if ($height < $maxHeight) {
        $maxHeight = (int)$height;
      }
    }

    $ratio = min($maxWidth / $width, $maxHeight / $height);
    if ($ratio <= 0) {
      $ratio = 1;
    }

    return array(
      'width' => max(1, (int)round($width * $ratio)),
      'height' => max(1, (int)round($height * $ratio)),
    );
  }

  /**
   * @param object $img
   * @param string $destination
   * @return int
   */
  protected function detectImageType($img, $destination) {
    $ext = strtolower((string)pathinfo($destination, PATHINFO_EXTENSION));

    if ($ext === 'png') {
      return IMAGETYPE_PNG;
    }
    if ($ext === 'gif') {
      return IMAGETYPE_GIF;
    }
    if ($ext === 'webp' && defined('IMAGETYPE_WEBP')) {
      return IMAGETYPE_WEBP;
    }
    if ($ext === 'avif' && defined('IMAGETYPE_AVIF')) {
      return IMAGETYPE_AVIF;
    }
    if ($ext === 'jpg' || $ext === 'jpeg' || $ext === 'jpe') {
      return IMAGETYPE_JPEG;
    }

    $fmt = strtolower((string)$img->getImageFormat());
    if (strpos($fmt, 'png') === 0) {
      return IMAGETYPE_PNG;
    }
    if (strpos($fmt, 'gif') === 0) {
      return IMAGETYPE_GIF;
    }
    if (strpos($fmt, 'webp') === 0 && defined('IMAGETYPE_WEBP')) {
      return IMAGETYPE_WEBP;
    }
    if (strpos($fmt, 'avif') === 0 && defined('IMAGETYPE_AVIF')) {
      return IMAGETYPE_AVIF;
    }
    if (strpos($fmt, 'jpg') === 0 || strpos($fmt, 'jpeg') === 0 || strpos($fmt, 'jpe') === 0) {
      return IMAGETYPE_JPEG;
    }

    return IMAGETYPE_JPEG;
  }

  /**
   * @param object $img
   * @return void
   */
  protected function applyEffectOperations(&$img) {
    foreach ($this->effects_queue as $effect) {
      if (!isset($effect['name'])) {
        continue;
      }

      $name = $effect['name'];
      $args = isset($effect['args']) && is_array($effect['args']) ? $effect['args'] : array();

      try {
        if ($name === 'greyscale') {
          $rv = isset($args[0]) ? (int)$args[0] : 0;
          $gv = isset($args[1]) ? (int)$args[1] : 0;
          $bv = isset($args[2]) ? (int)$args[2] : 0;
          $this->applyWeightedGreyscale($img, $rv, $gv, $bv);
          continue;
        }

        if ($name === 'bevel') {
          $edge = isset($args[0]) ? max(1, (int)$args[0]) : 10;
          $img->raiseImage($edge, $edge, 0, 0, true);
          continue;
        }

        if ($name === 'frame') {
          $width = isset($args[2]) ? max(1, (int)$args[2]) : 4;
          $color = isset($args[3]) && $args[3] !== '' ? $this->normalizeHexColor((string)$args[3], '#808080') : '#808080';
          $img->borderImage($this->createImagickPixelInstance($color), $width, $width);
          continue;
        }

        if ($name === 'drop_shadow') {
          $shadowColor = $this->normalizeHexColor(isset($args[1]) ? (string)$args[1] : '', '#000000');
          $this->registerJpegBackgroundColor(isset($args[2]) ? (string)$args[2] : '');
          $shadowWidth = isset($args[0]) ? max(1, (int)$args[0]) : 3;
          $shadowFade = isset($args[3]) ? max(20, min(100, (int)$args[3])) : 65;
          $this->applyDropShadowEffect($img, $shadowWidth, $shadowColor, $shadowFade);
          continue;
        }

        if ($name === 'motion_blur') {
          $lines = isset($args[0]) ? max(1, (int)$args[0]) : 2;
          $img->motionBlurImage(max(1, $lines * 2), 5, 45);
          continue;
        }

        if ($name === 'ellipse' || $name === 'round_edges') {
          if ($name === 'ellipse') {
            $this->registerJpegBackgroundColor(isset($args[0]) ? (string)$args[0] : '');
          } else {
            $this->registerJpegBackgroundColor(isset($args[1]) ? (string)$args[1] : '');
          }
          $this->applyRoundedMask($img, $name === 'round_edges' ? (isset($args[0]) ? max(1, (int)$args[0]) : 3) : 99999);
          continue;
        }
      } catch (Exception $e) {
        $this->logImagickException($e, __METHOD__, (string)$name);
        continue;
      }
    }
  }

  /**
   * @param object $img
   * @param int $rv
   * @param int $gv
   * @param int $bv
   * @return void
   */
  protected function applyWeightedGreyscale($img, $rv = 0, $gv = 0, $bv = 0) {
    $rWeight = max(0, (int)$rv);
    $gWeight = max(0, (int)$gv);
    $bWeight = max(0, (int)$bv);

    $sum = $rWeight + $gWeight + $bWeight;
    if ($sum <= 0) {
      return;
    }

    $wr = $rWeight / $sum;
    $wg = $gWeight / $sum;
    $wb = $bWeight / $sum;

    $iterator = $img->getPixelIterator();
    foreach ($iterator as $pixels) {
      foreach ($pixels as $pixel) {
        $color = $pixel->getColor(true);
        $r = isset($color['r']) ? (float)$color['r'] : 0.0;
        $g = isset($color['g']) ? (float)$color['g'] : 0.0;
        $b = isset($color['b']) ? (float)$color['b'] : 0.0;
        $gray = max(0.0, min(1.0, ($r * $wr) + ($g * $wg) + ($b * $wb)));

        $pixel->setColorValue($this->imagickConst('COLOR_RED', 1), $gray);
        $pixel->setColorValue($this->imagickConst('COLOR_GREEN', 2), $gray);
        $pixel->setColorValue($this->imagickConst('COLOR_BLUE', 3), $gray);
      }
      $iterator->syncIterator();
    }
  }

  /**
   * @param object $img
   * @return void
   */
  protected function convertCmykToRgb($img) {
    $profiles = $img->getImageProfiles('*', false);
    $has_icc_profile = (array_search('icc', $profiles) !== false);

    $icc_cmyk_path = $this->resolveIccProfilePath('IMAGEMANIPULATOR_ICC_PROFILE_CMYK', 'PSOcoated_v3.icc', 'CoatedFOGRA39.icc');
    $icc_rgb_path  = $this->resolveIccProfilePath('IMAGEMANIPULATOR_ICC_PROFILE_RGB', 'sRGB2014.icc', 'ColorMatchRGB.icc');

    if ($has_icc_profile === false && $icc_cmyk_path !== '' && is_file($icc_cmyk_path)) {
      $icc_cmyk = $this->safeFileGetContents($icc_cmyk_path, __METHOD__, 'cmyk profile');
      if ($icc_cmyk !== false) {
        $img->profileImage('icc', $icc_cmyk);
      }
    }

    if ($icc_rgb_path !== '' && is_file($icc_rgb_path)) {
      $icc_rgb = $this->safeFileGetContents($icc_rgb_path, __METHOD__, 'rgb profile');
      if ($icc_rgb !== false) {
        $img->profileImage('icc', $icc_rgb);
      }
    }

    if (method_exists($img, 'getQuantumRange')) {
      $range = $img->getQuantumRange();
      if (is_array($range)) {
        if (isset($range['quantumRangeLong'])) {
          $img->levelImage(0, 1.12, $range['quantumRangeLong']);
        } elseif (isset($range['quantumRangeString'])) {
          $img->levelImage(0, 1.12, $range['quantumRangeString']);
        }
      }
    }
  }

  /**
   * @param object $img
   * @return void
   */
  protected function applyMergeOperations($img) {
    foreach ($this->merge_data as $merge) {
      if (!isset($merge['file']) || !is_file($merge['file'])) {
        continue;
      }

      $overlay = $this->createImagickInstance();
      try {
        $overlay->readImage($merge['file']);

        $scale = isset($merge['scale']) ? (int)$merge['scale'] : 100;
        if ($scale !== 100) {
          $geo = $overlay->getImageGeometry();
          $ow = isset($geo['width']) ? (int)$geo['width'] : 0;
          $oh = isset($geo['height']) ? (int)$geo['height'] : 0;
          if ($ow > 0 && $oh > 0) {
            $rw = max(1, (int)round($ow * ($scale / 100)));
            $rh = max(1, (int)round($oh * ($scale / 100)));
            $overlay->resizeImage($rw, $rh, $this->imagickConst('FILTER_LANCZOS', 22), 1.0, true);
          }
        }

        if (isset($merge['trans_colour']) && is_string($merge['trans_colour'])) {
          $trans = '#' . strtoupper($merge['trans_colour']);
          $overlay->transparentPaintImage(
            $this->createImagickPixelInstance($trans),
            0.0,
            0,
            false
          );
        }

        $opacity = isset($merge['opacity']) ? (int)$merge['opacity'] : 100;
        if ($opacity < 100) {
          $overlay->setImageAlphaChannel($this->imagickConst('ALPHACHANNEL_SET', 1));
          $overlay->evaluateImage($this->imagickConst('EVALUATE_MULTIPLY', 2), $opacity / 100, $this->imagickConst('CHANNEL_ALPHA', 8));
        }

        $x = isset($merge['x']) ? (int)$merge['x'] : 0;
        $y = isset($merge['y']) ? (int)$merge['y'] : 0;

        // Legacy-Kompatibilitaet: negative Koordinaten sind Offsets von rechts/unten.
        if ($x < 0 || $y < 0) {
          $targetGeo = $img->getImageGeometry();
          $targetWidth = isset($targetGeo['width']) ? (int)$targetGeo['width'] : 0;
          $targetHeight = isset($targetGeo['height']) ? (int)$targetGeo['height'] : 0;
          if ($x < 0 && $targetWidth > 0) {
            $x = $targetWidth + $x;
          }
          if ($y < 0 && $targetHeight > 0) {
            $y = $targetHeight + $y;
          }
        }

        $img->compositeImage($overlay, $this->imagickConst('COMPOSITE_OVER', 40), $x, $y);
      } catch (Exception $e) {
        $this->logImagickException($e, __METHOD__, (string)$merge['file']);
        continue;
      } finally {
        $overlay->clear();
        $overlay->destroy();
      }
    }
  }

  /**
   * @param string $value
   * @return array<int, string>
   */
  protected function splitTopLevel($value) {
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
   * @param string $expression
   * @return void
   */
  protected function applyManipulationExpression($expression) {
    $expression = trim((string)$expression);
    if ($expression === '') {
      return;
    }

    if ($this->isQuoted($expression)) {
      $expression = substr($expression, 1, -1);
    }

    if (!preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s*(?:\((.*)\))?$/', $expression, $matches)) {
      return;
    }

    $method = $matches[1];
    if (!in_array($method, self::ALLOWED_MANIPULATIONS, true) || !method_exists($this, $method)) {
      return;
    }

    $arguments = array();
    if (isset($matches[2]) && trim($matches[2]) !== '') {
      foreach ($this->splitTopLevel($matches[2]) as $argument) {
        $arguments[] = $this->parseScalar($argument);
      }
    }

    call_user_func_array(array($this, $method), $arguments);
  }

  /**
   * @param string $value
   * @return mixed
   */
  protected function parseScalar($value) {
    $value = trim((string)$value);
    if ($this->isQuoted($value)) {
      return stripcslashes(substr($value, 1, -1));
    }

    $lower = strtolower($value);
    if ($lower === 'true') {
      return true;
    }
    if ($lower === 'false') {
      return false;
    }
    if ($lower === 'null') {
      return null;
    }
    if (is_numeric($value)) {
      return strpos($value, '.') !== false ? (float)$value : (int)$value;
    }

    return $value;
  }

  /**
   * @param string $value
   * @return bool
   */
  protected function isQuoted($value) {
    $length = strlen((string)$value);
    if ($length < 2) {
      return false;
    }
    $first = $value[0];
    $last = $value[$length - 1];
    return ($first === '\'' && $last === '\'') || ($first === '"' && $last === '"');
  }

  /**
   * @param object $img
   * @param int $radius
   * @return void
   */
  protected function applyRoundedMask($img, $radius) {
    $width = (int)$img->getImageWidth();
    $height = (int)$img->getImageHeight();
    if ($width <= 0 || $height <= 0) {
      return;
    }

    $mask = $this->createImagickInstance();
    $mask->newImage($width, $height, $this->createImagickPixelInstance('transparent'));
    $mask->setImageFormat('png');

    $draw = $this->createImagickDrawInstance();
    $draw->setFillColor($this->createImagickPixelInstance('white'));
    if ($radius >= min($width, $height)) {
      $draw->ellipse($width / 2, $height / 2, max(1, (int)($width / 2)), max(1, (int)($height / 2)), 0, 360);
    } else {
      $r = max(1, (int)$radius);
      $draw->roundRectangle(0, 0, $width - 1, $height - 1, $r, $r);
    }
    $mask->drawImage($draw);

    $img->setImageAlphaChannel($this->imagickConst('ALPHACHANNEL_SET', 1));
    $img->compositeImage($mask, $this->imagickConst('COMPOSITE_DSTIN', 30), 0, 0);

    $mask->clear();
    $mask->destroy();
  }

  /**
   * @param object $shadow
   * @param string $shadowColor
   * @return void
   */
  protected function colorizeShadowImage(&$shadow, $shadowColor) {
    $shadowColor = $this->normalizeHexColor((string)$shadowColor, '#000000');

    try {
      $alpha = clone $shadow;
      $alpha->separateImageChannel($this->imagickConst('CHANNEL_ALPHA', 8));

      $coloredShadow = $this->createImagickInstance();
      $coloredShadow->newImage(
        $shadow->getImageWidth(),
        $shadow->getImageHeight(),
        $this->createImagickPixelInstance($shadowColor)
      );
      $coloredShadow->setImageFormat('png');
      $coloredShadow->setImageAlphaChannel($this->imagickConst('ALPHACHANNEL_SET', 1));
      $coloredShadow->compositeImage($alpha, $this->imagickConst('COMPOSITE_COPYOPACITY', 19), 0, 0);

      $shadow->clear();
      $shadow->destroy();
      $shadow = $coloredShadow;

      $alpha->clear();
      $alpha->destroy();
    } catch (Exception $e) {
      $this->logImagickException($e, __METHOD__, 'drop_shadow colorize');
    }
  }

  /**
   * @param object $img
   * @param string $shadowColor
   * @param int $opacity
   * @param float $sigma
   * @param int $offset
   * @return object|null
   */
  protected function createDropShadowLayer($img, $shadowColor, $opacity, $sigma, $offset) {
    $shadow = null;

    try {
      $shadow = clone $img;

      if (method_exists($shadow, 'setImagePage')) {
        $shadow->setImagePage(0, 0, 0, 0);
      }

      $shadow->setImageBackgroundColor($this->createImagickPixelInstance($shadowColor));
      $shadow->setImageAlphaChannel($this->imagickConst('ALPHACHANNEL_SET', 1));

      $shadowResult = $shadow->shadowImage((int)$opacity, (float)$sigma, (int)$offset, (int)$offset);
      if ($shadowResult === false || !method_exists($shadow, 'getImageWidth') || !method_exists($shadow, 'getImageHeight')) {
        throw new RuntimeException('shadowImage() failed');
      }

      if (method_exists($shadow, 'setImagePage')) {
        $shadow->setImagePage(0, 0, 0, 0);
      }

      return $shadow;
    } catch (Exception $e) {
      if (is_object($shadow) && method_exists($shadow, 'clear') && method_exists($shadow, 'destroy')) {
        $shadow->clear();
        $shadow->destroy();
      }

      $this->logImagickException($e, __METHOD__, 'drop_shadow layer');
      return null;
    }
  }

  /**
   * @param object $img
   * @param int $shadowWidth
   * @param string $shadowColor
   * @param int $shadowFade
   * @return void
   */
  protected function applyDropShadowEffect(&$img, $shadowWidth, $shadowColor, $shadowFade = 65) {
    $shadowWidth = max(1, (int)$shadowWidth);
    $shadowFade = max(20, min(100, (int)$shadowFade));

    $shadowLayers = array();
    $foreground = null;
    $base = null;

    try {
      $foreground = clone $img;
      if (method_exists($foreground, 'setImagePage')) {
        $foreground->setImagePage(0, 0, 0, 0);
      }

      $layerSpecs = array(
        array(
          'opacity' => min(95, 78 + ($shadowWidth * 2)),
          'sigma' => max(0.8, round($shadowWidth * (0.10 + (($shadowFade / 100) * 0.12)), 1)),
          'offset' => max(1, (int)round($shadowWidth * 0.35)),
        ),
        array(
          'opacity' => min(78, 30 + (int)round($shadowWidth * (0.35 + (($shadowFade / 100) * 0.35)))),
          'sigma' => max(1.0, round($shadowWidth * (0.18 + (($shadowFade / 100) * 0.16)), 1)),
          'offset' => max(1, (int)round($shadowWidth * 0.65)),
        ),
        array(
          'opacity' => min(60, 16 + (int)round($shadowWidth * (0.18 + (($shadowFade / 100) * 0.22)))),
          'sigma' => max(1.2, round($shadowWidth * (0.24 + (($shadowFade / 100) * 0.20)), 1)),
          'offset' => max(1, (int)round($shadowWidth * 1.0)),
        ),
      );

      foreach ($layerSpecs as $layerSpec) {
        $shadowLayer = $this->createDropShadowLayer($img, $shadowColor, $layerSpec['opacity'], $layerSpec['sigma'], $layerSpec['offset']);
        if ($shadowLayer !== null) {
          $shadowLayers[] = $shadowLayer;
        }
      }

      if (count($shadowLayers) === 0) {
        throw new RuntimeException('drop_shadow layers could not be created');
      }

      $baseWidth = $foreground->getImageWidth();
      $baseHeight = $foreground->getImageHeight();
      foreach ($shadowLayers as $shadowLayer) {
        $baseWidth = max($baseWidth, $shadowLayer->getImageWidth());
        $baseHeight = max($baseHeight, $shadowLayer->getImageHeight());
      }

      $base = $this->createImagickInstance();
      $base->newImage($baseWidth, $baseHeight, $this->createImagickPixelInstance('transparent'));
      $base->setImageFormat($img->getImageFormat());

      foreach ($shadowLayers as $shadowLayer) {
        $shadowOffsetX = max(0, (int)round(($baseWidth - $shadowLayer->getImageWidth()) / 2));
        $shadowOffsetY = max(0, (int)round(($baseHeight - $shadowLayer->getImageHeight()) / 2));
        $base->compositeImage($shadowLayer, $this->imagickConst('COMPOSITE_OVER', 40), $shadowOffsetX, $shadowOffsetY);
      }

      $offsetX = max(0, (int)round(($baseWidth - $foreground->getImageWidth()) / 2));
      $offsetY = max(0, (int)round(($baseHeight - $foreground->getImageHeight()) / 2));
      $base->compositeImage($foreground, $this->imagickConst('COMPOSITE_OVER', 40), $offsetX, $offsetY);

      if (method_exists($base, 'setImagePage')) {
        $base->setImagePage(0, 0, 0, 0);
      }

      $img->clear();
      $img->destroy();
      $img = $base;
      $base = null;
    } catch (Exception $e) {
      $this->logImagickException($e, __METHOD__, 'drop_shadow');
    } finally {
      foreach ($shadowLayers as $shadowLayer) {
        if (is_object($shadowLayer) && method_exists($shadowLayer, 'clear') && method_exists($shadowLayer, 'destroy')) {
          $shadowLayer->clear();
          $shadowLayer->destroy();
        }
      }
      if (is_object($foreground) && method_exists($foreground, 'clear') && method_exists($foreground, 'destroy')) {
        $foreground->clear();
        $foreground->destroy();
      }
      if (is_object($base) && method_exists($base, 'clear') && method_exists($base, 'destroy')) {
        $base->clear();
        $base->destroy();
      }
    }
  }

  /**
   * @param object $img
   * @return void
   */
  protected function prepareImageForAlphaPipeline($img) {
    $img->setImageBackgroundColor($this->createImagickPixelInstance('transparent'));
    $img->setImageAlphaChannel($this->imagickConst('ALPHACHANNEL_SET', 1));
  }

  /**
   * @param string $color
   * @return void
   */
  protected function registerJpegBackgroundColor($color) {
    $color = trim((string)$color);
    if ($color === '') {
      return;
    }

    $this->jpegFlattenBackgroundColor = $this->normalizeHexColor($color, '#FFFFFF');
  }

  /**
   * @param string $color
   * @param string $fallback
   * @return string
   */
  protected function normalizeHexColor($color, $fallback = '#FFFFFF') {
    $hex = strtoupper(trim((string)$color));
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
      $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if (!preg_match('/^[0-9A-F]{6}$/', $hex)) {
      return $fallback;
    }
    return '#' . $hex;
  }

  /**
   * @param int|string|null $quality
   * @return int
   */
  protected function normalizeQuality($quality) {
    $q = (int)$quality;
    if ($q <= 0) {
      $q = defined('IMAGE_QUALITY') ? (int)IMAGE_QUALITY : 85;
    }
    return max(1, min(100, $q));
  }

  /**
   * @param object $img
   * @return void
   */
  protected function applySharpen($img, $image_type = IMAGETYPE_JPEG) {
    $sharpen = false;
    $sharpen_arr = null;
    $divisor = 1;
    $offset = 0;

    if (defined('DIR_FS_INC')) {
      $auto_include_file = DIR_FS_INC . 'auto_include.inc.php';
      if (is_file($auto_include_file)) {
        require_once($auto_include_file);
      }
    }

    if (defined('DIR_FS_ADMIN') && function_exists('auto_include')) {
      foreach (auto_include(DIR_FS_ADMIN . 'includes/extra/modules/image_sharpen/', 'php') as $file) {
        require($file);
      }
    }

    if ($sharpen === true && is_array($sharpen_arr) && count($sharpen_arr) === 3) {
      try {
        if (method_exists($img, 'convolveImage')) {
          $kernel = array();
          foreach ($sharpen_arr as $row) {
            if (is_array($row)) {
              foreach ($row as $value) {
                $kernel[] = (float)$value;
              }
            }
          }
          if (count($kernel) === 9) {
            $img->convolveImage($kernel);
            return;
          }
        }
      } catch (Exception $e) {
        $this->logImagickException($e, __METHOD__, 'convolve');
      }
    }

    if ((int)$image_type !== IMAGETYPE_JPEG) {
      return;
    }

    if (defined('IMAGE_QUALITY') && (int)IMAGE_QUALITY >= 60) {
      try {
        $img->unsharpMaskImage(0.8, 0.5, 1.0, 0.02);
      } catch (Exception $e) {
        $this->logImagickException($e, __METHOD__, 'unsharpMask');
      }
    }
  }

  /**
   * @param string $source_file
   * @return bool
   */
  protected function needsResize($source_file) {
    $info = $this->safeGetImageSize($source_file, __METHOD__, (string)$source_file);
    if (!is_array($info)) {
      return true;
    }

    $width = isset($info[0]) ? (int)$info[0] : 0;
    $height = isset($info[1]) ? (int)$info[1] : 0;

    if ($width <= 0 || $height <= 0) {
      return true;
    }

    $target = $this->calculateTargetSize($width, $height);
    if (!isset($target['width'], $target['height'])) {
      return true;
    }

    return ((int)$target['width'] !== $width || (int)$target['height'] !== $height);
  }

  /**
   * @param Exception $e
   * @param string $method
   * @param string $context
   * @return void
   */
  protected function logImagickException($e, $method, $context = '') {
    $message = 'Imagick Error [' . $method . ']';
    if ($context !== '') {
      $message .= ' [' . $context . ']';
    }
    $message .= ': ' . $e->getMessage();

    if (defined('DIR_FS_LOG')) {
      $written = error_log($message . PHP_EOL, 3, DIR_FS_LOG . 'imagick_error.log');
      if ($written !== false) {
        return;
      }
    }

    error_log($message);
  }

  /**
   * @param string $dir
   * @param string $method
   * @param string $context
   * @return bool
   */
  protected function ensureDirectoryExists($dir, $method, $context = '') {
    if ($dir === '' || is_dir($dir)) {
      return true;
    }

    if (mkdir($dir, 0775, true) || is_dir($dir)) {
      return true;
    }

    $this->logImagickException(new RuntimeException('Konnte Zielverzeichnis nicht erstellen: ' . $dir), $method, $context);
    return false;
  }

  /**
   * @param string $filePath
   * @param string $method
   * @param string $context
   * @return void
   */
  protected function safeUnlink($filePath, $method, $context = '') {
    if ($filePath === '' || !is_file($filePath)) {
      return;
    }

    if (!unlink($filePath)) {
      $this->logImagickException(new RuntimeException('Konnte Datei nicht loeschen: ' . $filePath), $method, $context);
    }
  }

  /**
   * @param string $filePath
   * @param string $method
   * @param string $context
   * @return string|false
   */
  protected function safeFileGetContents($filePath, $method, $context = '') {
    $errorMessage = '';
    set_error_handler(function ($severity, $message) use (&$errorMessage) {
      $errorMessage = (string)$message;
      return true;
    });

    try {
      $data = file_get_contents($filePath);
    } finally {
      restore_error_handler();
    }

    if ($data === false) {
      $message = $errorMessage !== '' ? $errorMessage : 'Konnte Datei nicht lesen: ' . $filePath;
      $this->logImagickException(new RuntimeException($message), $method, $context);
    }

    return $data;
  }

  /**
   * @param string $filePath
   * @param string $method
   * @param string $context
   * @return int|false
   */
  protected function safeExifImageType($filePath, $method, $context = '') {
    $errorMessage = '';
    set_error_handler(function ($severity, $message) use (&$errorMessage) {
      $errorMessage = (string)$message;
      return true;
    });

    try {
      $imageType = exif_imagetype($filePath);
    } finally {
      restore_error_handler();
    }

    if ($imageType === false && $errorMessage !== '') {
      $this->logImagickException(new RuntimeException($errorMessage), $method, $context);
    }

    return $imageType;
  }

  /**
   * @param string $filePath
   * @param string $method
   * @param string $context
   * @return array<int, mixed>|false
   */
  protected function safeGetImageSize($filePath, $method, $context = '') {
    $errorMessage = '';
    set_error_handler(function ($severity, $message) use (&$errorMessage) {
      $errorMessage = (string)$message;
      return true;
    });

    try {
      $info = getimagesize($filePath);
    } finally {
      restore_error_handler();
    }

    if ($info === false && $errorMessage !== '') {
      $this->logImagickException(new RuntimeException($errorMessage), $method, $context);
    }

    return $info;
  }

  /**
   * @return void
   */
  protected function cleanupTemporarySource() {
    if ($this->temporary_source !== null && is_file($this->temporary_source)) {
      $this->safeUnlink($this->temporary_source, __METHOD__, 'temporary source');
    }
    $this->temporary_source = null;
  }

  /**
   * @param object $img
   * @param string $method
   * @return void
   */
  protected function writeModernFormatsFromImage($img, $method) {
    if ($this->shouldWriteWebp() && !$this->isImageTypeWebp($this->image_type)) {
      if ($this->writeDerivedFormatImage($img, 'webp', $method)) {
        $this->webpWrittenInCreate = true;
      }
    }

    if ($this->shouldWriteAvif() && !$this->isImageTypeAvif($this->image_type)) {
      $this->writeDerivedFormatImage($img, 'avif', $method);
    }
  }

  /**
   * @param int $imageType
   * @return bool
   */
  protected function isImageTypeWebp($imageType) {
    return defined('IMAGETYPE_WEBP') && (int)$imageType === (int)IMAGETYPE_WEBP;
  }

  /**
   * @param int $imageType
   * @return bool
   */
  protected function isImageTypeAvif($imageType) {
    return defined('IMAGETYPE_AVIF') && (int)$imageType === (int)IMAGETYPE_AVIF;
  }

  /**
   * @return bool
   */
  protected function shouldWriteWebp() {
    if (!defined('IMAGE_TYPE_EXTENSION')) {
      return false;
    }

    return strtolower((string)IMAGE_TYPE_EXTENSION) === 'webp';
  }

  /**
   * @return bool
   */
  protected function shouldWriteAvif() {
    if (!defined('IMAGE_TYPE_EXTENSION')) {
      return false;
    }

    return strtolower((string)IMAGE_TYPE_EXTENSION) === 'avif';
  }

  /**
   * @param object $img
   * @param string $format
   * @param string $method
   * @return bool
   */
  protected function writeDerivedFormatImage($img, $format, $method) {
    if ($this->destination_file === null || $this->destination_file === '') {
      return false;
    }

    $format = strtolower((string)$format);
    $supported = $img->queryFormats(strtoupper($format));
    if (!is_array($supported) || count($supported) === 0) {
      $this->logImagickException(new RuntimeException('Format nicht von Imagick unterstuetzt: ' . $format), $method, (string)$this->destination_file);
      return false;
    }

    $target = $this->buildDerivedImagePath($this->destination_file, $format);
    $derived = clone $img;

    try {
      $derived->setImageFormat($format);
      $derived->setImageCompressionQuality($this->normalizeQuality($this->compression));
      $derived->writeImage($format . ':' . $target);
      return true;
    } catch (Exception $e) {
      $this->logImagickException($e, $method, $target);
      return false;
    } finally {
      $derived->clear();
      $derived->destroy();
    }
  }

  /**
   * @param string $filePath
   * @param string $targetExtension
   * @return string
   */
  protected function buildDerivedImagePath($filePath, $targetExtension) {
    $derived = preg_replace('/\.[^.]+$/', '.' . ltrim((string)$targetExtension, '.'), (string)$filePath);
    if ($derived === null || $derived === '') {
      return (string)$filePath . '.' . ltrim((string)$targetExtension, '.');
    }

    return $derived;
  }

  /**
   * @return object
   */
  protected function createImagickInstance() {
    $class = 'Imagick';
    return new $class();
  }

  /**
   * @param string $color
   * @return object
   */
  protected function createImagickPixelInstance($color) {
    $class = 'ImagickPixel';
    return new $class($color);
  }

  /**
   * @return object
   */
  protected function createImagickDrawInstance() {
    $class = 'ImagickDraw';
    return new $class();
  }

  /**
   * @param string $name
   * @param int $fallback
   * @return int
   */
  protected function imagickConst($name, $fallback) {
    $const = 'Imagick::' . $name;
    return defined($const) ? constant($const) : $fallback;
  }

  /**
   * @param string $constantName
   * @param string $defaultFile
   * @param string $legacyFallbackFile
   * @return string
   */
  protected function resolveIccProfilePath($constantName, $defaultFile, $legacyFallbackFile = '') {
    $baseDir = dirname(__FILE__) . '/ICC/';
    $configured = defined($constantName) ? trim((string)constant($constantName)) : '';

    if ($configured !== '') {
      $configured = basename($configured);
      $candidate = $baseDir . $configured;
      if (is_file($candidate)) {
        return $candidate;
      }
    }

    $defaultPath = $baseDir . basename((string)$defaultFile);
    if (is_file($defaultPath)) {
      return $defaultPath;
    }

    if ($legacyFallbackFile !== '') {
      $legacyPath = $baseDir . basename((string)$legacyFallbackFile);
      if (is_file($legacyPath)) {
        return $legacyPath;
      }
    }

    return '';
  }

  /**
   * @param string $destinationFile
   * @return string
   */
  protected function resolveAutoTransformForDestination($destinationFile) {
    $destination = $this->normalizePathForCompare($destinationFile);
    if ($destination === '') {
      return '';
    }

    $map = array(
      array('dir' => 'DIR_FS_CATALOG_INFO_IMAGES', 'transform' => 'PRODUCT_IMAGE_INFO_TRANSFORM'),
      array('dir' => 'DIR_FS_CATALOG_MIDI_IMAGES', 'transform' => 'PRODUCT_IMAGE_MIDI_TRANSFORM'),
      array('dir' => 'DIR_FS_CATALOG_MINI_IMAGES', 'transform' => 'PRODUCT_IMAGE_MINI_TRANSFORM'),
      array('dir' => 'DIR_FS_CATALOG_POPUP_IMAGES', 'transform' => 'PRODUCT_IMAGE_POPUP_TRANSFORM'),
      array('dir' => 'DIR_FS_CATALOG_THUMBNAIL_IMAGES', 'transform' => 'PRODUCT_IMAGE_THUMBNAIL_TRANSFORM'),
    );

    foreach ($map as $item) {
      if (!defined($item['dir']) || !defined($item['transform'])) {
        continue;
      }

      $targetDir = $this->normalizePathForCompare((string)constant($item['dir']));
      if ($targetDir === '') {
        continue;
      }

      if (strpos($destination, $targetDir) === 0) {
        return trim((string)constant($item['transform']));
      }
    }

    return '';
  }

  /**
   * @param string $path
   * @return string
   */
  protected function normalizePathForCompare($path) {
    $path = trim((string)$path);
    if ($path === '') {
      return '';
    }

    $normalized = str_replace('\\', '/', $path);
    $normalized = preg_replace('#/+#', '/', $normalized);
    if ($normalized === null) {
      return '';
    }

    return rtrim(strtolower($normalized), '/') . '/';
  }
}
