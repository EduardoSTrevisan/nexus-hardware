#!/usr/bin/env python3
"""Gera fichas técnicas HTML; com --site-url também cria sitemap.xml e URLs canônicas.

python scripts/preparar_publicacao.py
python scripts/preparar_publicacao.py --site-url https://USUARIO.github.io/nexus-hardware/
python scripts/preparar_publicacao.py --site-url https://USUARIO.github.io/nexus-hardware/ --api-url https://NOME.onrender.com/api
"""
import argparse
import html
import json
import re
from pathlib import Path
from urllib.parse import urlparse
from xml.sax.saxutils import escape as xml_escape

ROOT = Path(__file__).resolve().parents[1]
DOCS = ROOT / 'docs'

def escapar(value):
    return html.escape(str(value), quote=True)

def gerar(site_url='', api_url=''):
    produtos = json.loads((ROOT / 'seed' / 'produtos.json').read_text(encoding='utf-8'))
    destino = DOCS / 'produtos'
    destino.mkdir(exist_ok=True)
    for p in produtos:
        pid = int(p['id'])
        titulo = escapar(p['nome'])
        descricao = escapar(p['descricao'])
        imagem = escapar('../' + p['imagem'])
        specs = ''.join('<li>' + escapar(s) + '</li>' for s in p['especificacoes'])
        canonical = ('<link rel="canonical" href="' + escapar(site_url + f'produtos/produto-{pid}.html') + '">') if site_url else ''
        meta_descr = escapar(f'{p["nome"]}: ficha técnica fictícia da loja acadêmica NEXUS Hardware. {p["descricao"]}')
        preco = f'{p["preco"]:,.2f}'.replace(',', 'X').replace('.', ',').replace('X', '.')
        template = f'''<!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{titulo} | Ficha técnica NEXUS Hardware</title>
<meta name="description" content="{meta_descr}">
{canonical}
<link rel="stylesheet" href="../css/style.css"><link rel="icon" href="../assets/favicon.svg" type="image/svg+xml">
</head><body>
<div class="announcement">PROJETO ACADÊMICO • CATÁLOGO SIMULADO • SEM VENDAS REAIS</div>
<header class="site-header"><div class="container header-inner"><a class="brand" href="../index.html">NEXUS HARDWARE</a><nav class="main-nav"><a href="../index.html">Início</a><a href="../index.html#produtos">Produtos</a></nav></div></header>
<main class="interior-main"><div class="container">
<p><a href="../index.html">Início</a> / <a href="../index.html#fichas-tecnicas">Fichas técnicas</a> / {titulo}</p>
<section class="detail-layout"><div class="detail-art"><img src="{imagem}" alt="Ilustração: {titulo}" width="480" height="340"></div>
<div class="detail-info"><p class="eyebrow">{escapar(p['categoria'])} · {escapar(p['tipo'])}</p><h1>{titulo}</h1>
<p class="detail-description">{descricao}</p><p class="detail-price">R$ {preco} <small>Preço fictício para demonstração</small></p>
<p>Estoque inicial ilustrativo: {int(p['estoque'])} unidades. Consulte a API para valores atuais.</p>
<a class="btn btn-primary" href="../produto.html?id={pid}">Abrir demonstração interativa</a></div></section>
<section class="spec-section"><h2>Especificações técnicas de exemplo</h2><ul class="spec-list">{specs}</ul></section>
<section class="spec-section"><h2>Sobre esta loja</h2><p>A NEXUS Hardware é um projeto acadêmico com HTML, CSS, JavaScript, API PHP e ferramentas MCP para estudo de encontrabilidade. Não realiza vendas, pagamentos ou entregas reais.</p><p><a href="../index.html#fichas-tecnicas">Ver outras fichas</a></p></section>
</div></main></body></html>'''
        (destino / f'produto-{pid}.html').write_text(template, encoding='utf-8')

    if site_url:
        urls = [site_url + 'index.html'] + [site_url + f'produtos/produto-{p["id"]}.html' for p in produtos]
        sitemap = '<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n' + ''.join('  <url><loc>' + xml_escape(u) + '</loc></url>\n' for u in urls) + '</urlset>\n'
        (DOCS / 'sitemap.xml').write_text(sitemap, encoding='utf-8')
        (DOCS / 'robots.txt').write_text('User-agent: *\nAllow: /\nDisallow: /carrinho.html\n\nSitemap: ' + site_url + 'sitemap.xml\n', encoding='utf-8')
        p = DOCS / 'index.html'
        raw = p.read_text(encoding='utf-8')
        raw = re.sub(r'<link rel="canonical"[^>]*>\s*', '', raw)
        raw = raw.replace('</head>', '<link rel="canonical" href="' + escapar(site_url) + '">\n</head>', 1)
        p.write_text(raw, encoding='utf-8')
    else:
        (DOCS / 'sitemap.xml').unlink(missing_ok=True)

    if api_url:
        config = '/* GitHub Pages usando API PHP do Render */\nconst CONFIG = {\n  modo: "php",\n  apiBase: ' + json.dumps(api_url,ensure_ascii=False) + ',\n  seoProductPages: false\n};\n'
    else:
        config = '/* GitHub Pages: demonstração funciona mesmo sem Render */\nconst CONFIG = {\n  modo: "demo",\n  apiBase: "",\n  seoProductPages: false\n};\n'
    (DOCS / 'js' / 'config.js').write_text(config, encoding='utf-8')
    print(f'OK: {len(produtos)} fichas HTML geradas; modo ' + ('API PHP' if api_url else 'demo'))
    print('Sitemap gerado para '+site_url if site_url else 'Sitemap: informe seu URL publico para gerar')

def validar_url(url, nome):
    if not url: return ''
    p = urlparse(url)
    if p.scheme != 'https' or not p.netloc or p.username or p.password or p.query or p.fragment:
        raise SystemExit('ERRO: ' + nome + ' exige HTTPS, host publico, sem query ou fragmento.')
    return url.rstrip('/') + ('/' if nome == '--site-url' else '')

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--site-url', default='', help='URL pública do GitHub Pages')
    parser.add_argument('--api-url', default='', help='URL da API PHP do Render terminada em /api')
    args = parser.parse_args()
    gerar(validar_url(args.site_url, '--site-url'), validar_url(args.api_url, '--api-url'))
