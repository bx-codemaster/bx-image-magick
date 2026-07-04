# Neuen Effekt hinzufügen – Schritt-für-Schritt-Anleitung

Die Klasse `image_manipulation` in `src/admin/includes/classes/bx_image_magick.php` nutzt ein **dreistufiges System**: Whitelist → Registrierung → Ausführung. Jeder neue Effekt muss in allen drei Stufen eingetragen werden.

---

## Schritt 1: Name in `ALLOWED_MANIPULATIONS` eintragen

```php
protected const ALLOWED_MANIPULATIONS = array(
    'bevel',
    'greyscale',
    // ...
    'motion_blur',
    'my_new_effect',   // ← hier ergänzen
);
```

Diese Konstante dient als **Security-Whitelist** in `applyManipulationExpression()`. Ohne Eintrag hier wird der Effekt im Transform-String stillschweigend ignoriert.

---

## Schritt 2: Öffentliche Registrierungsmethode erstellen

```php
public function my_new_effect(int $strength = 5, string $color = 'FF0000') {
    $this->effects_queue[] = array(
        'name' => 'my_new_effect',
        'args' => array((int)$strength, (string)$color),
    );
}
```

**Regeln:**
- Methodenname muss **exakt** dem Namen in `ALLOWED_MANIPULATIONS` entsprechen
- Nur in die Queue schreiben, **nicht direkt Imagick aufrufen** – das passiert in Schritt 3
- Typen sofort casten: `(int)`, `(string)` etc.

---

## Schritt 3: Ausführungsblock in `applyEffectOperations()` ergänzen

```php
if ($name === 'my_new_effect') {
    $strength = isset($args[0]) ? max(1, (int)$args[0]) : 5;
    $color    = $this->normalizeHexColor(isset($args[1]) ? (string)$args[1] : '', '#FF0000');
    // Imagick-Logik hier:
    $img->blurImage($strength, $strength / 2);
    continue;
}
```

**Regeln:**
- Immer `continue` am Ende des Blocks – verhindert Fall-through
- Argumente aus `$args` defensiv mit `isset()` lesen
- Das `try/catch` im übergeordneten `foreach` fängt Imagick-Ausnahmen automatisch ab

---

## Sonderfälle

| Situation | Was tun |
|---|---|
| Effekt schneidet Bereiche ab (Schatten, Rand) | `$img` durch eine neue, größere Imagick-Instanz ersetzen (siehe `applyDropShadowEffect()`) |
| Effekt erfordert Transparenz (Maske, Alpha) | `prepareImageForAlphaPipeline()` wurde bereits upstream aufgerufen – Alpha ist immer aktiv |
| Effekt setzt JPEG-Hintergrundfarbe | `$this->registerJpegBackgroundColor($color)` aufrufen statt direkt in `$this->jpegFlattenBackgroundColor` schreiben |
| Hilfsobjekte (Imagick-Klone, Masken) | Immer im `finally`-Block mit `->clear()->destroy()` aufräumen |

---

## Was automatisch funktioniert (ohne weiteres Zutun)

- **Transform-String-Parsing**: `manipulate()` → `applyManipulationExpression()` ruft die Methode aus Schritt 2 via `call_user_func_array()` auf.  
  Der Effekt ist damit direkt über den Transform-String nutzbar, z. B.:  
  ```
  my_new_effect(5,'FF0000')
  ```
- **Argument-Parsing** übernimmt `parseScalar()` – unterstützt `int`, `float`, `string`, `bool`, `null`.
- **Kombinierbarkeit**: Mehrere Effekte kommagetrennt im Transform-String werden automatisch sequenziell abgearbeitet.

---

## Vollständiges Minimalbeispiel: Sepia-Effekt

### Schritt 1 – `ALLOWED_MANIPULATIONS`
```php
protected const ALLOWED_MANIPULATIONS = array(
    // ... bestehende Einträge ...
    'sepia',
);
```

### Schritt 2 – Registrierungsmethode
```php
public function sepia(int $threshold = 80) {
    $this->effects_queue[] = array(
        'name' => 'sepia',
        'args' => array(max(0, min(100, (int)$threshold))),
    );
}
```

### Schritt 3 – Ausführungsblock in `applyEffectOperations()`
```php
if ($name === 'sepia') {
    $threshold = isset($args[0]) ? max(0, min(100, (int)$args[0])) : 80;
    $img->sepiaToneImage($threshold);
    continue;
}
```

### Verwendung im Transform-String
```
sepia(80)
```

---

## Überblick über relevante Methoden

| Methode | Aufgabe |
|---|---|
| `manipulate()` | Parst den Transform-String und ruft `applyManipulationExpression()` auf |
| `applyManipulationExpression()` | Prüft Whitelist, parsed Argumente, ruft die Registrierungsmethode auf |
| `splitTopLevel()` | Trennt kommagetrennte Ausdrücke unter Beachtung von Klammerschachtelung |
| `parseScalar()` | Wandelt Argument-Strings in PHP-Skalare um |
| `applyEffectOperations()` | Führt alle Einträge der `$effects_queue` auf dem Imagick-Objekt aus |
| `registerJpegBackgroundColor()` | Setzt die Hintergrundfarbe für späteres JPEG-Flattening |
| `normalizeHexColor()` | Normalisiert Farbwerte auf `#RRGGBB` |
| `logImagickException()` | Protokolliert Imagick-Fehler ins Log-Verzeichnis |
