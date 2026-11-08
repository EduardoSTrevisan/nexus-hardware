<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';
if (!str_starts_with(envv('DB_DSN'), 'pgsql:') || !str_contains(envv('DB_DSN'), 'dbname=nexus_hardware')) {
    fwrite(STDERR, "Configure o banco nexus_hardware no arquivo .env.\n"); exit(1);
}
try {
    $db = new PDO(envv('DB_DSN'), envv('DB_USER'), envv('DB_PASSWORD'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    if ($db->query('SELECT current_database()')->fetchColumn() !== 'nexus_hardware') {
        throw new RuntimeException('Banco incorreto.');
    }
    $sql = 'SELECT p.id, p.cliente_nome, p.total_centavos, p.criado_em, '
         . 'COALESCE(SUM(i.quantidade), 0) AS itens '
         . 'FROM pedidos p LEFT JOIN pedido_itens i ON i.pedido_id = p.id '
         . 'GROUP BY p.id ORDER BY p.id DESC LIMIT 15';
    $pedidos = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    echo "=== ULTIMOS PEDIDOS FICTICIOS SALVOS NO POSTGRESQL ===" . PHP_EOL;
    if (!$pedidos) { echo "Nenhum pedido ainda. Faca uma compra simulada na loja.\n"; exit(0); }
    foreach ($pedidos as $p) {
        echo sprintf('NX-%06d | %-25s | %d itens | R$ %s | %s',
           (int)$p['id'], substr((string)$p['cliente_nome'], 0, 25), (int)$p['itens'],
           number_format((int)$p['total_centavos'] / 100, 2, ',', '.'), $p['criado_em']) . PHP_EOL;
    }
} catch (Throwable $e) { fwrite(STDERR, "Nao foi possivel consultar pedidos. Verifique .env e tabelas.\n"); exit(1); }
