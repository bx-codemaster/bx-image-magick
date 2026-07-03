# Handbuch: Klasse image_manipulation

## Zweck der Klasse

Die Klasse `image_manipulation` verarbeitet Bilder mit Imagick:
- Skalierung auf Zielmaße
- optionale Effekte (z. B. `round_edges`, `drop_shadow`)
- optionale Overlays per `merge()`
- Schreiben des Zielformats (JPG/PNG/GIF/WebP)
- optionales Ableiten moderner Formate

Hinweis zum aktuellen Stand:
- AVIF ist im Code vorbereitet, aber in der aktiven Ausgabe-Pipeline derzeit deaktiviert.

Klasse: `src/admin/includes/classes/bx_image_magick.php`

## Kurzüberblick: Welche Methode wird wann aufgerufen?

Es gibt zwei relevante Aufrufmuster.

### Muster A: Shop-Standard (wie in admin/includes)

1. `new image_manipulation(...)`
2. optional `merge(...)`
3. `create()`
4. optional `createWebp()`

Wichtig:
- Standardmäßig wird im Konstruktor kein `create()` mehr aufgerufen.
- Dadurch ist der Ablauf eindeutig: Objekt erstellen, optional konfigurieren, dann genau einmal `create()` aufrufen.
- Legacy-Option: Über `MODULE_BX_IMAGE_MAGICK_AUTO_CREATE_ON_CONSTRUCT` kann das alte Verhalten wieder aktiviert werden.

### Muster B: Kontrollierter eigener Ablauf (empfohlen für neue Integrationen)

1. Objekt ohne Zielpfad erzeugen (kein Auto-Create)
2. Effekte/Merge konfigurieren
3. Zielpfad setzen
4. `create()` aufrufen
5. optional `createWebp()`

So vermeidest du doppelte Verarbeitung.

## Öffentliche Methoden im Alltag

### `__construct($resource_file, $max_width, $max_height, $destination_file = '', $compression = null, $transform = '')`

Bedeutung:
- `$resource_file`: Quellbild
- `$max_width`, `$max_height`: Zielgrenzen
- `$destination_file`: Zieldatei
- `$compression`: Qualitätswert 1..100 (Default aus `IMAGE_QUALITY` oder 85)
- `$transform`: Transform-String, z. B. `"round_edges(6),drop_shadow(3)"`

Hinweis:
- Optionales Legacy-Verhalten per Konstante `MODULE_BX_IMAGE_MAGICK_AUTO_CREATE_ON_CONSTRUCT`.

### `create()`

Hauptpipeline:
1. Quelle laden
2. Orientierung korrigieren (JPEG/EXIF)
3. Zielgröße berechnen
4. Bild skalieren
5. ggf. CMYK -> RGB konvertieren
6. Transform-String parsen (`manipulate()`)
7. Queue-Effekte anwenden
8. Merge-Overlays anwenden
9. Bild schreiben (Format aus Bild/Dateiendung)
10. optional moderne Formate aus demselben Bild schreiben

### `createWebp()`

Kompatibilitätsmethode für Legacy-Aufrufe.

Verhalten:
- Schreibt nur, wenn `IMAGE_TYPE_EXTENSION` auf `webp` steht.
- Schreibt nicht doppelt, wenn WebP bereits während `create()` erzeugt wurde.

Praxis:
- In aktuellen Integrationen ist ein zusätzlicher Aufruf oft nicht nötig, weil `create()` WebP bereits selbst erzeugen kann (wenn konfiguriert).

### `merge($merge_file, $x_pos = 0, $y_pos = 0, $opacity = 100, $scale_or_trans_colour = 'FF0000')`

Legt ein Overlay in die Merge-Queue.

Parameter:
- `$merge_file`: Overlay-Datei
- `$x_pos`, `$y_pos`: Position
- `$opacity`: 0..100
- `$scale_or_trans_colour`:
  - Zahl: Skalierung in Prozent
  - String: transparente Farbe (Hex, z. B. `"FF00FF"`)

### Effektmethoden (bauen nur die Queue auf)

