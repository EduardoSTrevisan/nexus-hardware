"""Teste REAL do protocolo MCP v2 com cliente in-process, usando a API PHP.

Exige SDK: py -3 -m pip install -r mcp_servidor/requirements.txt
Exige API PHP iniciada em http://127.0.0.1:8000.
"""
import asyncio
from mcp import Client
from servidor import mcp

async def main():
    async with Client(mcp, raise_exceptions=True) as cliente:
        resultado = await cliente.list_tools()
        nomes = [ferramenta.name for ferramenta in resultado.tools]
        print('Ferramentas MCP detectadas:', ', '.join(nomes))
        necessarias = {'listar_produtos','buscar_produtos','filtrar_preco','detalhes_produto','verificar_estoque','listar_categorias'}
        faltando = necessarias - set(nomes)
        if faltando:
            raise RuntimeError('Ferramentas ausentes: ' + ', '.join(sorted(faltando)))
        consulta = await cliente.call_tool('buscar_produtos', {'nome': 'placa de vídeo'})
        if consulta.is_error:
            raise RuntimeError('MCP informou erro ao buscar produtos: ' + str(consulta.content))
        if 'Placa de Vídeo' not in str(consulta.structured_content or consulta.content):
            raise RuntimeError('Busca MCP nao retornou a placa de video esperada.')
        preco = await cliente.call_tool('filtrar_preco', {'preco_maximo': 500.0})
        if preco.is_error:
            raise RuntimeError('MCP informou erro no filtro de preco.')
        print('SUCESSO: Protocolo MCP, descoberta de ferramentas, busca e filtro testados.')
        print('O MCP consultou a API PHP em tempo real. Ainda nao significa indexacao publica por IA.')

if __name__ == '__main__':
    asyncio.run(main())
