<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
  http_response_code(403);
  echo 'Forbidden: This script can only be run via command line (CLI).' . PHP_EOL;
  exit(1);
}

/**
 * Reproduzierbarer A/B-Vergleich zwischen GD- und Imagick-Implementierung
 * der Klasse image_manipulation.
 *
 * Nutzung:
 * php ModifiedModuleLoaderClient/Modules/bx-codemaster/bx-imagemagick/tools/ab_compare_image_manipulation.php \
 *   --input=images/product_images/original_images/example.jpg \
 *   --width=1200 --height=1200 \
 *   --quality=85 \
 *   --output=tmp/ab-compare
 */

$root = realpath(__DIR__ . '/../../../../../');
if ($root === false) {
  fwrite(STDERR, "Root-Verzeichnis konnte nicht bestimmt werden.\n");
  exit(1);
}

$options = getopt('', ['input:', 'width::', 'height::', 'quality::', 'output::']);
if (!isset($options['input'])) {
  fwrite(STDERR, "Fehlender Parameter --input\n");
  exit(1);
}

$inputArg = (string)$options['input'];
$inputFile = is_file($inputArg) ? realpath($inputArg) : realpath($root . DIRECTORY_SEPARATOR . ltrim(str_replace(['/', '\\\\'], DIRECTORY_SEPARATOR, $inputArg), DIRECTORY_SEPARATOR));
if ($inputFile === false || !is_file($inputFile)) {
  fwrite(STDERR, "Input-Datei nicht gefunden: {$inputArg}\n");
  exit(1);
}

$width = isset($options['width']) ? max(1, (int)$options['width']) : 1200;
$height = isset($options['height']) ? max(1, (int)$options['height']) : 1200;
$quality = isset($options['quality']) ? max(1, min(100, (int)$options['quality'])) : 85;
$outputDir = isset($options['output'])
  ? (string)$options['output']
  : $root . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'ab-compare-image-manipulation';

if (!preg_match('~^([A-Za-z]:\\\\|/)~', $outputDir)) {
  $outputDir = $root . DIRECTORY_SEPARATOR . ltrim(str_replace(['/', '\\\\'], DIRECTORY_SEPARATOR, $outputDir), DIRECTORY_SEPARATOR);
}

if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
  fwrite(STDERR, "Output-Verzeichnis konnte nicht erstellt werden: {$outputDir}\n");
  exit(1);
}

$gdOutput = $outputDir . DIRECTORY_SEPARATOR . 'result-gd.jpg';
$imagickOutput = $outputDir . DIRECTORY_SEPARATOR . 'result-imagick.jpg';
$diffOutput = $outputDir . DIRECTORY_SEPARATOR . 'diff.png';

@unlink($gdOutput);
@unlink($imagickOutput);
@unlink($diffOutput);

$gdScript = createRunnerScript(
  $root,
  $inputFile,
  $gdOutput,
  $width,
  $height,
  $quality,
  $root . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'image_manipulator.php'
);

$imagickScript = createRunnerScript(
  $root,
  $inputFile,
  $imagickOutput,
  $width,
  $height,
  $quality,
  $root . DIRECTORY_SEPARATOR . 'ModifiedModuleLoaderClient' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'bx-codemaster' . DIRECTORY_SEPARATOR . 'bx-imagemagick' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'image_manipulator_imagick.php'
);

$gdRun = runPhpScript($gdScript, $root);
$imagickRun = runPhpScript($imagickScript, $root);

@unlink($gdScript);
@unlink($imagickScript);

$report = [
  'input' => [
    'file' => $inputFile,
    'width' => $width,
    'height' => $height,
    'quality' => $quality,
  ],
  'gd' => collectImageResult($gdOutput, $gdRun),
  'imagick' => collectImageResult($imagickOutput, $imagickRun),
  'compare' => compareOutputs($gdOutput, $imagickOutput, $diffOutput),
  'output' => [
    'directory' => $outputDir,
    'gd_file' => $gdOutput,
    'imagick_file' => $imagickOutput,
    'diff_file' => is_file($diffOutput) ? $diffOutput : null,
  ],
];

$reportFile = $outputDir . DIRECTORY_SEPARATOR . 'report.json';
file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
echo "Report gespeichert: {$reportFile}" . PHP_EOL;

function createRunnerScript(
  string $root,
  string $inputFile,
  string $outputFile,
  int $width,
  int $height,
  int $quality,
  string $classFile
): string {
  $tmp = tempnam(sys_get_temp_dir(), 'abimg_');
  if ($tmp === false) {
    throw new RuntimeException('Temp-Datei konnte nicht erstellt werden.');
  }
  $script = $tmp . '.php';
  @unlink($tmp);

  $codeLines = [];
  $codeLines[] = '<?php';
  $codeLines[] = "define('_VALID_XTC', true);";
  $codeLines[] = 'define(\'DIR_FS_ADMIN\', ' . var_export($root . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR, true) . ');';
  $codeLines[] = 'define(\'DIR_FS_INC\', ' . var_export($root . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR, true) . ');';
  $codeLines[] = 'define(\'IMAGE_QUALITY\', ' . $quality . ');';
  $codeLines[] = "define('PRODUCT_IMAGE_NO_ENLARGE_UNDER_DEFAULT', 'false');";
  $codeLines[] = 'require_once ' . var_export($classFile, true) . ';';
  $codeLines[] = '$start = microtime(true);';
  $codeLines[] = 'try {';
  $codeLines[] = '  $manipulator = new image_manipulation(' . var_export($inputFile, true) . ', ' . $width . ', ' . $height . ', ' . var_export($outputFile, true) . ', ' . $quality . ', \'\');';
  $codeLines[] = '  $manipulator->create();';
  $codeLines[] = '  $ok = is_file(' . var_export($outputFile, true) . ');';
  $codeLines[] = '  $result = [\'ok\' => $ok, \'time_ms\' => (int)round((microtime(true) - $start) * 1000), \'error\' => null];';
  $codeLines[] = '} catch (Throwable $e) {';
  $codeLines[] = '  $result = [\'ok\' => false, \'time_ms\' => (int)round((microtime(true) - $start) * 1000), \'error\' => $e->getMessage()];';
  $codeLines[] = '}';
  $codeLines[] = 'echo json_encode($result, JSON_UNESCAPED_SLASHES);';
  $code = implode("\n", $codeLines) . "\n";

  file_put_contents($script, $code);
  return $script;
}

