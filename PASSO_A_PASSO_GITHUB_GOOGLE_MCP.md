# NEXUS Hardware — publicar GitHub, API PHP, MCP e Google

## 1. GitHub Pages: loja pública sem custo

1. Faça backup da pasta do projeto e extraia este ZIP.
2. Crie um repositório **público, vazio**, chamado `nexus-hardware` em https://github.com/new (não marque README, gitignore ou licença).
3. Se ainda não tiver Git para Windows, instale de https://git-scm.com/download/win.
4. Execute `PUBLICAR_NO_GITHUB.bat` na pasta principal, informe seu usuário GitHub e siga as mensagens. O Git poderá abrir uma janela de login.
5. No repositório, acesse **Settings → Pages → Build and deployment → Deploy from a branch → main → /docs → Save**.
6. Use o endereço informado em Settings → Pages para abrir a loja. Ela funcionará em **modo de demonstração sem backend**, com catálogo e carrinho usando dados no navegador.
7. Depois de confirmar o URL real, no terminal da pasta principal execute: `py scripts/preparar_publicacao.py --site-url https://SEU-USUARIO.github.io/nexus-hardware/` (ajuste seu endereço). Depois execute `git add docs`, `git commit -m "Adiciona sitemap e canonical"`, `git push`.

## 2. Render: backend PHP separado do GitHub Pages

1. Acesse https://dashboard.render.com e conecte o seu GitHub.
2. Selecione **New → Web Service**, escolha o repositório e **Docker** com plano **Free**, usando o Dockerfile da raiz.
3. No serviço, configure estas variáveis de ambiente: `PUBLIC_INDEXING=0`, `STORE_REAL=0`, `SITE_URL=https://NOME-REAL.onrender.com`, `ALLOWED_ORIGINS=https://SEU-USUARIO.github.io`. Não configure `DB_DSN` para uma demonstração temporária.
4. Teste a API: `https://NOME-REAL.onrender.com/api/health.php` e `/api/produtos.php` (10 produtos).
5. Para conectar o site à API, execute: `py scripts/preparar_publicacao.py --site-url https://SEU-USUARIO.github.io/nexus-hardware/ --api-url https://NOME-REAL.onrender.com/api`, depois `git add docs`, `git commit -m "Conecta frontend a API PHP"`, `git push`.
6. Confira no navegador com F12 → Network que `produtos.php` é solicitado no Render e responde HTTP 200. Faça uma compra fictícia para demonstrar.
7. No Render gratuito, o serviço pode desligar após inatividade e arquivos JSON são temporários. Para dados persistentes é necessário PostgreSQL remoto; o banco instalado no seu PC não será usado diretamente no Render.

## 3. MCP: busca de produtos com IA

1. Instale Python 3.11+ com o comando `py` funcionando.
2. Inicie a API no PC com `INICIAR_LOJA.bat`. Para testar usando Render, configure no PowerShell: `$env:NEXUS_API_URL='https://NOME-REAL.onrender.com/api'`.
3. Execute `py mcp_servidor/testar_consultas.py`: testa seis operações de busca/consulta sem depender do SDK MCP.
4. Execute `py -m pip install -r mcp_servidor/requirements.txt` (precisa de internet), depois `py mcp_servidor/testar_protocolo.py`: valida a descoberta e as chamadas pelo **protocolo MCP**.
5. Abra o MCP Inspector com `py -m mcp dev mcp_servidor/servidor.py` e faça os testes `buscar_produtos(nome="placa de vídeo")` e `filtrar_preco(preco_maximo=500)`.

**Nota:** MCP usa a API da loja; não significa que ChatGPT ou Google já indexaram o site.

## 4. Google: encontrabilidade da loja acadêmica

1. Primeiro confirme que a URL do GitHub Pages está pública e acessível em uma janela anônima.
2. Em https://search.google.com/search-console adicione uma propriedade **Prefixo de URL** com o endereço completo do Pages. Para verificar, o Google pode pedir um arquivo HTML: coloque-o na pasta `docs/`, envie ao GitHub e clique em **Verificar**.
3. Adicione o sitemap `https://SEU-USUARIO.github.io/nexus-hardware/sitemap.xml` em **Sitemaps**.
4. Em **Inspeção de URL**, consulte a página inicial e uma ficha `.../produtos/produto-1.html` e solicite indexação.
5. Pesquise no Google `site:SEU-USUARIO.github.io/nexus-hardware/` para conferir resultados. Pode demorar dias/semanas e a indexação não é garantida.
6. **Atenção:** esta loja é acadêmica, produtos e preços são fictícios e não representa comércio real. Deixe esse aviso visível. Não habilite schema Offer de produtos reais sem produtos reais.

## Testes e evidências para apresentar

- URL do GitHub Pages (site navegável com imagens, produtos, carrinho).
- URL do Render `/api/produtos.php` (JSON de 10 produtos).
- MCP Inspector (seis ferramentas, busca, filtro, consulta de estoque).
- Search Console: status de rastreamento e envio do sitemap. **Não confunda solicitação de indexação com estar indexado.**

**Segurança:** nunca envie `.env`, senha do PostgreSQL ou token administrativo ao GitHub. O `.gitignore` protege o `.env`.
