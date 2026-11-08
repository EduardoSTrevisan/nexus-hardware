"""Teste não destrutivo para o PC do aluno: loja, API e paginas SEO.
Abra o servidor primeiro: INICIAR_LOJA.bat
"""
import json
from urllib.request import urlopen
base = 'http://127.0.0.1:8000'
for path in ['/','/api/health.php','/api/produtos.php','/api/produto.php?id=1','/p.php?id=1','/robots.txt','/sitemap.xml']:
    with urlopen(base+path, timeout=6) as response:
        corpo = response.read()
        assert response.status == 200 and corpo
        if path.endswith('produtos.php'):
            assert len(json.loads(corpo)) == 10
        print('OK',path, response.status, len(corpo),'bytes')
print('OK: paginas e API disponiveis. Nao foi criado nenhum pedido.')
