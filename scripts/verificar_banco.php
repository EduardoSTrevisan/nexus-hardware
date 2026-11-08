<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';

/** Diagnóstico somente leitura, executado no TERMINAL. Nunca expõe a senha. */
function linha(string $chave, string $valor): void {
    echo str_pad($chave, 23) . ': ' . $valor . PHP_EOL;
}
function falhar(string $mensagem): never {
    fwrite(STDERR, "ERRO: {$mensagem}" . PHP_EOL);
    exit(1);
}

echo "=== NEXUS HARDWARE | VERIFICACAO DO BANCO ===" . PHP_EOL;
linha('PHP', PHP_VERSION);
linha('pdo_pgsql', extension_loaded('pdo_pgsql') ? 'HABILITADO' : 'NAO HABILITADO');
if (!extension_loaded('pdo_pgsql')) falhar('Habilite extension=pdo_pgsql no php.ini da linha de comando.');
$dsn = envv('DB_DSN');
if (!str_starts_with($dsn, 'pgsql:')) falhar('Execute INSTALAR_BANCO.bat para configurar o .env com PostgreSQL.');
if (!str_contains($dsn, 'dbname=nexus_hardware')) falhar('Por seguranca, este verificador exige dbname=nexus_hardware.');
try {
    $db = new PDO($dsn, envv('DB_USER'), envv('DB_PASSWORD'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    linha('PostgreSQL', (string)$db->query('SHOW server_version')->fetchColumn());
    $atual = (string)$db->query('SELECT current_database()')->fetchColumn();
    linha('Banco conectado', $atual);
    if ($atual !== 'nexus_hardware') falhar('Banco incorreto. Este verificador exige nexus_hardware.');
    $tabelas = ['produtos', 'pedidos', 'pedido_itens'];
    foreach ($tabelas as $nome) {
        $existe = $db->query("SELECT to_regclass('public." . $nome . "')")->fetchColumn();
        if (!$existe) falhar("Falta a tabela {$nome}; execute INSTALAR_BANCO.bat.");
        $total = (int)$db->query('SELECT COUNT(*) FROM ' . $nome)->fetchColumn();
        linha('Tabela ' . $nome, $total . ' registros');
    }
    $qtd = (int)$db->query('SELECT COUNT(*) FROM produtos')->fetchColumn();
    if ($qtd === 0) falhar('Tabela produtos vazia; execute INSTALAR_BANCO.bat.');
    $produtos = LojaStore::produtos();
    if (count($produtos) !== $qtd) falhar('API e banco retornaram quantidades diferentes.');
    $exemplo = $produtos[0];
    linha('Exemplo de produto', $exemplo['id'] . ' - ' . $exemplo['nome'] . ' (estoque: ' . $exemplo['estoque'] . ')');
    echo PHP_EOL . "SUCESSO: PHP e PostgreSQL estao conectados. A loja utiliza o banco de verdade." . PHP_EOL;
} catch (PDOException $e) {
    // Não imprime DSN/senha no terminal por segurança.
    falhar('Nao foi possivel conectar/consultar o PostgreSQL. Confira senha no .env, servidor PostgreSQL ligado, porta 5432 e tabelas. Detalhe: ' . $e->getCode());
} catch (Throwable $e) {
    falhar('Falha na verificacao: ' . $e->getMessage());
}
