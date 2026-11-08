"""Consultas de leitura à API PHP; separadas do protocolo MCP para testes rápidos."""
from __future__ import annotations

import json
import os
from typing import Any
from urllib.error import HTTPError, URLError
from urllib.parse import urlencode
from urllib.request import Request, urlopen

API_URL = os.environ.get('NEXUS_API_URL', 'http://127.0.0.1:8000/api').rstrip('/')


def ler_api(arquivo: str, **parametros: Any) -> Any:
    url = API_URL + '/' + arquivo
    if parametros:
        url += '?' + urlencode(parametros)
    requisicao = Request(url, headers={'Accept': 'application/json', 'User-Agent': 'NEXUS-MCP/1.0'})
    try:
        with urlopen(requisicao, timeout=8) as resposta:
            return json.load(resposta)
    except HTTPError as erro:
        try:
            mensagem = json.loads(erro.read().decode('utf-8')).get('erro', str(erro))
        except (ValueError, UnicodeError):
            mensagem = str(erro)
        raise ValueError(f'API PHP respondeu erro: {mensagem}') from erro
    except (URLError, TimeoutError) as erro:
        raise ConnectionError(f'API PHP indisponível em {API_URL}; inicie o site antes do MCP.') from erro


def listar_produtos() -> list[dict]:
    return ler_api('produtos.php')


def buscar_produtos(nome: str) -> list[dict]:
    termo = nome.strip().casefold()
    return [p for p in listar_produtos() if termo in f"{p['nome']} {p['tipo']} {p['categoria']}".casefold()]


def filtrar_preco(preco_maximo: float, categoria: str = '') -> list[dict]:
    if preco_maximo < 0:
        raise ValueError('O preço máximo precisa ser maior ou igual a zero.')
    categoria = categoria.strip().casefold()
    return [p for p in listar_produtos()
            if p['preco'] <= preco_maximo and (not categoria or p['categoria'].casefold() == categoria)]


def detalhes_produto(produto_id: int) -> dict:
    if produto_id <= 0:
        raise ValueError('Informe um ID positivo.')
    return ler_api('produto.php', id=produto_id)


def verificar_estoque(produto_id: int) -> dict:
    p = detalhes_produto(produto_id)
    return {'id': p['id'], 'nome': p['nome'], 'estoque': p['estoque'], 'disponivel': p['estoque'] > 0}


def listar_categorias() -> list[str]:
    return sorted({p['categoria'] for p in listar_produtos()})
