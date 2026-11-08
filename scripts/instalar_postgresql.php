<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';

$dsn = envv('DB_DSN');
if (!str_starts_with($dsn, 'pgsql:')) {
    fwrite(STDERR, "ERRO: Configure DB_DSN=pgsql:... no arquivo .env\n");
    exit(1);
}
try {
    $db = new PDO($dsn, envv('DB_USER'), envv('DB_PASSWORD'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    if ($db->query('SELECT current_database()')->fetchColumn() !== 'nexus_hardware') {
        throw new RuntimeException('Este instalador so pode modificar o banco nexus_hardware.');
    }
    $schema = (string)file_get_contents(__DIR__ . '/../sql/postgresql.sql');
    $schema = preg_replace('/^\s*--[^\r\n]*$/m', '', $schema);
    foreach (explode(';', $schema) as $comando) {
        if (trim($comando) !== '') $db->exec(trim($comando));
    }
    echo "Tabelas PostgreSQL verificadas/criadas.\n";
    $produtos = json_decode((string)file_get_contents(__DIR__ . '/../seed/produtos.json'), true, 512, JSON_THROW_ON_ERROR);
    $sql = 'INSERT INTO produtos
      (id, nome, categoria, tipo, preco_centavos, estoque, imagem, destaque, selo, descricao, especificacoes)
      VALUES (?,?,?,?,?,?,?,?,?,?,CAST(? AS jsonb))
      ON CONFLICT (id) DO UPDATE SET
      nome=EXCLUDED.nome, categoria=EXCLUDED.categoria, tipo=EXCLUDED.tipo,
      preco_centavos=EXCLUDED.preco_centavos, imagem=EXCLUDED.imagem,
      destaque=EXCLUDED.destaque, selo=EXCLUDED.selo,
      descricao=EXCLUDED.descricao, especificacoes=EXCLUDED.especificacoes';
    $stmt = $db->prepare($sql);
    $db->beginTransaction();
    foreach ($produtos as $p) {
        $stmt->execute([$p['id'], $p['nome'], $p['categoria'], $p['tipo'],
            (int)round($p['preco'] * 100), (int)$p['estoque'], $p['imagem'],
            $p['destaque'] ? 'true' : 'false', $p['selo'], $p['descricao'], json_safe($p['especificacoes'])]);
    }
    $db->commit();
    echo count($produtos) . " produtos inseridos/atualizados. Estoques existentes preservados.\n";
    echo "OK! Inicie a loja e acesse http://localhost:8000/api/produtos.php\n";
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    fwrite(STDERR, "ERRO na instalacao: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
