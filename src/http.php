<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
$permitidos = array_filter(array_map('trim', explode(',', envv('ALLOWED_ORIGINS'))));
$origem = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
if ($origem !== '' && in_array($origem, $permitidos, true)) {
    header('Access-Control-Allow-Origin: ' . $origem);
    header('Vary: Origin');
    header('Access-Control-Allow-Headers: Content-Type, X-Admin-Token');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
function responder(mixed $dados, int $status = 200): never {
    http_response_code($status); echo json_safe($dados); exit;
}
function exigirMetodo(string $metodo): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== $metodo) responder(['sucesso'=>false, 'erro'=>'Método não permitido.'],405);
}
function entrada(): array {
    if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 20000) responder(['sucesso'=>false,'erro'=>'Pedido muito grande.'],413);
    $raw = file_get_contents('php://input');
    $obj = json_decode($raw ?: '', true);
    if (!is_array($obj)) responder(['sucesso'=>false,'erro'=>'JSON inválido.'],400);
    return $obj;
}
function executar(callable $acao): never {
    try { responder($acao()); }
    catch (InvalidArgumentException $e) { responder(['sucesso'=>false,'erro'=>$e->getMessage()], 422); }
    catch (Throwable $e) { error_log($e); responder(['sucesso'=>false,'erro'=>'Erro interno no servidor. Verifique as configurações.'], 500); }
}
