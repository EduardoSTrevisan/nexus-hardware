<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$produto = $id ? LojaStore::produto((int)$id) : null;
if (!$produto) { http_response_code(404); header('Content-Type: text/html; charset=utf-8'); echo '<h1>Produto não encontrado</h1><a href="index.html">Voltar à loja</a>'; exit; }
$p = $produto;
$url = base_url();
$nome = h($p['nome']);
$imagem = h($p['imagem']);
$estoque = (int)$p['estoque'];
$preco = number_format((float)$p['preco'], 2, ',', '.');
$descr = h($p['descricao']);
$link = $url . '/p.php?id=' . $p['id'];
$real = envv('STORE_REAL') === '1';
$indexar = $real && envv('PUBLIC_INDEXING') === '1';
$schema = [
    '@context'=>'https://schema.org','@type'=>'Product',
    'name'=>$p['nome'], 'description'=>$p['descricao'],
    'sku'=>'NEXUS-' . $p['id'], 'image'=>[$url . '/' . $p['imagem']], 'url'=>$link
];
// Somente descrever como oferta real quando a loja deixar de ser acadêmica.
if ($real) $schema['offers'] = ['@type'=>'Offer', 'priceCurrency'=>'BRL', 'price'=>$p['preco'], 'availability'=>'https://schema.org/' . ($estoque ? 'InStock' : 'OutOfStock'), 'url'=>$link, 'itemCondition'=>'https://schema.org/NewCondition'];
$meta = '<meta name="description" content="' . $descr . '">';
$meta .= '<link rel="canonical" href="' . h($link) . '">';
$meta .= '<meta name="robots" content="' . ($indexar ? 'index,follow' : 'noindex,nofollow') . '">';
$meta .= '<meta property="og:type" content="product"><meta property="og:title" content="' . $nome . '">';
$meta .= '<meta property="og:description" content="' . $descr . '"><meta property="og:url" content="' . h($link) . '">';
$meta .= '<script type="application/ld+json">' . json_safe($schema) . '</script>';
$html = file_get_contents(__DIR__ . '/produto.html');
$html = str_replace('<title>Produto | NEXUS Hardware</title>', '<title>' . $nome . ' | NEXUS Hardware</title>' . $meta, $html);
$detalhes = '<div class="detail-art"><img src="' . $imagem . '" alt="Ilustração de ' . $nome . '"></div>' .
 '<div class="detail-info"><div class="detail-category">' . h($p['categoria']) . ' › ' . h($p['tipo']) . '</div>' .
 '<h1>' . $nome . '</h1><p class="detail-description">' . $descr . '</p>' .
 '<p class="stock detail-stock"><span class="stock-dot"></span>' . ($estoque ? "$estoque unidades disponíveis" : 'Esgotado') . '</p>' .
 '<div class="detail-price">R$ ' . $preco . '<small>Valor fictício — projeto acadêmico</small></div>' .
 '<div class="detail-controls"><div class="qty-control"><button id="menos">−</button><span id="qtd">1</span><button id="mais">+</button></div>' .
 '<button class="btn btn-primary" id="adicionar" ' . ($estoque ? '' : 'disabled') . '>Adicionar ao carrinho</button></div>' .
 '<button class="btn btn-outline btn-full" id="comprar" ' . ($estoque ? '' : 'disabled') . '>Comprar agora</button></div>';
$html = str_replace('<p class="loading">Carregando produto...</p>', $detalhes, $html);
$html = str_replace('<ul id="especificacoes" class="spec-list"></ul>', '<ul id="especificacoes" class="spec-list">' . implode('', array_map(fn($s) => '<li>' . h($s) . '</li>', $p['especificacoes'])) . '</ul>', $html);
$html = str_replace('<span id="nome-breadcrumb">Detalhes</span>', '<span id="nome-breadcrumb">' . $nome . '</span>', $html);
header('Content-Type: text/html; charset=utf-8');
echo $html;