function runPhpScript(string $script, string $cwd): array {
  $php = PHP_BINARY;
  $cmd = escapeshellarg($php) . ' ' . escapeshellarg($script);

  $descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
  ];

  $process = proc_open($cmd, $descriptors, $pipes, $cwd);
  if (!is_resource($process)) {
    return ['ok' => false, 'time_ms' => 0, 'error' => 'proc_open fehlgeschlagen'];
  }

  fclose($pipes[0]);
  $stdout = stream_get_contents($pipes[1]);
  fclose($pipes[1]);
  $stderr = stream_get_contents($pipes[2]);
  fclose($pipes[2]);

  $exitCode = proc_close($process);
  $payload = extractJsonPayload((string)$stdout);
  $decoded = is_string($payload) ? json_decode($payload, true) : null;
  if (!is_array($decoded)) {
    return [
      'ok' => false,
      'time_ms' => 0,
      'error' => trim((string)$stderr) !== '' ? trim((string)$stderr) : 'Runner-Output nicht lesbar',
      'exit_code' => $exitCode,
    ];
  }

  $decoded['exit_code'] = $exitCode;
  if (trim((string)$stderr) !== '') {
    $decoded['stderr'] = trim((string)$stderr);
  }
  return $decoded;
}

function extractJsonPayload(string $output): ?string {
  $trimmed = trim($output);
  if ($trimmed === '') {
    return null;
  }

  if ($trimmed[0] === '{' && substr($trimmed, -1) === '}') {
    return $trimmed;
  }

  $start = strpos($trimmed, '{');
  $end = strrpos($trimmed, '}');
  if ($start === false || $end === false || $end <= $start) {
    return null;
  }

  return substr($trimmed, $start, $end - $start + 1);
}

function collectImageResult(string $file, array $run): array {
  $info = is_file($file) ? @getimagesize($file) : false;

  return [
    'run' => $run,
    'file_exists' => is_file($file),
    'bytes' => is_file($file) ? filesize($file) : null,
    'sha1' => is_file($file) ? sha1_file($file) : null,
    'dimensions' => is_array($info) ? ['width' => $info[0], 'height' => $info[1], 'type' => $info[2]] : null,
  ];
}

function compareOutputs(string $gdFile, string $imagickFile, string $diffFile): array {
  if (!is_file($gdFile) || !is_file($imagickFile)) {
    return [
      'available' => false,
      'reason' => 'Mindestens eine Ergebnisdatei fehlt.',
    ];
  }

  if (!class_exists('Imagick')) {
    return [
      'available' => false,
      'reason' => 'Imagick-Erweiterung nicht geladen, kein RMSE/SSIM-Vergleich moeglich.',
    ];
  }

  try {
    $a = new Imagick();
    $b = new Imagick();
    $a->readImage($gdFile);
    $b->readImage($imagickFile);

    if ($a->getImageWidth() !== $b->getImageWidth() || $a->getImageHeight() !== $b->getImageHeight()) {
      return [
        'available' => false,
        'reason' => 'Dimensionen unterschiedlich, RMSE nicht vergleichbar.',
      ];
    }

    $metricType = defined('Imagick::METRIC_ROOTMEANSQUAREDERROR')
      ? constant('Imagick::METRIC_ROOTMEANSQUAREDERROR')
      : 2;

    $compare = $a->compareImages($b, $metricType);
    $diff = $compare[0] ?? null;
    $rmse = isset($compare[1]) ? (float)$compare[1] : null;

    if ($diff instanceof Imagick) {
      $diff->setImageFormat('png');
      $diff->writeImage($diffFile);
      $diff->clear();
      $diff->destroy();
    }

    $a->clear();
    $a->destroy();
    $b->clear();
    $b->destroy();

    return [
      'available' => true,
      'metric' => 'RMSE',
      'value' => $rmse,
      'interpretation' => ($rmse !== null && $rmse < 0.02) ? 'sehr nah' : (($rmse !== null && $rmse < 0.08) ? 'sichtbar unterschiedlich' : 'deutlich unterschiedlich'),
      'diff_file' => is_file($diffFile) ? $diffFile : null,
    ];
  } catch (Throwable $e) {
    return [
      'available' => false,
      'reason' => 'Vergleich fehlgeschlagen: ' . $e->getMessage(),
    ];
  }
}
