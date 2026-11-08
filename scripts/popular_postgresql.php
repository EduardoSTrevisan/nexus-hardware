<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';
if (!str_starts_with(envv('DB_DSN'), 'pgsql:')) {
    fwrite(STDERR, "Configure DB_DSN=pgsql:... no .env antes de importar.\n");
    exit(1);
}
try {
    $db = new PDO(envv('DB_DSN'), envv('DB_USER'), envv('DB_PASSWORD'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    if ($db->query('SELECT current_database()')->fetchColumn() !== 'nexus_hardware') {
        throw new RuntimeException('Este script so pode modificar o banco nexus_hardware.');
    }
    $produtos = json_decode((string)file_get_contents(__DIR__ . '/../seed/produtos.json'), true, 512, JSON_THROW_ON_ERROR);
    // Atualiza nome/preço/descrição sem redefinir estoque de produtos já existentes.
    $stmt = $db->prepare('INSERT INTO produtos
      (id,nome,categoria,tipo,preco_centavos,estoque,imagem,destaque,selo,descricao,especificacoes)
      VALUES (?,?,?,?,?,?,?,?,?,?,CAST(? AS jsonb))
      ON CONFLICT (id) DO UPDATE SET
        nome = EXCLUDED.nome, categoria = EXCLUDED.categoria, tipo = EXCLUDED.tipo,
        preco_centavos = EXCLUDED.preco_centavos, imagem = EXCLUDED.imagem,
        destaque = EXCLUDED.destaque, selo = EXCLUDED.selo,
        descricao = EXCLUDED.descricao, especificacoes = EXCLUDED.especificacoes');
    $db->beginTransaction();
    foreach ($produtos as $p) {
        $stmt->execute([
            $p['id'], $p['nome'], $p['categoria'], $p['tipo'],
            (int)round($p['preco'] * 100), (int)$p['estoque'], $p['imagem'],
            $p['destaque'] ? 'true' : 'false', $p['selo'], $p['descricao'],
            json_safe($p['especificacoes'])
        ]);
    }
    $db->commit();
    echo count($produtos) . " produtos cadastrados no PostgreSQL.\n";
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    fwrite(STDERR, "Erro ao importar: " . $e->getMessage() . "\n");
    exit(1);
}