Diese Methoden schreiben noch kein Bild, sondern merken Effekte für `create()` vor:
- `bevel(...)`
- `greyscale(...)`
- `ellipse(...)`
- `round_edges(...)`
- `frame(...)`
- `drop_shadow(...)`
- `motion_blur(...)`

## Transform-String: Syntax und Regeln

`$transform` wird von `manipulate()` geparst.

Beispiele:
- `"round_edges(6),drop_shadow(3)"`
- `"greyscale()"`
- `"frame('FFFFFF','000000',4,'808080')"`

Regeln:
- Mehrere Aufrufe mit Komma trennen.
- Nur erlaubte Methodennamen werden ausgeführt.
- Argumente dürfen Zahlen, bool, null oder Strings sein.

## Beispiele

### Beispiel 1: Standard-Resize mit Transform

```php
$img = new image_manipulation(
  DIR_FS_CATALOG_ORIGINAL_IMAGES . $products_image_name,
  PRODUCT_IMAGE_INFO_WIDTH,
  PRODUCT_IMAGE_INFO_HEIGHT,
  DIR_FS_CATALOG_INFO_IMAGES . $products_image_name,
  IMAGE_QUALITY,
  "round_edges(6),drop_shadow(3)"
);

$img->create();

// Optional für Legacy-Flow
$img->createWebp();
```

Hinweis:
- Ohne Legacy-Konstante muss `create()` explizit aufgerufen werden.

### Beispiel 2: Merge-Overlay (Wasserzeichen)

```php
$img = new image_manipulation(
  DIR_FS_CATALOG_ORIGINAL_IMAGES . $products_image_name,
  PRODUCT_IMAGE_POPUP_WIDTH,
  PRODUCT_IMAGE_POPUP_HEIGHT,
  '',
  IMAGE_QUALITY,
  ''
);

$img->merge(
  DIR_FS_CATALOG_IMAGES . 'watermark.png',
  20,
  20,
  70,
  100
);

$img->destination_file = DIR_FS_CATALOG_POPUP_IMAGES . $products_image_name;
$img->create();

if (defined('IMAGE_TYPE_EXTENSION') && IMAGE_TYPE_EXTENSION === 'webp') {
  $img->createWebp();
}
```

### Beispiel 3: Nur Graustufen ohne Transform-String

```php
$img = new image_manipulation(
  DIR_FS_CATALOG_ORIGINAL_IMAGES . $products_image_name,
  800,
  800,
  '',
  82,
  ''
);

$img->greyscale(85, 85, 85);
$img->destination_file = DIR_FS_CATALOG_IMAGES . 'product_images/info_images/' . $products_image_name;
$img->create();
```

Hinweis:
- `greyscale()` ohne Parameter entspricht effektiv einer Gewichtung 0,0,0 und erzeugt daher keine sinnvolle Graustufen-Umwandlung.

## Typische Stolperfallen

1. Kein Output trotz Konstruktor
- Ursache: Es gibt standardmäßig kein Auto-`create()` mehr im Konstruktor.
- Lösung: Nach der Konfiguration immer explizit `create()` aufrufen.

2. Kein Output trotz Aufruf
- Ursache: Imagick nicht verfügbar oder Quelldatei/Zielpfad ungültig.
- Prüfung: Existiert Quelle, ist Zielverzeichnis schreibbar, ist `Imagick` installiert?

3. WebP fehlt
- Ursache: `IMAGE_TYPE_EXTENSION` ist nicht `webp` oder Encoder fehlt in Imagick.
- Prüfung: `queryFormats('WEBP')` muss Format liefern.

4. Farbabweichungen bei Druckdaten
- Ursache: CMYK ohne passendes Profil.
- Lösung: ICC-Profile im Verzeichnis `src/admin/includes/classes/ICC/` prüfen.

## Empfehlung für neue Integrationen

Nutze den kontrollierten Ablauf (Muster B):
- Objekt mit leerem Zielpfad erzeugen
- alle gewünschten Effekte/Merge definieren
- genau einmal `create()` aufrufen
- optional `createWebp()` nur für Legacy-Kompatibilität
