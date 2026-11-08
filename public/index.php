<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';
$produtos = LojaStore::produtos();
function cartao(array $p): string {
    $link = 'p.php?id=' . $p['id'];
    $nome = h($p['nome']);
    $estoque = (int)$p['estoque'];
    $preco = number_format((float)$p['preco'], 2, ',', '.');
    $imagem = h($p['imagem']);
    return '<article class="product-card"><a class="product-art" href="' . $link . '"><img src="' . $imagem . '" alt="Ilustração de ' . $nome . '" loading="lazy"></a>' .
     '<div class="product-content"><span class="eyebrow">' . h($p['tipo']) . '</span><a class="product-title" href="' . $link . '">' . $nome . '</a>' .
     '<p class="stock ' . ($estoque ? '' : 'out') . '"><span class="stock-dot"></span>' . ($estoque ? "$estoque em estoque" : 'Esgotado') . '</p>' .
     '<div class="product-bottom"><strong class="product-price">R$ ' . $preco . '</strong><button class="icon-add" data-adicionar="' . $p['id'] . '" aria-label="Adicionar ao carrinho" ' . ($estoque ? '' : 'disabled') . '>+</button></div></div></article>';
}
$html = file_get_contents(__DIR__ . '/index.html');
$cards = implode('', array_map('cartao', $produtos));
$destaques = implode('', array_map('cartao', array_slice(array_values(array_filter($produtos, fn($p) => $p['destaque'] && $p['estoque'] > 0)),0,4)));
$html = str_replace('<p class="loading">Carregando produtos...</p>', $cards, $html);
$html = str_replace('<p class="loading">Carregando destaques...</p>', $destaques, $html);
$url = base_url();
$dados = ['@context'=>'https://schema.org','@type'=>'WebSite','name'=>'NEXUS Hardware — Projeto acadêmico','url'=>$url . '/index.html','description'=>'Loja fictícia de demonstração de hardware, sem vendas reais.'];
$meta = '<link rel="canonical" href="' . h($url) . '/index.html">';
$meta .= '<meta name="robots" content="' . (envv('PUBLIC_INDEXING') === '1' && envv('STORE_REAL') === '1' ? 'index,follow' : 'noindex,nofollow') . '">';
$meta .= '<script type="application/ld+json">' . json_safe($dados) . '</script>';
$html = str_replace('</head>', $meta . '</head>', $html);
header('Content-Type: text/html; charset=utf-8');
echo $html;
