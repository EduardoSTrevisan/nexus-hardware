**LEIA PRIMEIRO:** `PASSO_A_PASSO_GITHUB_GOOGLE_MCP.md` — instruções atualizadas.

# Publicar a NEXUS Hardware no GitHub e no Render

> Este arquivo deixa o caminho pronto para publicação. Sem acesso à sua conta, o projeto **não está publicado**. A primeira prioridade para a apresentação de amanhã é fazê-lo funcionar no WampServer com PostgreSQL e testar MCP.

## A. Publicar código no GitHub

1. No GitHub, crie um repositório chamado `nexus-hardware`, sem gerar README (este projeto já tem um).
2. Abra a pasta `nexus_hardware_completo` no terminal do VS Code e execute:

```bash
git init
git add .
git commit -m "NEXUS Hardware: frontend, API PHP, PostgreSQL e MCP"
git branch -M main
git remote add origin https://github.com/SEU-USUARIO/nexus-hardware.git
git push -u origin main
```

Substitua `SEU-USUARIO`. Não publique o arquivo `.env` (o `.gitignore` já o protege). **Antes do push, rode `git status` e confira que sua senha não está em nenhum arquivo adicionado.**

## B. Publicar o backend PHP no Render

1. Entre em <https://render.com> e crie um **Web Service** conectando seu repositório `nexus-hardware`.
2. Escolha deploy por **Docker** (o repositório já tem um `Dockerfile`).
3. O diretório raiz do serviço deve ser a pasta que contém `Dockerfile` (a mesma pasta do projeto, quando ela for a raiz do repositório).
4. Para uma **demonstração temporária**, configure variáveis: `PUBLIC_INDEXING=0`, `STORE_REAL=0`, `SITE_URL=https://NOME-DO-SEU-SERVICO.onrender.com`. Deixe `DB_DSN` vazio para o modo JSON de teste. Configure `ALLOWED_ORIGINS=https://SEU-USUARIO.github.io`.
5. Assim que estiver online, abra `https://NOME-DO-SEU-SERVICO.onrender.com/api/produtos.php` e confirme uma lista JSON com 10 produtos.

**Atenção:** o modo JSON do Render usa armazenamento temporário e **não garante persistência**; os pedidos/estoques podem zerar após deploys ou reinícios. Para aplicação persistente de verdade, crie um banco PostgreSQL hospedado e configure `DB_DSN`/`DB_USER`/`DB_PASSWORD` no serviço Render. O PostgreSQL instalado em seu PC **não** pode ser utilizado diretamente pela hospedagem externa sem configuração própria e segura.

## C. Publicar o frontend no GitHub Pages

O projeto já inclui a pasta **`docs/`** com todos os arquivos estáticos. O PHP fica no Render; o Pages só mostra o frontend.

1. Depois de o serviço Render estar funcionando, abra `docs/js/config.js` e troque `SUBSTITUA-SEU-SERVICO` pelo nome real do serviço Render. Exemplo: `https://nexus-api-exemplo.onrender.com/api`.
2. Salve, execute `git add docs/js/config.js`, `git commit -m "Configura URL do backend"`, `git push`.
3. No repositório do GitHub abra **Settings → Pages → Build and deployment** e selecione **Deploy from a branch → `main` → `/docs`**.
4. O endereço costuma ser `https://SEU-USUARIO.github.io/nexus-hardware/`. O site deve consumir a API Render, com CORS permitido por `ALLOWED_ORIGINS`.
5. No Chrome, pressione F12 → Network. Confirme que as requisições `/api/produtos.php` são feitas ao serviço Render, não ao domínio GitHub Pages.

**Limitação SEO:** o frontend do GitHub Pages é estático e carrega catálogo via JavaScript; as páginas `index.php` e `p.php` renderizadas no PHP (SEO) ficam no Render. Os dados são fictícios; não habilite indexação pública.

## D. MCP com backend público

Se o Render estiver online, ajuste `NEXUS_API_URL` do cliente MCP para `https://NOME-DO-SEU-SERVICO.onrender.com/api`. Não é necessário publicar o servidor MCP para testar em um cliente local. Como ele usa `stdio`, o cliente MCP executa `mcp_servidor/servidor.py` no seu PC.

## Checklist antes de mostrar ao professor

- [ ] Loja abre e pesquisa produtos.
- [ ] API PHP mostra JSON.
- [ ] PostgreSQL cadastra produtos e registra o pedido no seu PC.
- [ ] `mcp_servidor/testar_consultas.py` passa.
- [ ] MCP Inspector reconhece as ferramentas.
- [ ] Se a entrega exigir link público: backend Render e frontend GitHub Pages funcionando.
- [ ] Prints de evidência e PowerPoint prontos.
