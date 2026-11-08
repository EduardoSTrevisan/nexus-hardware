<?php
require_once __DIR__ . '/../src/bootstrap.php';
if (envv('DB_DSN') === '') { fwrite(STDERR,"Configure DB_DSN no .env antes de popular o MySQL.\n"); exit(1); }
$db = new PDO(envv('DB_DSN'), envv('DB_USER'), envv('DB_PASSWORD'), [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$produtos = json_decode(file_get_contents(__DIR__ . '/../seed/produtos.json'), true,512,JSON_THROW_ON_ERROR);
$stmt = $db->prepare('INSERT INTO produtos (id,nome,categoria,tipo,preco_centavos,estoque,imagem,destaque,selo,descricao,especificacoes) VALUES (?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE nome=VALUES(nome),categoria=VALUES(categoria),tipo=VALUES(tipo),preco_centavos=VALUES(preco_centavos),imagem=VALUES(imagem),destaque=VALUES(destaque),selo=VALUES(selo),descricao=VALUES(descricao),especificacoes=VALUES(especificacoes)');
foreach($produtos as $p) $stmt->execute([$p['id'],$p['nome'],$p['categoria'],$p['tipo'],(int)round($p['preco']*100),$p['estoque'],$p['imagem'],$p['destaque']?1:0,$p['selo'],$p['descricao'],json_safe($p['especificacoes'])]);
echo count($produtos) . " produtos importados. O estoque de produtos existentes foi preservado.\n";
