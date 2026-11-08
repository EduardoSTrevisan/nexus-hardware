"""Teste das consultas que alimentam o MCP; nao exige o SDK nem um cliente de IA.
Primeiro inicie a API PHP em http://localhost:8000.
"""
from catalogo import listar_produtos, buscar_produtos, filtrar_preco, detalhes_produto, verificar_estoque, listar_categorias

produtos = listar_produtos()
assert len(produtos) == 10, f'Esperados 10 produtos; recebidos {len(produtos)}'
assert buscar_produtos('placa de vídeo'), 'Busca não encontrou a placa'
assert filtrar_preco(400), 'Filtro não encontrou produtos'
assert detalhes_produto(1)['id'] == 1
assert verificar_estoque(1)['estoque'] >= 0
assert len(listar_categorias()) >= 2
print('OK: MCP -> API PHP: 10 produtos, busca, preço, detalhes, estoque e categorias.')
print('Exemplo de busca "placa de vídeo":')
for p in buscar_produtos('placa de vídeo'):
    print(f"  {p['id']} | {p['nome']} | R$ {p['preco']:.2f} | estoque {p['estoque']}")
print('\nEste teste valida as consultas. O protocolo MCP precisa ser validado no Inspector/cliente.')
