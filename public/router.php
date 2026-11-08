<?php
// Roteamento para o servidor embutido do PHP. Compatível com Windows/WampServer e Linux.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (!is_string($path)) { http_response_code(400); exit('Rota inválida'); }
$path = rawurldecode($path);
if (str_contains($path, '..') || str_contains($path, "\0")) {
    http_response_code(400);
    exit('Rota inválida');
}

if ($path === '/' || $path === '/index.html' || $path === '/index.php') {
    require __DIR__ . '/index.php';
    return true;
}
if ($path === '/robots.txt') { require __DIR__ . '/robots.php'; return true; }
if ($path === '/sitemap.xml') { require __DIR__ . '/sitemap.php'; return true; }

// realpath() no Windows usa barras invertidas (\); normalize antes de comparar.
// Arquivos CSS, JS, SVG etc. devem ser servidos diretamente pelo PHP.
$root = realpath(__DIR__);
$relative = ltrim(str_replace('\\', '/', $path), '/');
$file = realpath($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative));
$rootNormalizado = rtrim(str_replace('\\', '/', $root), '/');
$fileNormalizado = $file === false ? '' : str_replace('\\', '/', $file);
if ($file !== false && str_starts_with($fileNormalizado, $rootNormalizado . '/') && is_file($file)) {
    return false;
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo 'Página não encontrada.';
return true;
