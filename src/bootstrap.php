<?php
declare(strict_types=1);

// Carrega .env local (sem biblioteca externa). Variáveis do sistema têm preferência.
$arquivoEnv = dirname(__DIR__) . '/.env';
if (is_file($arquivoEnv)) {
    foreach (file($arquivoEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $linha) {
        $linha = trim($linha);
        if ($linha === '' || str_starts_with($linha, '#') || !str_contains($linha, '=')) continue;
        [$chave, $valor] = explode('=', $linha, 2);
        $chave = trim($chave);
        if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $chave)) continue;
        if (getenv($chave) === false) putenv($chave . '=' . trim(trim($valor), '"\''));
    }
}
require_once __DIR__ . '/store.php';
function envv(string $key, string $default = ''): string {
    $valor = getenv($key);
    return $valor === false ? $default : $valor;
}
function base_url(): string {
    $manual = rtrim(envv('SITE_URL'), '/');
    if ($manual !== '') return $manual;
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    // Não confiar cegamente no header Host para URLs absolutas.
    if (!preg_match('/^(localhost|127\.0\.0\.1)(:[0-9]{2,5})?$/', $host)) return 'http://localhost:8000';
    return 'http://' . $host;
}
function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function json_safe(mixed $value): string {
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
}
