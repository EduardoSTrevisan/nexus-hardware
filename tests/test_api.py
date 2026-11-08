"""Teste ponta a ponta sem dependências: python tests/test_api.py"""
import contextlib
import json
import os
from pathlib import Path
import subprocess
import time
import urllib.error
import urllib.request
import xml.etree.ElementTree as ET

ROOT = Path(__file__).resolve().parents[1]
PORT = 18125
BASE = f'http://127.0.0.1:{PORT}'


def request(path, data=None, headers=None, method=None):
    body = None if data is None else json.dumps(data).encode()
    h = {'Content-Type': 'application/json'} if body else {}
    h.update(headers or {})
    req = urllib.request.Request(BASE + path, data=body, headers=h, method=method)
    try:
        with urllib.request.urlopen(req, timeout=10) as r:
            raw = r.read().decode()
            return r.status, json.loads(raw) if r.headers.get('Content-Type','').startswith('application/json') else raw
    except urllib.error.HTTPError as e:
        raw = e.read().decode()
        return e.code, json.loads(raw) if e.headers.get('Content-Type','').startswith('application/json') else raw


def main():
    data = ROOT / 'storage/loja.json'
    if data.exists():
        data.unlink()
    env = {**os.environ, 'ADMIN_TOKEN': 'um-token-com-mais-de-16-caracteres', 'PUBLIC_INDEXING':'0', 'STORE_REAL':'0'}
    p = subprocess.Popen(['php','-S',f'127.0.0.1:{PORT}','-t','public','public/router.php'], cwd=ROOT, env=env, stdout=subprocess.DEVNULL, stderr=subprocess.PIPE)
    try:
        for _ in range(80):
            try:
                if request('/api/health.php')[0] == 200: break
            except urllib.error.URLError:
                time.sleep(.08)
        else: raise Exception('servidor não iniciou')
        status, produtos = request('/api/produtos.php')
        assert status == 200 and len(produtos) == 10
        status, cotacao = request('/api/frete.php?cep=01001-000&subtotal_centavos=10000')
        assert status == 200 and cotacao['valor'] == 24.9 and cotacao['simulado']
        status, cotacao = request('/api/frete.php?cep=123&subtotal_centavos=10000')
        assert status == 422
        assert produtos[0]['estoque'] == 8
        status, p1 = request('/api/produto.php?id=1')
        assert status == 200 and p1['nome'].startswith('Placa de Vídeo')
        status, _ = request('/api/produto.php?id=999')
        assert status == 404
        status, result = request('/api/finalizar_pedido.php', {'cliente':{'nome':'Aluno Exemplo','email':'aluno@example.com'},'itens':[{'produto_id':1,'quantidade':2},{'produto_id':4,'quantidade':1}]})
        assert status == 200 and result['sucesso'] and result['pedido'] == 'NX-000001', (status,result)
        assert result['total'] == 6229.7, result
        assert request('/api/produto.php?id=1')[1]['estoque'] == 6
        status, result = request('/api/finalizar_pedido.php', {'cliente':{'nome':'Aluno Exemplo','email':'aluno@example.com'}, 'itens':[{'produto_id':1,'quantidade':10}]})
        assert status == 422 and 'Estoque' in result['erro']
        assert request('/api/produto.php?id=1')[1]['estoque'] == 6
        status, _ = request('/api/finalizar_pedido.php', {'cliente':{'nome':'Teste','email':'foo@example.com'}, 'itens':[{'produto_id':1,'quantidade':1},{'produto_id':1,'quantidade':30}]})
        assert status == 422
        status, result = request('/api/restaurar_estoque.php', {}, method='POST')
        assert status == 403
        status, result = request('/api/restaurar_estoque.php', {}, headers={'X-Admin-Token':env['ADMIN_TOKEN']}, method='POST')
        assert status == 200 and result['produtos_restaurados'] == 10
        assert request('/api/produto.php?id=1')[1]['estoque'] == 8
        status, shipping_order = request('/api/finalizar_pedido.php', {'cliente':{'nome':'Teste CEP','email':'aluno@example.com','cep':'01001-000'}, 'itens':[{'produto_id':1,'quantidade':1}]})
        assert status == 200 and shipping_order['frete'] == 24.9 and shipping_order['total'] == 2924.8, shipping_order
        status, pagina = request('/index.html')
        assert status == 200 and 'Placa de Vídeo Phantom 12GB' in pagina
        assert '<meta name="robots" content="noindex,nofollow">' in pagina
        status, detalhe = request('/p.php?id=1')
        assert status == 200 and '<h1>Placa de Vídeo Phantom 12GB</h1>' in detalhe and 'application/ld+json' in detalhe
        status, robots = request('/robots.txt')
        assert status == 200 and 'Disallow: /' in robots
        status, mapa = request('/sitemap.xml')
        assert status == 200
        ET.fromstring(mapa)
        print('OK: 10 produtos, detalhes, pedido, frete, baixa de estoque, proteção de estoque, reset seguro, SEO server-side, robots, sitemap.')
    finally:
        p.terminate()
        try: p.communicate(timeout=3)
        except subprocess.TimeoutExpired: p.kill()
        # Deixar o projeto zerado para o usuário.
        if data.exists(): data.unlink()

if __name__ == '__main__': main()
