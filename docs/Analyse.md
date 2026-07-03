
Die PHP-Klasse image_manipulation in bx_image_magick.php dient der Bildbearbeitung und -manipulation in modified eCommerce. Sie ist als moderner und leistungsfähiger Ersatz (Drop-in-Replacement) für die klassische GD-basierte Verarbeitung konzipiert und nutzt Imagick.

---

### 1. Hauptmerkmale und aktueller Status

* Hochwertige Skalierung und Resizing: Die Klasse nutzt FILTER_LANCZOS für gute Verkleinerungsqualität.
* Farbraum-Konvertierung (CMYK zu RGB): CMYK-Bilder werden über ICC-Profile nach RGB konvertiert, um Browser-Farbabweichungen zu reduzieren.
* EXIF-Ausrichtungskorrektur: JPEGs werden bei Bedarf anhand der EXIF-Orientierung automatisch korrekt gedreht.
* Effekt-Pipeline: Transform-Strings werden geparst und in eine interne Effekt-Queue überführt.
* Merge-/Overlay-System: Wasserzeichen oder Logos können mit Position, Opazität, Skalierung und Transparenzfarbe eingeblendet werden.
* Moderne Formate:
  * WebP ist aktiv in der Ableitungs-Pipeline (konfigurationsabhängig).
  * AVIF ist im aktuellen Stand vorbereitet, aber in den relevanten Schreibpfaden derzeit deaktiviert (auskommentiert).
* Browser-Ausgabe oder Speichern:
  * Ausgabe an den Browser ist möglich (setOutputToBrowser(true)).
  * Wichtig: create() erwartet trotzdem eine gesetzte destination_file (nicht leer), sonst bricht die Methode früh ab.

---

### 2. Wichtige Methoden und Ablauf

#### A. Initialisierung: __construct

```php
function __construct($resource_file, $max_width, $max_height, $destination_file = '', $compression = null, $transform = '')
```

Parameter:

* $resource_file: Pfad zum Originalbild.
* $max_width / $max_height: Maximaldimensionen; Seitenverhältnis bleibt erhalten.
* $destination_file: Zielpfad für die Ausgabe.
* $compression: Qualitätswert (Standard aus IMAGE_QUALITY, sonst 85).
* $transform: Optionaler Transform-String.

Wenn $transform leer ist, versucht die Klasse einen Auto-Transform anhand des Zielpfads aufzulösen.
Optional kann die Verarbeitung direkt im Konstruktor starten (abhängig von MODULE_BX_IMAGE_MAGICK_AUTO_CREATE_ON_CONSTRUCT).

#### B. Pipeline-Start: create

create() führt die eigentliche Verarbeitung aus:

1. Prüfen, ob Imagick verfügbar ist.
2. Prüfen, ob Quelldatei existiert und destination_file gesetzt ist.
3. Orientierung korrigieren (correctImageOrientation).
4. Zielgröße berechnen und skalieren.
5. Falls nötig CMYK zu RGB konvertieren.
6. Alpha-Pipeline vorbereiten.
7. Effekte und Merge-Operationen anwenden.
8. Ausgabeformat konfigurieren, schreiben und ggf. moderne Ableitungen erzeugen.
9. Optional direkte Browser-Ausgabe statt Dateischreiben.

#### C. Merge-System: merge

```php
public function merge($merge_file, $x_pos = 0, $y_pos = 0, $opacity = 100, $scale_or_trans_colour = 'FF0000')
```

* Legt ein Overlay in der Merge-Queue ab.
* Negative x/y-Koordinaten werden als Offset von rechts bzw. unten interpretiert.

#### D. Manuelle Effekte registrieren

Unterstützt sind u. a.:

* greyscale()
* round_edges()
* drop_shadow($shadow_width, $shadow_colour, $shadow_backgroundcolor, $shadow_fade)
* ellipse()
* frame()
* bevel()
* motion_blur()

---

### 3. Hinweise zur Format-Erzeugung

* WebP-Ableitungen werden abhängig von der Konfiguration erzeugt.
* AVIF ist aktuell vorbereitet, aber in der aktiven Pipeline auskommentiert.
* createWebp() ist weiterhin als kompatibler Ergänzungsweg vorhanden.

---

### 4. Anwendungsbeispiele

#### Beispiel 1: Verkleinern und speichern

```php
$img = new image_manipulation(
    '/pfad/zu/original/bild.jpg',
    800,
    600,
    '/pfad/zu/ziel/bild.jpg'
);

$img->create();
```

#### Beispiel 2: Effekte und Wasserzeichen

```php
$img = new image_manipulation(
    '/pfad/zu/produkt.png',
    500,
    500,
    '/pfad/zu/ziel_produkt.png'
);

$img->round_edges(15, 'FFFFFF');
$img->drop_shadow(10, '000000', 'FFFFFF', 65);

$img->merge(
    '/pfad/zu/wasserzeichen.png',
    -10,
    -10,
    80
);

$img->create();
```

#### Beispiel 3: Transform-String

```php
$transformations = 'greyscale(8,8,8),round_edges(10,#ffffff),drop_shadow(5,#000000,#ffffff,60)';

$img = new image_manipulation(
    '/pfad/zu/bild.jpg',
    400,
    400,
    '/pfad/zu/ziel.jpg',
    90,
    $transformations
);

$img->create();
```

#### Beispiel 4: Direkte Ausgabe an den Browser (korrekter aktueller Stand)

Hinweis: destination_file muss gesetzt sein, auch wenn die Ausgabe an den Browser erfolgt.

```php
$img = new image_manipulation(
    '/pfad/zu/bild.jpg',
    200,
    200,
    '/tmp/preview.jpg'
);

$img->setOutputToBrowser(true)
    ->create();
exit;
```