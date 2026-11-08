<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';
if (!str_starts_with(envv('DB_DSN'), 'pgsql:') || !str_contains(envv('DB_DSN'), 'dbname=nexus_hardware')) {
    fwrite(STDERR, "Operacao permitida apenas no banco nexus_hardware.\n"); exit(1);
}
echo "Este comando RESTAURA as quantidades iniciais dos 10 produtos ficticios.\n";
echo "Os pedidos anteriores permanecem registrados.\n";
echo "Digite RESTAURAR para confirmar: ";
$confirma = trim((string)fgets(STDIN));
if ($confirma !== 'RESTAURAR') { echo "Cancelado.\n"; exit(0); }
try { 
    $db = new PDO(envv('DB_DSN'), envv('DB_USER'), envv('DB_PASSWORD'), [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    if ($db->query('SELECT current_database()')->fetchColumn() !== 'nexus_hardware') {
        throw new RuntimeException('Banco errado.');
    }
    echo LojaStore::reset() . " produtos tiveram o estoque restaurado.\n"; }
catch (Throwable $e) { fwrite(STDERR, "Falha ao restaurar estoque. Verifique .env e banco.\n"); exit(1); }
