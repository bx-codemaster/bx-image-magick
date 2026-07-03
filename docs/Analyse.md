

Die PHP-Klasse [image_manipulation] in [bx_image_magick.php] dient der Bildbearbeitung und -manipulation in modified eCommerce. Sie ist als moderner und leistungsfähiger Ersatz (Drop-in-Replacement) für die traditionelle, GD-basierte Bildverarbeitung konzipiert und nutzt die PHP-Bibliothek **Imagick** (ImageMagick).

Hier ist eine detaillierte Analyse der Funktionsweise und des Gebrauchs der Klasse:

---

### 1. Hauptmerkmale & Funktionalitäten

*   **Hochwertige Skalierung & Resizing:** Nutzt den Lanczos-Filter (`FILTER_LANCZOS`), um Bilder qualitativ hochwertiger und schärfer zu verkleinern als die GD-Bibliothek.
*   **Farbraum-Konvertierung (CMYK zu RGB):** Wandelt Produktbilder im CMYK-Farbraum (oft von Fotografen geliefert) über ICC-Farbprofile (`PSOcoated_v3.icc`, `sRGB2014.icc`) automatisch in den RGB-Farbraum um, um Farbverfälschungen im Browser zu verhindern.
*   **EXIF-Ausrichtungskorrektur:** Liest EXIF-Metadaten von JPEG-Dateien aus und dreht das Bild automatisch in die richtige Ausrichtung, falls dieses z. B. hochkant mit einem Smartphone aufgenommen wurde.
*   **Effekt-Pipeline (Transform-Befehle):** Unterstützt eine Kette von Transformationen, die entweder über einen Konfigurations-String (z. B. aus der Datenbank) geparst oder direkt über Methodenaufrufe registriert werden.
*   **Wasserzeichen & Overlays (Merge-System):** Ermöglicht das präzise Platzieren von Logos oder Wasserzeichen über dem Bild inklusive Skalierung, Transparenz (Opacity) und transparenten Hintergrundfarben.
*   **Unterstützung moderner Bildformate:** Erzeugt neben dem Standard-Bildtyp bei Bedarf automatisch moderne Ableitungen im **WebP-** und/oder **AVIF-**Format zur Optimierung der Ladezeiten.
*   **Browser-Ausgabe oder Speichern:** Kann das verarbeitete Bild entweder direkt als Datei auf dem Server speichern oder als HTTP-Stream an den Browser ausgeben.

---

### 2. Wichtige Methoden und Ablauf

#### A. Initialisierung: [__construct]
```php
function __construct($resource_file, $max_width, $max_height, $destination_file = '', $compression = null, $transform = '')
```
*   **Parameter:**
    *   `$resource_file`: Pfad zum Originalbild auf dem Server.
    *   `$max_width` / `$max_height`: Gewünschte Maximaldimensionen. Das Seitenverhältnis (Aspect Ratio) wird beibehalten.
    *   `$destination_file`: Pfad, unter dem das manipulierte Bild gespeichert werden soll.
    *   `$compression`: Kompressionsrate (1 bis 100). Standardwert ist 85 (oder die Konstante `IMAGE_QUALITY`).
    *   `$transform`: Optionaler String mit aneinandergereihten Manipulationen (z. B. `"greyscale(8,8,8),round_edges(10,#ffffff)"`). Wenn leer, wird anhand des Zielpfads versucht, den passenden Transform-String automatisch aufzulösen.

#### B. Pipeline-Start: [create]
Diese Methode führt die eigentliche Bildverarbeitung aus:
1. Prüft, ob Imagick installiert und die Quelldatei vorhanden ist.
2. Korrigiert die Orientierung ([correctImageOrientation].
3. Berechnet die Zielgröße und skaliert das Bild.
4. Führt ggf. die CMYK-zu-RGB-Konvertierung durch.
5. Initialisiert die Alpha-Pipeline (für Transparenzen).
6. Wendet die registrierten Effekte ([applyEffectOperations] an.
7. Speichert das Bild (oder gibt es an den Browser aus) und generiert moderne Formate (WebP, AVIF).

#### C. Wasserzeichen hinzufügen: [merge]
```php
public function merge($merge_file, $x_pos = 0, $y_pos = 0, $opacity = 100, $scale_or_trans_colour = 'FF0000')
```
*   Fügt das Overlay `$merge_file` der Merge-Queue hinzu.
*   Negative `$x_pos` oder `$y_pos` werden als Versatz von rechts bzw. unten interpretiert.

#### D. Manuelle Effekte registrieren:
*   [greyscale()]: Wandelt das Bild gewichtet in Graustufen um.
*   [round_edges()]: Rundet die Ecken ab.
*   [drop_shadow()]: Erzeugt einen weichen Schatten um das Bild.
*   [ellipse()]: Schneidet das Bild elliptisch/kreisförmig aus.
*   [frame()]: Fügt einen Rahmen hinzu.
*   [bevel()]: Hebt die Kanten plastisch hervor (3D-Effekt).
*   [motion_blur()]: Wendet eine Bewegungsunschärfe an.

---

### 3. Anwendungsbeispiele

#### Beispiel 1: Einfache Bildverkleinerung und Speichern
Verkleinert ein hochgeladenes Bild auf maximal 800x600 Pixel und speichert es ab. Wenn konfiguriert, werden automatisch auch `.webp`/`.avif`-Versionen erzeugt.

```php
$img = new image_manipulation(
    '/pfad/zu/original/bild.jpg', // Quelle
    800,                          // Max. Breite
    600,                          // Max. Höhe
    '/pfad/zu/ziel/bild.jpg'      // Ziel
);

// Führt die Operationen aus
$img->create();
```

#### Beispiel 2: Bild mit Effekten und Wasserzeichen (Manuell)
Hier runden wir die Ecken ab, fügen einen Schatten hinzu und legen ein Wasserzeichen-Logo unten rechts (mit 10px Abstand) ab.

```php
$img = new image_manipulation(
    '/pfad/zu/produkt.png',
    500,
    500,
    '/pfad/zu/ziel_produkt.png'
);

// Effekte in Queue eintragen
$img->round_edges(15, 'FFFFFF'); // Ecken-Radius 15px, weißer Hintergrund
$img->drop_shadow(10, '000000', 'FFFFFF', 65); // 10px schwarzer Schatten

// Wasserzeichen (Logo) unten rechts platzieren (-10, -10), mit 80% Deckkraft
$img->merge(
    '/pfad/zu/wasserzeichen.png',
    -10, // x-offset von rechts
    -10, // y-offset von unten
    80   // Opacity 80%
);

// Pipeline ausführen und Datei schreiben
$img->create();
```

#### Beispiel 3: Verwendung per Transform-String (Kompakt)
Dieses Muster wird häufig verwendet, um vordefinierte Einstellungen aus dem Admin-Bereich (siehe [admin/bx_image_magick.php] anzuwenden.

```php
$transformations = "greyscale(8,8,8),round_edges(10,#ffffff),drop_shadow(5,#000000,#ffffff,60)";

$img = new image_manipulation(
    '/pfad/zu/bild.jpg',
    400,
    400,
    '/pfad/zu/ziel.jpg',
    90, // Qualität
    $transformations
);

$img->create();
```

#### Beispiel 4: Direkte Ausgabe an den Browser
Nützlich für dynamisch generierte Bilder oder Thumbnails "on-the-fly".

```php
$img = new image_manipulation(
    '/pfad/zu/bild.jpg',
    200,
    200
);

// Header senden und direkt ausgeben
$img->setOutputToBrowser(true)
    ->create();
exit;
```