<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';
try {
    $dsn = envv('DB_DSN');
    if (!str_starts_with($dsn, 'pgsql:')) throw new RuntimeException('Configure DB_DSN=pgsql:... no .env.');
    $db = new PDO($dsn, envv('DB_USER'), envv('DB_PASSWORD'), [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $versao = $db->query('SHOW server_version')->fetchColumn();
    $tabelas = $db->query("SELECT to_regclass('public.produtos') AS produtos, to_regclass('public.pedidos') AS pedidos, to_regclass('public.pedido_itens') AS itens")->fetch(PDO::FETCH_ASSOC);
    echo 'PostgreSQL conectado! Versão: ' . $versao . PHP_EOL;
    foreach ($tabelas as $k => $valor) echo $k . ': ' . ($valor ?? 'AINDA NÃO CRIADA') . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'Falha na conexão: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
