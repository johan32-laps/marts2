<?php
/**
 * head.php - MARTS
 * Genera las etiquetas <head> con rutas correctas para cualquier profundidad
 * Uso: include con $pageTitle definido antes
 */

// Calcular la ruta base relativa al directorio raíz del proyecto
// Funciona sin importar desde dónde se incluya
$scriptPath  = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
$projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/../../'));
$depth       = substr_count(str_replace($projectRoot . '/', '', $scriptPath), '/');
$base        = str_repeat('../', $depth);

// Si $base está vacío (archivo en raíz), usar ./
if (empty($base)) $base = './';

$pageTitle = $pageTitle ?? 'MARTS';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($pageTitle); ?> | MARTS</title>
<link rel="icon" type="image/png" href="<?php echo $base; ?>public/img/icon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo $base; ?>public/css/style.css">
