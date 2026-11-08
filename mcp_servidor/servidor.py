"""MCP real, leitura apenas, consultando a API PHP da NEXUS Hardware.

Instale: python -m pip install -r mcp_servidor/requirements.txt
Examine com: python -m mcp dev mcp_servidor/servidor.py (ou 'mcp dev ...')
O cliente MCP normalmente inicia este script automaticamente via stdio.
"""
from __future__ import annotations

# SDK oficial v2 (2026). Nao renomeie o diretório para 'mcp', para não conflitar com a biblioteca.
from mcp.server import MCPServer
from catalogo import (listar_produtos as _listar, buscar_produtos as _buscar,
                      filtrar_preco as _preco, detalhes_produto as _detalhes,
                      verificar_estoque as _estoque, listar_categorias as _categorias)

mcp = MCPServer('NEXUS Hardware - Encontrabilidade')

@mcp.tool()
def listar_produtos() -> list[dict]:
    """Lista todos os produtos fictícios da loja e seus preços e estoques atualizados."""
    return _listar()

@mcp.tool()
def buscar_produtos(nome: str) -> list[dict]:
    """Busca produtos por nome, tipo ou categoria; ex.: placa de vídeo, mouse, SSD."""
    return _buscar(nome)

@mcp.tool()
def filtrar_preco(preco_maximo: float, categoria: str = '') -> list[dict]:
    """Busca hardware com preço em reais até um limite; categoria é opcional."""
    return _preco(preco_maximo, categoria)

@mcp.tool()
def detalhes_produto(produto_id: int) -> dict:
    """Mostra descrição, especificações técnicas e preço de um produto pelo ID."""
    return _detalhes(produto_id)

@mcp.tool()
def verificar_estoque(produto_id: int) -> dict:
    """Consulta estoque atual e se um produto está disponível."""
    return _estoque(produto_id)

@mcp.tool()
def listar_categorias() -> list[str]:
    """Lista todas as categorias disponíveis no catálogo de hardware."""
    return _categorias()

@mcp.resource('nexus://sobre')
def sobre_loja() -> str:
    """Contexto sobre a loja de demonstração e a origem dos dados."""
    return ('NEXUS Hardware é um projeto acadêmico fictício. Valores, imagens e estoques são demonstrativos. '
            'As ferramentas MCP consultam, em tempo real, a API PHP; a API consulta o PostgreSQL configurado '
            'ou o armazenamento local em JSON. Não existem pagamentos ou entregas reais.')

@mcp.prompt()
def recomendar_hardware(necessidade: str, teto_reais: float) -> str:
    """Roteiro para buscar itens no catálogo dentro de um orçamento fictício."""
    return (f'Consulte filtrar_preco com preco_maximo={teto_reais} e buscar_produtos para: {necessidade}. '
            'Recomende apenas produtos retornados pelas ferramentas; cite preços e estoques atuais. '
            'Não apresente esta loja acadêmica como comércio real.')

if __name__ == '__main__':
    mcp.run()
