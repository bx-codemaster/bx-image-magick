<?php
/**
 * Bildmanipulation via Imagick für modified eCommerce.
 *
 * Diese Klasse kapselt das Skalieren, Konvertieren und Nachbearbeiten von
 * Produktbildern auf Basis von Imagick. Sie bildet die bekannte Schnittstelle
 * der bisherigen Bildmanipulation nach, verarbeitet Transform-Strings in eine
 * interne Effekt-Queue und schreibt neben dem Zielbild bei Bedarf auch moderne
 * Ableitungen wie WebP oder AVIF.
 *
 * @file        bx_image_magick.php
 * @package     bx-image-magick
 * @author      bx-codemaster (benax)
 * @website     www.bx-coding.de
 * @license     GNU General Public License (GPL)
 * @since       2026-06-10
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

  // Steuert, ob das Bild direkt an den Browser ausgegeben werden soll
  /** @var bool */
  protected bool $output_to_browser = false;

  /**
   * Initialisiert eine Bildmanipulationsinstanz mit Quelle, Ziel und Optionen.
   *
   * Der Konstruktor übernimmt Dateipfade, Zielabmessungen, Qualitätsvorgaben
   * und optionale Transform-Definitionen. Wird kein Transform-String übergeben,
   * wird er anhand des Zielpfads automatisch aus der Bildgrößen-Konfiguration
   * ermittelt. Optional kann die Bildverarbeitung bereits beim Erzeugen des
   * Objekts sofort gestartet werden.
   *
   * @param string $resource_file Absoluter Pfad zur Quelldatei.
   * @param int $max_width Maximale Zielbreite für die Skalierung.
   * @param int $max_height Maximale Zielhöhe für die Skalierung.
   * @param string $destination_file Absoluter Pfad der Zieldatei.
   * @param int|null $compression Qualitätswert für die Ausgabe oder null für den Standardwert.
   * @param string $transform Optionaler Transform-String mit Effektdefinitionen.
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
   * Setter, um die Browser-Ausgabe zu aktivieren
   */
  public function setOutputToBrowser(bool $status = true): self {
      $this->output_to_browser = $status;
      return $this;
  }

  /**
   * Ermittelt den passenden HTTP-Mime-Type basierend auf dem internen Image-Type
   */
  protected function getMimeTypeFromImageType(string $image_type): string {
      // Da ich deine genaue detectImageType-Rückgabe nicht kenne (z.B. String oder Int-Konstante),
      // hier ein sicherer Fallback direkt über Imagick, falls dein Typ ein String ist:
      $type = strtolower((string)$image_type);
      
      if (strpos($type, 'png') !== false) return 'image/png';
      if (strpos($type, 'webp') !== false) return 'image/webp';
      if (strpos($type, 'gif') !== false) return 'image/gif';
      if (strpos($type, 'avif') !== false) return 'image/avif';
      
      return 'image/jpeg';
  }

  /**
   * Registriert eine Overlay-Datei für die spätere Merge-Verarbeitung.
   *
   * Die Methode bildet die aus dem Shop bekannte Merge-Signatur nach und legt
   * die gewünschte Overlay-Datei mit Position, Transparenz und optionaler
   * Skalierung oder Transparenzfarbe in der Merge-Queue ab. Die eigentliche
   * Verarbeitung erfolgt erst im Rahmen der Bildpipeline.
   *
   * @param string $merge_file Pfad zur einzublendenden Overlay-Datei.
   * @param int $x_pos Horizontale Zielposition oder negativer Offset von rechts.
   * @param int $y_pos Vertikale Zielposition oder negativer Offset von unten.
   * @param int $opacity Deckkraft des Overlays im Bereich von 0 bis 100.
   * @param int|string $scale_or_trans_colour Skalierungswert in Prozent oder Transparenzfarbe als Hex-Wert.
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
   * Führt die vollständige Bildverarbeitung und Ausgabe der Zieldatei aus.
   *
   * Die Methode lädt die Quelldatei, korrigiert bei Bedarf die Orientierung,
   * skaliert das Bild, konvertiert CMYK nach RGB, wendet alle registrierten
   * Effekte und Merges an und schreibt anschließend die Zieldatei im passenden
   * Format. Zusätzlich können moderne Ableitungen wie WebP oder AVIF erzeugt
   * werden. Bei Fehlern wird die Verarbeitung protokolliert und sauber beendet.
   *
   * @throws \ImagickException Kann von Imagick-nahen Operationen ausgelöst werden.
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

      // Browser-Ausgabe statt Dateisystem ---
      if ($this->output_to_browser) {
          // Passenden Content-Type Header anhand des erkannten Typs senden
          // (z.B. image/jpeg, image/png, image/webp)
          $mimeType = $this->getMimeTypeFromImageType($this->image_type);
          header("Content-Type: " . $mimeType);
          
          // Bild-Blob direkt ausgeben
          echo $img->getImageBlob();
          return; // Methode hier beenden, damit im 'finally' sauber zerstört wird
      }

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
   * Wendet Transform-Queue, Effekt-Queue und Merge-Queue auf ein Bild an.
   *
   * Falls ein Transform-String vorliegt, wird dieser zunächst in konkrete
   * Effekte übersetzt. Anschließend werden alle queued Effekte und Overlays auf
   * das übergebene Imagick-Objekt angewendet. Zum Schluss wird die virtuelle
   * Arbeitsfläche zurückgesetzt, damit keine Offsets in die Ausgabe durchrutschen.
   *
   * @param object $img Zu verarbeitende Imagick-Instanz.
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
   * Konfiguriert Formatpolitik und Schreibziel für das finale Ausgabebild.
   *
   * Abhängig vom erkannten Zieltyp werden Hintergrund, Alpha-Verhalten,
   * Interlacing, Kompression und das eigentliche Ausgabeformat vorbereitet. Für
   * JPEG wird das Bild vor dem Schreiben flachgerechnet, während PNG, WebP,
   * AVIF und GIF ihre Alpha-Informationen beibehalten.
   *
   * @param object $img Zu konfigurierende Imagick-Instanz.
   * @param int $imageType Erkannter Zieltyp anhand Dateiendung oder Bildformat.
   * @param string $destination Ursprünglicher Zielpfad.
   * @return string Schreibziel inklusive optionalem Formatpräfix für Imagick.
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
   * Finalisiert ein Bild unmittelbar vor dem Schreiben auf den Datenträger.
   *
   * Vor der Ausgabe werden Schärfung, Qualitätsstufe, Metadatenbereinigung und
   * das Zurücksetzen der virtuellen Bildfläche ausgeführt. Damit bleibt die
   * tatsächliche Ausgabe konsistent über alle unterstützten Formate hinweg.
   *
   * @param object $img Vorbereitetes Imagick-Bild.
   * @param int $imageType Zieltyp, der für formatabhängige Schärfung genutzt wird.
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
   * Erzeugt eine WebP-Ableitung aus der bereits geschriebenen Zieldatei.
   *
   * Die Methode bleibt zur Kompatibilität mit bestehenden Shop-Aufrufen
   * erhalten. Sie wird nur aktiv, wenn WebP als Ableitungsformat vorgesehen ist
   * und create() die WebP-Datei nicht bereits direkt mitgeschrieben hat.
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
   * Parst den aktuellen Transform-String in konkrete Effekte der Effekt-Queue.
   *
   * Die Methode sorgt für Kompatibilität zur bisherigen GD-basierten Klasse.
   * Jeder Ausdruck des Transform-Strings wird analysiert, validiert und in die
   * interne Effekt-Queue übernommen, damit die eigentliche Verarbeitung später
   * in einer einheitlichen Pipeline stattfinden kann.
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
   * Registriert einen Bevel-Effekt für die spätere Verarbeitung.
   *
   * Der Effekt wird nicht sofort ausgeführt, sondern mit seinen Parametern in
   * die interne Effekt-Queue geschrieben. Die Farbparameter bleiben dabei aus
   * Kompatibilitätsgründen erhalten, auch wenn Imagick den Effekt später nur
   * vereinfacht nachbildet.
   *
   * @param int $edge_width Breite der Kantenanhebung in Pixeln.
   * @param string $light_colour Helle Akzentfarbe des Effekts.
   * @param string $dark_colour Dunkle Akzentfarbe des Effekts.
   * @return void
   */
  public function bevel($edge_width = 10, $light_colour = 'FFFFFF', $dark_colour = '000000') {
    $this->effects_queue[] = array(
      'name' => 'bevel',
      'args' => array((int)$edge_width, (string)$light_colour, (string)$dark_colour),
    );
  }

  /**
   * Registriert einen gewichteten Greyscale-Effekt.
   *
   * Die drei Parameter definieren die relative Gewichtung der Farbkanäle für
   * die spätere Graustufenbildung. Die eigentliche Umrechnung erfolgt erst in
   * der Effektpipeline.
   *
   * @param int $rv Gewichtung des Rotkanals.
   * @param int $gv Gewichtung des Grünkanals.
   * @param int $bv Gewichtung des Blaukanals.
   * @return void
   */
  public function greyscale($rv = 0, $gv = 0, $bv = 0) {
    $this->effects_queue[] = array(
      'name' => 'greyscale',
      'args' => array((int)$rv, (int)$gv, (int)$bv),
    );
  }

  /**
   * Registriert einen elliptischen Beschnitt für das Zielbild.
   *
   * Die übergebene Hintergrundfarbe wird später für Formate ohne Transparenz
   * als JPEG-Hintergrund vorgemerkt, während der eigentliche Beschnitt über
   * eine gerundete Maske in der Effektpipeline erfolgt.
   *
   * @param string $bg_colour Hintergrundfarbe für flachgerechnete Ausgaben.
   * @return void
   */
  public function ellipse($bg_colour = 'FFFFFF') {
    $this->effects_queue[] = array(
      'name' => 'ellipse',
      'args' => array((string)$bg_colour),
    );
  }

  /**
   * Registriert abgerundete Ecken für das Zielbild.
   *
   * Gespeichert werden Radius, Hintergrundfarbe und ein Kompatibilitätswert für
   * Anti-Aliasing. Die eigentliche Maskierung wird später bei der Anwendung der
   * Effekte ausgeführt.
   *
   * @param int $edge_rad Radius der Rundung in Pixeln.
   * @param string $bg_colour Hintergrundfarbe für transparente Zielbereiche.
   * @param int $anti_alias Kompatibilitätsparameter zur historischen Signatur.
   * @return void
   */
  public function round_edges($edge_rad = 3, $bg_colour = 'FFFFFF', $anti_alias = 1) {
    $this->effects_queue[] = array(
      'name' => 'round_edges',
      'args' => array((int)$edge_rad, (string)$bg_colour, (int)$anti_alias),
    );
  }

  /**
   * Registriert einen Rahmeneffekt für das spätere Zielbild.
   *
   * Die Parameter werden unverändert in der Effekt-Queue abgelegt. Bei der
   * späteren Ausführung wird daraus ein einfacher Border-Effekt erzeugt, der die
   * historische Signatur der GD-Klasse nachbildet.
   *
   * @param string $light_colour Helle Rahmenfarbe aus der alten Schnittstelle.
   * @param string $dark_colour Dunkle Rahmenfarbe aus der alten Schnittstelle.
   * @param int $mid_width Rahmenbreite in Pixeln.
   * @param string $frame_colour Effektive Rahmenfarbe, sofern explizit gesetzt.
   * @return void
   */
  public function frame($light_colour = 'FFFFFF', $dark_colour = '000000', $mid_width = 4, $frame_colour = '') {
    $this->effects_queue[] = array(
      'name' => 'frame',
      'args' => array((string)$light_colour, (string)$dark_colour, (int)$mid_width, (string)$frame_colour),
    );
  }

  /**
   * Registriert einen Schatteneffekt für die Effektpipeline.
   *
   * Gespeichert werden die Schattenbreite sowie Vorder- und Hintergrundfarbe.
   * Die eigentliche Schattenkomposition erfolgt später über mehrere gestaffelte
   * Schattenebenen im Imagick-Workflow.
   *
   * @param int $shadow_width Breite des Schattens in Pixeln.
   * @param string $shadow_colour Farbe des Schattens als Hex-Wert.
   * @param string $shadow_backgroundcolor Hintergrundfarbe für Formate ohne Alpha-Kanal.
   * @return void
   */
  public function drop_shadow($shadow_width, $shadow_colour = '000000', $shadow_backgroundcolor = 'FFFFFF') {
    $this->effects_queue[] = array(
      'name' => 'drop_shadow',
      'args' => array((int)$shadow_width, (string)$shadow_colour, (string)$shadow_backgroundcolor),
    );
  }

  /**
   * Registriert einen Motion-Blur-Effekt für die spätere Verarbeitung.
   *
   * Die Anzahl der Blur-Linien wird für die Intensität des späteren Effekts
   * vorgemerkt. Die Hintergrundfarbe bleibt als Kompatibilitätsparameter in der
   * historischen Signatur erhalten.
   *
   * @param int $num_blur_lines Stärke des Bewegungsunschärfe-Effekts.
   * @param string $background_colour Historischer Hintergrundparameter der GD-Signatur.
   * @return void
   */
  public function motion_blur($num_blur_lines, $background_colour = 'FFFFFF') {
    $this->effects_queue[] = array(
      'name' => 'motion_blur',
      'args' => array((int)$num_blur_lines, (string)$background_colour),
    );
  }

  /**
   * Korrigiert die Bildorientierung anhand der EXIF-Daten einer JPEG-Datei.
   *
   * Für nicht unterstützte Dateitypen oder fehlende EXIF-Funktionen wird der
   * ursprüngliche Pfad unverändert zurückgegeben. Bei erfolgreicher Korrektur
   * wird eine temporäre JPEG-Datei erzeugt, deren Pfad in der Instanz für die
   * spätere Bereinigung vorgemerkt wird.
   *
   * @param string $resource_file Pfad zur ursprünglichen Quelldatei.
   * @return string Pfad zur korrigierten Datei oder zur Originaldatei.
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
   * Stellt die historische sharpen()-Signatur für Kompatibilitätsaufrufe bereit.
   *
   * Die eigentliche Schärfung wird im Imagick-Workflow zentral über
   * applySharpen() vor dem Schreiben des Bildes gesteuert. Diese Methode bleibt
   * daher bewusst leer und dient nur als stabile öffentliche API.
   *
   * @return void
   */
  public function sharpen() {
    return;
  }

  /**
   * Berechnet die endgültige Zielgröße unter Beibehaltung des Seitenverhältnisses.
   *
   * Als Grundlage dienen die konfigurierten Maximalmaße der Instanz. Optional
   * wird ein Vergrößern kleiner Bilder unterdrückt. Das Ergebnis enthält immer
   * mindestens ein Pixel pro Achse.
   *
   * @param int $width Ursprüngliche Bildbreite.
   * @param int $height Ursprüngliche Bildhöhe.
   * @return array{width:int,height:int} Berechnete Zielmaße für die Skalierung.
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
   * Ermittelt den Zielformattyp anhand Dateiendung und aktuellem Bildformat.
   *
   * Zunächst wird die Dateiendung des Zielpfads ausgewertet. Falls daraus kein
   * eindeutiges Ergebnis entsteht, dient das aktuell gesetzte Imagick-Format als
   * Fallback. Ohne Treffer wird JPEG als sicherer Standard angenommen.
   *
   * @param object $img Imagick-Instanz mit eventuell bereits gesetztem Format.
   * @param string $destination Zielpfad der Ausgabedatei.
   * @return int PHP-IMAGETYPE-Konstante des erkannten Zielformats.
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
   * Führt alle registrierten Effekte in definierter Reihenfolge auf dem Bild aus.
   *
   * Die Methode iteriert über die interne Effekt-Queue und ruft je nach Typ die
   * passende Imagick-Verarbeitung auf. Fehler einzelner Effekte werden isoliert
   * protokolliert, damit die restliche Pipeline weiterlaufen kann.
   *
   * @param object $img Imagick-Instanz, auf die die Effekte angewendet werden.
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
   * Wandelt ein Bild anhand gewichteter Farbkanäle in Graustufen um.
   *
   * Die Gewichtung der RGB-Kanäle wird aus den übergebenen Werten berechnet und
   * anschließend pixelweise auf das Bild angewendet. Sind alle Gewichte null,
   * wird das Bild unverändert belassen.
   *
   * @param object $img Zu bearbeitende Imagick-Instanz.
   * @param int $rv Gewichtung des Rotkanals.
   * @param int $gv Gewichtung des Grünkanals.
   * @param int $bv Gewichtung des Blaukanals.
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
   * Konvertiert ein CMYK-Bild über ICC-Profile in den RGB-Farbraum.
   *
   * Fehlende Profile werden nach Möglichkeit aus der Modulkonfiguration oder aus
   * Standarddateien geladen. Nach der Profiltransformation wird optional noch
   * eine leichte Level-Korrektur vorgenommen, um zu flache Ergebnisse zu
   * vermeiden.
   *
   * @param object $img Imagick-Instanz im CMYK-Farbraum.
   * @return void
   */
  protected function convertCmykToRgb($img) {
    $profiles        = $img->getImageProfiles('*', false);
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
   * Verarbeitet alle vorgemerkten Overlay-Merges auf dem aktuellen Bild.
   *
   * Jedes Overlay kann optional skaliert, teiltransparent gemacht und über eine
   * definierte Transparenzfarbe freigestellt werden. Negative Koordinaten werden
   * kompatibel zur Legacy-Implementierung als Offsets von rechts oder unten
   * interpretiert.
   *
   * @param object $img Zielbild, auf das alle Overlays komponiert werden.
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
   * Trennt einen Transform-Ausdruck an Kommata der obersten Klammer-Ebene.
   *
   * Kommas in verschachtelten Klammern oder innerhalb von Quotes bleiben dabei
   * erhalten, sodass komplexere Effektargumente sicher in Einzelausdrücke
   * zerlegt werden können.
   *
   * @param string $value Kompletter Transform-String oder Argumentausdruck.
   * @return array<int, string> Liste der extrahierten Top-Level-Ausdrücke.
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
   * Überführt einen einzelnen Transform-Ausdruck in einen Methodenaufruf.
   *
   * Der Ausdruck wird syntaktisch geprüft, auf erlaubte Manipulationen
   * beschränkt und anschließend mit geparsten Skalarwerten gegen die passende
   * Effektmethode der Klasse aufgerufen.
   *
   * @param string $expression Einzelner Effekt-Ausdruck aus dem Transform-String.
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
   * Wandelt einen String aus dem Transform-Parser in einen skalaren PHP-Wert um.
   *
   * Unterstützt werden gequotete Strings, Booleans, null sowie numerische
   * Werte. Alles andere wird unverändert als String zurückgegeben.
   *
   * @param string $value Rohwert eines Arguments aus dem Transform-Ausdruck.
   * @return mixed Normalisierter Skalarwert für den Methodenaufruf.
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
   * Prüft, ob ein String vollständig von passenden Quotes umschlossen ist.
   *
   * Die Methode erkennt sowohl einfache als auch doppelte Anführungszeichen und
   * dient dem Parser als kleine Hilfsfunktion beim Entpacken von Stringwerten.
   *
   * @param string $value Zu prüfender Ausdruck.
   * @return bool True, wenn der Ausdruck gequotet ist, sonst false.
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
   * Legt eine gerundete Maske über das Bild und beschneidet dessen Alpha-Kanal.
   *
   * Bei sehr großem Radius wird eine Ellipse erzeugt, ansonsten ein Rechteck
   * mit runden Ecken. Die Maske wird anschließend über den Ziel-Alpha-Kanal auf
   * das Bild angewendet.
   *
   * @param object $img Zu maskierende Imagick-Instanz.
   * @param int $radius Radius der Rundung oder großer Wert für eine Ellipse.
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
   * Färbt eine vorbereitete Schattenebene anhand ihres Alpha-Kanals ein.
   *
   * Dazu wird zunächst der Alpha-Kanal separiert und anschließend auf eine neue,
   * vollflächige Ebene in der gewünschten Schattenfarbe übertragen. Die
   * ursprüngliche Schatteninstanz wird durch das neu colorierte Bild ersetzt.
   *
   * @param object $shadow Referenz auf die zu färbende Schattenebene.
   * @param string $shadowColor Gewünschte Schattenfarbe als Hex-Wert.
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
   * Erzeugt aus dem aktuellen Bild eine einzelne weichgezeichnete Schattenebene.
   *
   * Das Bild wird geklont, mit Schattenfarbe und Alpha-Konfiguration versehen
   * und anschließend über shadowImage() in eine versetzte Schattenebene
   * umgewandelt. Bei Fehlern liefert die Methode null und räumt erzeugte Objekte
   * sauber wieder auf.
   *
   * @param object $img Vorlage für die Schattenebene.
   * @param string $shadowColor Farbe des Schattens.
   * @param int $opacity Deckkraft des Schattens.
   * @param float $sigma Weichzeichnungsstärke des Schattens.
   * @param int $offset Versatz des Schattens in X- und Y-Richtung.
   * @return object|null Fertige Schattenebene oder null bei Fehlern.
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
   * Baut einen mehrlagigen Schatteneffekt und komponiert ihn mit dem Bild.
   *
   * Für einen weicheren Schatten werden mehrere Ebenen mit unterschiedlicher
   * Deckkraft, Unschärfe und Verschiebung erzeugt und auf einer transparenten
   * Arbeitsfläche zusammengesetzt. Am Ende ersetzt die neue Komposition das
   * bisherige Bildobjekt.
   *
   * @param object $img Referenz auf das zu erweiternde Zielbild.
   * @param int $shadowWidth Grundbreite des Schattens in Pixeln.
   * @param string $shadowColor Schattenfarbe als Hex-Wert.
   * @param int $shadowFade Verlaufslänge des Schattens im Bereich 20 bis 100.
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
   * Bereitet ein Bild für die interne Alpha-Pipeline vor.
   *
   * Der Hintergrund wird auf transparent gesetzt und der Alpha-Kanal explizit
   * aktiviert, damit nachgelagerte Effekte wie Masken oder Schatten konsistent
   * auf transparenten Zwischenständen arbeiten können.
   *
   * @param object $img Zu initialisierende Imagick-Instanz.
   * @return void
   */
  protected function prepareImageForAlphaPipeline($img) {
    $img->setImageBackgroundColor($this->createImagickPixelInstance('transparent'));
    $img->setImageAlphaChannel($this->imagickConst('ALPHACHANNEL_SET', 1));
  }

  /**
   * Merkt eine Hintergrundfarbe für spätere JPEG-Ausgaben vor.
   *
   * Leere Werte werden ignoriert. Gültige Farben werden normalisiert und als
   * Standardhintergrund für das spätere Flattening von Formaten ohne Alpha
   * gespeichert.
   *
   * @param string $color Farbwert aus einem Effektparameter.
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
   * Normalisiert einen Farbwert auf das Format #RRGGBB.
   *
   * Drei- und sechsstellige Hex-Werte werden akzeptiert. Ungültige Eingaben
   * führen zum übergebenen Fallback, damit alle nachgelagerten Bildoperationen
   * mit einem sicheren Farbwert arbeiten.
   *
   * @param string $color Zu prüfender Farbwert.
   * @param string $fallback Rückgabefarbe bei ungültiger Eingabe.
   * @return string Normalisierte Hex-Farbe inklusive führendem #.
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
   * Wandelt eine Qualitätsangabe in einen gültigen Ausgabewert um.
   *
   * Nicht gesetzte oder ungültige Qualitätswerte werden auf die Shop-Konstante
   * IMAGE_QUALITY beziehungsweise einen sicheren Standardwert zurückgeführt.
   * Das Ergebnis wird immer auf den Bereich 1 bis 100 begrenzt.
   *
   * @param int|string|null $quality Eingabewert aus Konfiguration oder Aufruf.
   * @return int Normalisierte Qualitätsstufe für die Ausgabe.
   */
  protected function normalizeQuality($quality) {
    $q = (int)$quality;
    if ($q <= 0) {
      $q = defined('IMAGE_QUALITY') ? (int)IMAGE_QUALITY : 85;
    }
    return max(1, min(100, $q));
  }

  /**
   * Wendet die konfigurierte Schärfung passend zum Zielformat an.
   *
   * Falls ein externes Sharpen-Modul vorhanden ist, wird dessen Kernel bevorzugt
   * über convolveImage() verwendet. Andernfalls erhält JPEG-Ausgabe ab einer
   * ausreichenden Qualitätsstufe eine moderate Unsharp-Mask-Nachschärfung.
   *
   * @param object $img Zu schärfende Imagick-Instanz.
   * @param int $image_type Zieltyp des Bildes.
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
   * Prüft, ob eine Quelldatei gegenüber den Zielmaßen skaliert werden muss.
   *
   * Dazu werden zunächst die Originalabmessungen sicher gelesen und anschließend
   * mit den durch calculateTargetSize() ermittelten Zielmaßen verglichen.
   * Fehler beim Lesen führen vorsorglich zu true.
   *
   * @param string $source_file Pfad zur Quelldatei.
   * @return bool True, wenn eine Größenänderung erforderlich ist, sonst false.
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
   * Protokolliert eine Ausnahme aus dem Imagick-Workflow.
   *
   * Die Meldung wird bevorzugt in die Imagick-Logdatei im Shop-Logverzeichnis
   * geschrieben. Falls das nicht möglich ist, erfolgt ein Fallback auf das
   * allgemeine PHP-Error-Log.
   *
   * @param Exception $e Auszulösende oder abgefangene Ausnahme.
   * @param string $method Methodenname oder technischer Ursprung der Meldung.
   * @param string $context Optionaler Zusatzkontext, etwa Dateipfad oder Effektname.
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
   * Stellt sicher, dass ein Zielverzeichnis für die Ausgabe existiert.
   *
   * Existierende Verzeichnisse werden akzeptiert, fehlende rekursiv angelegt.
   * Schlägt das Anlegen fehl, wird der Fehler protokolliert und false
   * zurückgegeben.
   *
   * @param string $dir Zu prüfendes oder anzulegendes Verzeichnis.
   * @param string $method Aufrufender Methodenname für das Logging.
   * @param string $context Optionaler Zusatzkontext für die Protokollierung.
   * @return bool True bei vorhandenem oder erfolgreich angelegtem Verzeichnis.
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
   * Löscht eine Datei sicher und protokolliert fehlgeschlagene Löschversuche.
   *
   * Nicht vorhandene Dateien werden still ignoriert. Fehler beim Entfernen einer
   * existierenden Datei werden als RuntimeException in das Modul-Logging
   * übernommen.
   *
   * @param string $filePath Zu löschender Dateipfad.
   * @param string $method Aufrufende Methode für die Fehlerprotokollierung.
   * @param string $context Optionaler Zusatzkontext für das Logging.
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
   * Liest eine Datei mit abgefangenen PHP-Warnungen sicher ein.
   *
   * Temporäre Error-Handler sammeln Laufzeitwarnungen ein und wandeln sie bei
   * Fehlschlag in eine protokollierte RuntimeException um. So bleibt der Aufrufer
   * frei von direkten PHP-Warnungen im Ausgabekontext.
   *
   * @param string $filePath Zu lesender Dateipfad.
   * @param string $method Aufrufende Methode für die Fehlerprotokollierung.
   * @param string $context Optionaler Zusatzkontext für das Logging.
   * @return string|false Dateiinhalt oder false bei Fehlern.
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
   * Ermittelt den EXIF-Bildtyp einer Datei mit abgefangenen Warnungen.
   *
   * Fehler der internen PHP-Funktion werden gesammelt und bei Bedarf über das
   * Modul-Logging protokolliert, statt ungefiltert im Laufzeitkontext zu landen.
   *
   * @param string $filePath Pfad der zu prüfenden Datei.
   * @param string $method Aufrufende Methode für die Fehlerprotokollierung.
   * @param string $context Optionaler Zusatzkontext für das Logging.
   * @return int|false Erkannter IMAGETYPE-Wert oder false bei Fehlern.
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
   * Liest Bildinformationen über getimagesize() mit eigener Fehlerbehandlung.
   *
   * Warnungen werden temporär abgefangen und bei Fehlschlägen über das interne
   * Logging protokolliert. Erfolgreiche Aufrufe liefern die üblichen Dimensionen
   * und Metadaten von getimagesize().
   *
   * @param string $filePath Pfad zur zu analysierenden Bilddatei.
   * @param string $method Aufrufende Methode für die Fehlerprotokollierung.
   * @param string $context Optionaler Zusatzkontext für das Logging.
   * @return array<int, mixed>|false Bildinformationen oder false bei Fehlern.
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
   * Entfernt eine temporär erzeugte Quelldatei und setzt den Status zurück.
   *
   * Wird während der Orientierungskorrektur eine temporäre Datei angelegt,
   * übernimmt diese Methode deren Bereinigung nach Abschluss der Verarbeitung.
   * Anschließend wird die interne Referenz zurückgesetzt.
   *
   * @return void
   */
  protected function cleanupTemporarySource() {
    if ($this->temporary_source !== null && is_file($this->temporary_source)) {
      $this->safeUnlink($this->temporary_source, __METHOD__, 'temporary source');
    }
    $this->temporary_source = null;
  }

  /**
   * Schreibt aus dem aktuellen Bildzustand zusätzliche moderne Ausgabeformate.
   *
   * Abhängig von der Konfiguration werden WebP- und AVIF-Ableitungen nur dann
   * erzeugt, wenn das Zielformat nicht ohnehin bereits diesem Typ entspricht.
   * Erfolgreiche WebP-Schreibvorgänge markieren den internen Status entsprechend.
   *
   * @param object $img Bereits verarbeitetes Ausgangsbild.
   * @param string $method Aufrufende Methode für Logging und Fehlerkontext.
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
   * Prüft, ob ein IMAGETYPE-Wert dem WebP-Format entspricht.
   *
   * Die Prüfung berücksichtigt, dass die WebP-Konstante nur in unterstützten
   * Umgebungen definiert ist.
   *
   * @param int $imageType Zu prüfender Bildtyp.
   * @return bool True bei WebP, sonst false.
   */
  protected function isImageTypeWebp($imageType) {
    return defined('IMAGETYPE_WEBP') && (int)$imageType === (int)IMAGETYPE_WEBP;
  }

  /**
   * Prüft, ob ein IMAGETYPE-Wert dem AVIF-Format entspricht.
   *
   * Die Prüfung wird nur dann positiv, wenn die AVIF-Konstante in der aktuellen
   * PHP-Umgebung überhaupt verfügbar ist.
   *
   * @param int $imageType Zu prüfender Bildtyp.
   * @return bool True bei AVIF, sonst false.
   */
  protected function isImageTypeAvif($imageType) {
    return defined('IMAGETYPE_AVIF') && (int)$imageType === (int)IMAGETYPE_AVIF;
  }

  /**
   * Prüft, ob gemäß Shop-Konfiguration eine WebP-Ausgabe gewünscht ist.
   *
   * Grundlage ist die Konstante IMAGE_TYPE_EXTENSION. Ist sie nicht gesetzt
   * oder enthält sie einen anderen Wert, wird false zurückgegeben.
   *
   * @return bool True, wenn WebP als Ziel- oder Ableitungsformat aktiv ist.
   */
  protected function shouldWriteWebp() {
    if (!defined('IMAGE_TYPE_EXTENSION')) {
      return false;
    }

    return strtolower((string)IMAGE_TYPE_EXTENSION) === 'webp';
  }

  /**
   * Prüft, ob gemäß Shop-Konfiguration eine AVIF-Ausgabe gewünscht ist.
   *
   * Grundlage ist die Konstante IMAGE_TYPE_EXTENSION. Nur der explizite Wert
   * avif aktiviert die zusätzliche oder direkte AVIF-Erzeugung.
   *
   * @return bool True, wenn AVIF als Ziel- oder Ableitungsformat aktiv ist.
   */
  protected function shouldWriteAvif() {
    if (!defined('IMAGE_TYPE_EXTENSION')) {
      return false;
    }

    return strtolower((string)IMAGE_TYPE_EXTENSION) === 'avif';
  }

  /**
   * Schreibt eine zusätzliche Format-Ableitung auf Basis des aktuellen Bildes.
   *
   * Vor dem Schreiben wird geprüft, ob Imagick das gewünschte Format überhaupt
   * unterstützt. Anschließend wird ein Klon des Bildes in das Zielformat
   * überführt und unter einem abgeleiteten Dateinamen gespeichert.
   *
   * @param object $img Ausgangsbild für die Ableitung.
   * @param string $format Gewünschtes Zielformat wie webp oder avif.
   * @param string $method Aufrufender Methodenname für Logging und Fehlerkontext.
   * @return bool True bei erfolgreichem Schreiben, sonst false.
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
   * Erzeugt den Dateipfad für eine abgeleitete Bilddatei mit neuer Endung.
   *
   * Existiert bereits eine Dateiendung, wird sie ersetzt. Andernfalls wird die
   * gewünschte Zielerweiterung an den ursprünglichen Pfad angehängt.
   *
   * @param string $filePath Ausgangspfad des Original- oder Zielbildes.
   * @param string $targetExtension Gewünschte neue Dateierweiterung.
   * @return string Pfad der abgeleiteten Ausgabedatei.
   */
  protected function buildDerivedImagePath($filePath, $targetExtension) {
    $derived = preg_replace('/\.[^.]+$/', '.' . ltrim((string)$targetExtension, '.'), (string)$filePath);
    if ($derived === null || $derived === '') {
      return (string)$filePath . '.' . ltrim((string)$targetExtension, '.');
    }

    return $derived;
  }

  /**
   * Erzeugt eine neue Imagick-Instanz über dynamische Klassennamenauflösung.
   *
   * Die indirekte Instanziierung vereinfacht Kompatibilität mit statischer
   * Analyse und hält die Erstellung der Kernklasse an einer zentralen Stelle.
   *
   * @return object Neue Imagick-Instanz.
   */
  protected function createImagickInstance() {
    $class = 'Imagick';
    return new $class();
  }

  /**
   * Erzeugt ein ImagickPixel-Objekt für einen gegebenen Farbwert.
   *
   * Die Hilfsmethode bündelt die Pixelerzeugung zentral, damit Farbangaben in
   * der gesamten Klasse konsistent in ImagickPixel-Instanzen überführt werden.
   *
   * @param string $color Farbdefinition für das neue Pixelobjekt.
   * @return object Neue ImagickPixel-Instanz.
   */
  protected function createImagickPixelInstance($color) {
    $class = 'ImagickPixel';
    return new $class($color);
  }

  /**
   * Erzeugt ein neues ImagickDraw-Objekt für Masken und Vektoroperationen.
   *
   * Die Erstellung wird über diese Hilfsmethode zentralisiert, damit Zeichen-
   * operationen an einer Stelle gekapselt bleiben.
   *
   * @return object Neue ImagickDraw-Instanz.
   */
  protected function createImagickDrawInstance() {
    $class = 'ImagickDraw';
    return new $class();
  }

  /**
   * Liest eine Imagick-Konstante sicher aus und liefert andernfalls einen Fallback.
   *
   * So kann die Klasse auch in Umgebungen arbeiten, in denen bestimmte
   * Imagick-Konstanten nicht definiert sind oder je nach Version abweichen.
   *
   * @param string $name Name der Imagick-Konstante ohne Klassenpräfix.
   * @param int $fallback Ersatzwert bei fehlender Konstante.
   * @return int Aufgelöster Konstantenwert oder der Fallback.
   */
  protected function imagickConst($name, $fallback) {
    $const = 'Imagick::' . $name;
    return defined($const) ? constant($const) : $fallback;
  }

  /**
   * Ermittelt den Pfad zu einem ICC-Profil aus Konfiguration und Fallbacks.
   *
   * Zuerst wird ein konfigurierter Dateiname geprüft, danach das Standardprofil
   * und optional ein Legacy-Fallback. Nur tatsächlich vorhandene Dateien werden
   * als Ergebnis zurückgegeben.
   *
   * @param string $constantName Name der Konstante mit dem konfigurierten Profilnamen.
   * @param string $defaultFile Standarddatei für moderne Installationen.
   * @param string $legacyFallbackFile Optionales Ausweichprofil für ältere Setups.
   * @return string Absoluter Pfad zum gefundenen Profil oder ein leerer String.
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
   * Leitet aus dem Zielpfad automatisch den passenden Transform-String ab.
   *
   * Dazu wird der Zielpfad normiert und mit den bekannten Bildverzeichnissen
   * des Shops verglichen. Bei einem Treffer wird die zugehörige Transform-
   * Konstante der jeweiligen Bildgröße zurückgegeben.
   *
   * @param string $destinationFile Zielpfad des auszugebenden Bildes.
   * @return string Ermittelter Transform-String oder ein leerer String.
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
   * Normalisiert einen Dateipfad für robuste Präfixvergleiche.
   *
   * Backslashes werden vereinheitlicht, doppelte Trenner reduziert, die
   * Schreibweise auf Kleinbuchstaben gebracht und ein abschließender Slash
   * ergänzt. Leere oder unbrauchbare Eingaben ergeben einen leeren String.
   *
   * @param string $path Zu normalisierender Dateipfad.
   * @return string Vergleichstauglicher Normalform-Pfad.
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
