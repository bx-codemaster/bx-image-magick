# BX Image Magick

## Überblick

Dieses Modul stellt die Klasse `image_manipulation` bereit und ersetzt die klassische GD-basierte Bildverarbeitung durch Imagick.

Ausführliches Methoden-Handbuch mit Aufrufreihenfolge und Beispielen:
- `docs/HANDBUCH-image_manipulation.md`

Datei der Klasse:
- `src/admin/includes/classes/bx_image_magick.php`

Ziele der Klasse:
- Größenanpassung von Produktbildern
- Farbkonvertierung (CMYK nach RGB mit ICC-Profilen)
- Effekt- und Merge-Operationen
- Ausgabe in klassischen Formaten (JPEG, PNG, GIF)
- Moderne Formate (WebP, optional AVIF) im selben Verarbeitungsdurchlauf

## Voraussetzungen

- PHP mit Imagick-Erweiterung
- Vorhandene Schreibrechte auf das Zielverzeichnis
- ICC-Profile im Verzeichnis `src/admin/includes/classes/ICC/`

## Kernfunktionen

### 1. Bild laden, skalieren, speichern

Die Methode `create()` übernimmt den Hauptprozess:
- Datei laden
- Orientierung bei JPEG korrigieren (EXIF)
- Zielgröße berechnen
- Bild skalieren
- Farbkonvertierung bei CMYK-Quellen
- optionale Effekte und Overlays anwenden
- Hauptbild schreiben
- moderne Ausgabeformate direkt aus dem Bild im Speicher erzeugen

### 2. Moderne Formate ohne zusätzlichen Datei-Read

WebP/AVIF werden aus dem bereits verarbeiteten Imagick-Objekt geschrieben.
Dadurch entfällt ein zusätzlicher Festplatten-Read im Normalfall.

Konfiguration über `IMAGE_TYPE_EXTENSION`:
- `webp`: WebP wird berücksichtigt
- `avif`: AVIF wird berücksichtigt

Die Methode `createWebp()` bleibt als kompatibler Wrapper erhalten, um ältere Shop-Aufrufe nicht zu brechen.
Ein internes Flag verhindert dabei doppeltes WebP-Schreiben.

### 3. ICC-Profil-Handling

Für CMYK-Quellen nutzt die Klasse konfigurierte oder fallback-basierte Profile:
- CMYK: `IMAGEMANIPULATOR_ICC_PROFILE_CMYK`
- RGB:  `IMAGEMANIPULATOR_ICC_PROFILE_RGB`

Damit werden Farbabweichungen bei Druckdaten deutlich reduziert.

### 4. Fehlerbehandlung und Logging

Dateioperationen arbeiten ohne stilles Unterdrücken von Fehlern.
Fehler werden über `logImagickException()` protokolliert (in `DIR_FS_LOG/imagick_error.log`, falls verfügbar).

## Unterstützte Manipulationen

Die Transform-Queue unterstützt folgende Operationen:
- `bevel`
- `greyscale`
- `ellipse`
- `round_edges`
- `merge`
- `frame`
- `drop_shadow`
- `motion_blur`

## Typischer Ablauf im Shop

1. Klasse wird instanziiert
2. `create()` verarbeitet und schreibt das Hauptbild
3. Je nach Konfiguration werden WebP/AVIF direkt mitgeschrieben
4. Ein externer Aufruf von `createWebp()` bleibt kompatibel möglich, ohne Doppel-Write

## Kurzes Beispiel

```php
$image = new image_manipulation(
  DIR_FS_CATALOG . 'images/product.jpg',
  600,
  600,
  DIR_FS_CATALOG . 'images/product_resized.jpg',
  85,
  "round_edges(6),drop_shadow(3)"
);

// Optional fuer Legacy-Aufrufe
$image->createWebp();
```

## Hinweise zur Wartung

- Bei neuen Ausgabeformaten immer Erkennung und Schreibzweig gemeinsam erweitern.
- ICC-Dateinamen nur als Dateiname konfigurieren (kein Pfad), da intern abgesichert auf das ICC-Verzeichnis aufgelöst wird.
- Bei Kundenproblemen zuerst `imagick_error.log` prüfen.
