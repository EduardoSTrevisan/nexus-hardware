
> **Para configurar no Windows hoje:** leia `COMECE_AQUI.txt` e execute, nessa ordem,
> `INSTALAR_BANCO.bat` → `VERIFICAR_BANCO.bat` → `INICIAR_LOJA.bat` → `MOSTRAR_PEDIDOS.bat` → `TESTAR_MCP.bat`.
> Veja também `PASSO_A_PASSO_POSTGRESQL.md`. O banco `nexus_hardware` já deve existir no pgAdmin.

> **PARA A APRESENTAÇÃO DE AMANHÃ:** comece por `COMECE_AQUI.txt` e `GUIA_APRESENTACAO.md`.
> No Windows, os atalhos `INSTALAR_BANCO.bat` e `INICIAR_LOJA.bat` automatizam os passos.

# NEXUS Hardware — loja virtual acadêmica (frontend + PHP + MCP)

Projeto de faculdade de **loja fictícia de hardware** usando tecnologias simples: HTML, CSS, JavaScript puro, PHP e um servidor MCP em Python. Os 10 produtos, preços e estoques são **ilustrativos**. Não recebe pagamentos nem realiza entregas reais.

## O que foi desenvolvido

- Site responsivo: início, catálogo, busca, categorias, ordenação, produto, carrinho e pedido simulado.
- API PHP: listar/detalhar produtos, registrar pedido, validar/baixar estoque, simular frete e restaurar estoque com token administrativo.
- Persistência: padrão JSON no servidor, com bloqueio de arquivo; adaptado para PostgreSQL 18 e MySQL.
- SEO/GEO: páginas de catálogo e de produto renderizadas no servidor PHP, metadados, canonical, JSON-LD, `robots.txt` e sitemap dinâmico. Demonstração **não indexável por padrão**.
- MCP Python: ferramentas para listar/buscar produtos, filtrar preços, consultar detalhes e estoque, recurso de contexto e um prompt de comparação. MCP é **leitura apenas**.
- Testes básicos da API em `tests/test_api.py`.

## 1. Executar no computador (jeito mais fácil)

**Requisito:** PHP 8.1 ou superior instalado (XAMPP também funciona, mas rode o comando no terminal para começar).

1. Extraia o projeto e abra a pasta `nexus_hardware_completo` no VS Code.
2. Abra o terminal nessa pasta.
3. Execute:

```bash
php -S localhost:8000 -t public public/router.php
```

4. Abra **http://localhost:8000** no navegador.

Não precisa instalar MySQL para a primeira demonstração: o PHP cria `storage/loja.json` automaticamente no servidor. **Não abra o HTML com duplo clique** para testar o backend; utilize o endereço do servidor PHP.

### Testar o pedido

1. Escolha um produto, adicione ao carrinho e vá para a finalização.
2. Digite um nome e e-mail de exemplo, opcionalmente um CEP fictício válido de oito dígitos.
3. Finalize o pedido simulado. A API gera um número `NX-000001`, grava o pedido e reduz o estoque.
4. Volte ao catálogo e confira a quantidade atualizada.

**Restaurar estoque:** por segurança, o botão simples de reset do modo de demonstração foi desabilitado na versão PHP. Para restaurar, configure no arquivo `.env` a variável `ADMIN_TOKEN` com **pelo menos 16 caracteres** e use a API com o header `X-Admin-Token`:

```bash
curl -X POST http://localhost:8000/api/restaurar_estoque.php -H "X-Admin-Token: COLOQUE_SEU_TOKEN_AQUI"
```

## 2. Arquivos e pastas

```text
nexus_hardware_completo/
  public/
    index.html              # Versão estática da vitrine
    index.php               # Vitrine renderizada pelo PHP (SEO)
    produto.html            # Versão estática do produto
    p.php                   # Página renderizada de produto (?id=1)
    carrinho.html           # Carrinho e finalização
    css/ assets/ js/       # HTML, estilos, imagens SVG, JavaScript
    api/
      produtos.php          # GET /api/produtos.php
      produto.php           # GET /api/produto.php?id=1
      frete.php             # GET /api/frete.php?cep=01001000&subtotal_centavos=10000
      finalizar_pedido.php  # POST /api/finalizar_pedido.php
      restaurar_estoque.php # POST /api/restaurar_estoque.php (protegido)
      health.php            # GET /api/health.php
    router.php              # Rotas para o servidor de desenvolvimento PHP
    robots.php sitemap.php # SEO dinâmico no servidor PHP
  src/                      # Regras do backend, persistência e validação
  seed/produtos.json        # Produtos e estoque inicial
  storage/                  # Pedidos/estoque atuais — não versionar
  sql/mysql.sql             # Tabelas MySQL
  scripts/popular_mysql.php # Importar os 10 produtos de exemplo
  mcp_servidor/servidor.py     # Servidor MCP em Python
  tests/test_api.py         # Testes do backend
  Dockerfile                # Opção para hospedar PHP no Render
  .env.example              # Variáveis de configuração
```

## 3. Endpoints PHP

| Método | URL | O que faz |
|---|---|---|
| GET | `/api/health.php` | Confere o funcionamento do servidor |
| GET | `/api/produtos.php` | Lista os produtos atuais (array JSON) |
| GET | `/api/produto.php?id=1` | Consulta um produto |
| GET | `/api/frete.php?cep=01001000&subtotal_centavos=10000` | Frete **simulado**, sem transportadora real |
| POST | `/api/finalizar_pedido.php` | Registra pedido e desconta do estoque |
| POST | `/api/restaurar_estoque.php` | Repõe estoque inicial, com token administrativo |

Exemplo de corpo JSON para finalizar:

```json
{
  "cliente": {"nome": "Aluno Exemplo", "email": "aluno@example.com", "cep": "01001-000"},
  "itens": [{"produto_id": 1, "quantidade": 1}, {"produto_id": 4, "quantidade": 2}]
}
```

O servidor calcula preços e frete por conta própria: não aceita valores de produtos enviados pelo navegador. Em caso de erro de estoque, a compra não altera o pedido/estoque. O frete é **uma regra fictícia**: gratuito a partir de R$ 3.000, R$ 24,90 para CEP começando em 01–19 e R$ 39,90 nos demais; o prazo apresentado também é fictício.

## 4. MySQL (quando quiser usar banco relacional)

O projeto **já tem o código para MySQL**, mas no modo inicial usa JSON para permitir os primeiros testes sem banco instalado.

1. Crie a base/tabelas executando `sql/mysql.sql` no phpMyAdmin ou cliente MySQL.
2. Copie `.env.example` para `.env` na raiz da pasta e configure:

```env
DB_DSN=mysql:host=127.0.0.1;port=3306;dbname=nexus_hardware;charset=utf8mb4
DB_USER=root
DB_PASSWORD=
SITE_URL=http://localhost:8000
```

3. Garanta que a extensão `pdo_mysql` do PHP esteja habilitada.
4. Importe os produtos iniciais uma vez:

```bash
php scripts/popular_mysql.php
```

5. Reinicie o servidor PHP. **A alteração do modo JSON para MySQL não migra automaticamente os pedidos antigos**. Guarde uma cópia do arquivo JSON caso queira preservá-los.

## 5. MCP — conexão com assistentes de IA

A API PHP deve estar funcionando antes de iniciar o MCP.

**Pré-requisitos:** Python 3.11+ e conexão com a internet para instalar a biblioteca oficial MCP:

```bash
python -m venv .venv
# Windows: .venv\Scripts\activate
# Linux/macOS: source .venv/bin/activate
python -m pip install -r mcp_servidor/requirements.txt
```

No mesmo computador, configure `NEXUS_API_URL=http://localhost:8000/api` e execute `python mcp_servidor/servidor.py`.

Normalmente **o aplicativo de IA inicia o MCP automaticamente**; você deve informar a ele o caminho do interpretador Python, o caminho absoluto de `mcp_servidor/servidor.py` e a variável de ambiente `NEXUS_API_URL`. Exemplo de configuração *genérica*, adapte ao formato exigido pelo seu aplicativo:

```json
{
  "mcpServers": {
    "nexus-hardware": {
      "command": "CAMINHO_COMPLETO_DO_PYTHON",
      "args": ["CAMINHO_COMPLETO_DO_PROJETO/mcp_servidor/servidor.py"],
      "env": {"NEXUS_API_URL": "http://localhost:8000/api"}
    }
  }
}
```

Para inspecionar com a ferramenta oficial MCP Inspector (Node.js/npx necessário):

```bash
mcp dev mcp_servidor/servidor.py
```

Ferramentas disponíveis: `listar_produtos`, `buscar_produtos`, `filtrar_preco`, `detalhes_produto`, `verificar_estoque`, `listar_categorias`. Recurso: `nexus://sobre`. Prompt: `recomendar_hardware`.

**Importante:** publicar um site **não conecta automaticamente** esse MCP ao ChatGPT/Claude/Gemini. Um cliente compatível precisa ser configurado. O servidor atual usa `stdio` (execução local), não é um endpoint MCP público. Não há compras via MCP, somente consultas.

## 6. SEO e encontrabilidade em IA

Quando servido pelo PHP:

- `http://localhost:8000/index.html` entrega **HTML contendo os produtos**, mesmo sem JavaScript.
- `http://localhost:8000/p.php?id=1` entrega **HTML com os detalhes**, metadados e `Product` em JSON-LD.
- `http://localhost:8000/robots.txt` e `/sitemap.xml` são dinâmicos.
- Por padrão, `PUBLIC_INDEXING=0` e `STORE_REAL=0`: **robots bloqueia indexação e as páginas usam noindex**. É intencional, pois os produtos são fictícios.

**Para um comércio verdadeiro**, antes de liberar a indexação:

1. Troque os exemplos por produtos, preços, imagens e estoques reais.
2. Inclua as informações comerciais reais (contato, política de troca, privacidade, frete, dados da empresa e processos de pagamento apropriados).
3. Instale hospedagem com HTTPS, banco persistente e domínio próprio. Configure `SITE_URL=https://seudominio...`.
4. Só então configure `STORE_REAL=1` e `PUBLIC_INDEXING=1` em `.env`/variáveis do servidor. O código passa a emitir informações de oferta no schema, sitemap e regras de rastreamento.
5. Valide schema em [Rich Results Test](https://search.google.com/test/rich-results), envie sitemap ao Google Search Console e Bing Webmaster Tools e confirme o acesso do `OAI-SearchBot`.

**Nem SEO nem MCP garantem aparecer nas respostas de IA.** São mecanismos diferentes: SEO/GEO facilitam que ferramentas de busca entendam páginas públicas; MCP expõe funções a clientes que você autorizou a se conectar. Nunca adicione avaliações inventadas ou alegações comerciais falsas.

## 7. Publicação

**Render (PHP):** o `Dockerfile` configura PHP 8.4 com `pdo_mysql` e inicia o servidor na porta fornecida pelo serviço. Crie um *Web Service* Docker no Render apontando para o repositório com essa pasta na raiz. Defina `SITE_URL`, `PUBLIC_INDEXING`, `STORE_REAL`, `DB_DSN`, `DB_USER`, `DB_PASSWORD`, `ADMIN_TOKEN` conforme o ambiente.

**Atenção:** o modo JSON em hospedagem com armazenamento **temporário** pode perder dados ao reiniciar, fazer deploy ou escalar. Para pedidos/estoque persistentes, use um MySQL externo gerenciado e faça backups. O servidor embutido do PHP incluído é apropriado à atividade/demonstração, **não é um servidor de produção robusto** para loja com clientes reais.

**GitHub Pages (frontend separado):** use a pasta `docs/` já preparada e selecione `/docs` na configuração do Pages. Somente HTML, CSS, JS e imagens são publicados. PHP **não executa** no GitHub Pages. Configure em `public/js/config.js`:

```js
const CONFIG = {
  modo: 'php',
  apiBase: 'https://SEU-BACKEND-NO-RENDER.onrender.com/api',
  seoProductPages: false
};
```

Publique os arquivos estáticos (`index.html`, `produto.html`, `carrinho.html`, `css`, `js`, `assets`) e configure `ALLOWED_ORIGINS=https://SEUUSUARIO.github.io` no backend. Nesse formato, as páginas estáticas dependem de JavaScript para exibir os produtos: **o melhor SEO será conseguido quando as páginas de catálogo/produto forem servidas pelo PHP**, como em `index.php`/`p.php`.

## 8. Testar automaticamente

Com PHP 8.1+ e Python 3 instalados:

```bash
python tests/test_api.py
```

O teste cria temporariamente um servidor, faz pedidos fictícios, verifica a baixa do estoque, valida proteção de reset, a renderização dos produtos no HTML, `robots.txt` e `sitemap.xml`. Ele remove os dados gerados após o teste. **Não execute em uma pasta com pedidos reais**.

## 9. Próximos passos antes de vender de verdade

Este trabalho é um protótipo acadêmico. Falta implementar autenticação/administração, proteção contra abuso, pagamento integrado, frete de transportadora, tratamento legal de dados pessoais, termos e políticas, fotos e preços reais, HTTPS no domínio final e infraestrutura de produção com backups. Não converta `STORE_REAL` para `1` enquanto esses requisitos não forem atendidos.

### Fontes das decisões de SEO e MCP

- [SDK MCP Python v2](https://py.sdk.modelcontextprotocol.io/)
- [Google: dados estruturados de produtos](https://developers.google.com/search/docs/appearance/structured-data/product)
- [OpenAI: encontrabilidade e OAI-SearchBot](https://help.openai.com/pt-br/articles/12627856-publicadores-e-desenvolvedores-faq)


## PostgreSQL 18 no WampServer (Windows)

**Se você já usa PostgreSQL no pgAdmin, não precisa criar outra instalação.**

1. Crie o banco vazio `nexus_hardware` (não use o banco do WashTEC).
2. Abra o Query Tool desse banco e execute o arquivo `sql/postgresql.sql`.
3. Crie `.env` na raiz, copiando `.env.example`, com estas linhas (sem colchetes):

```env
SITE_URL=http://localhost:8000
DB_DSN=pgsql:host=127.0.0.1;port=5432;dbname=nexus_hardware
DB_USER=postgres
DB_PASSWORD=SUA_SENHA_LOCAL
PUBLIC_INDEXING=0
STORE_REAL=0
```

Não compartilhe sua senha e não publique `.env` no GitHub. Se a senha tiver espaços, `#`, aspas ou caracteres especiais, use a variável `DB_PASSWORD` no ambiente do Windows, pois o leitor `.env` desse projeto é propositalmente simples.

4. No terminal PowerShell do VS Code, de dentro da pasta do projeto:

```powershell
$php = Get-ChildItem 'C:\wamp64\bin\php\php*\php.exe' | Sort-Object FullName -Descending | Select-Object -First 1
& $php.FullName scripts/testar_postgresql.php
& $php.FullName scripts/popular_postgresql.php
& $php.FullName -S localhost:8000 -t public public/router.php
```

5. Abra `http://localhost:8000`, entre em Produtos e faça um pedido fictício. No pgAdmin, confirme os registros com:

```sql
SELECT id,nome,estoque FROM produtos ORDER BY id;
SELECT id,cliente_nome,total_centavos FROM pedidos ORDER BY id DESC LIMIT 5;
SELECT pedido_id,produto_id,quantidade FROM pedido_itens ORDER BY pedido_id DESC LIMIT 5;
```

Quando DB_DSN está preenchido, a loja usa PostgreSQL, não o arquivo JSON antigo. Os pedidos anteriores guardados em `storage/loja.json` **não são migrados automaticamente**. O banco de dados começa vazio, e a importação adiciona os dez produtos de demonstração.

**Xdebug antigo:** se o PHP apresentar `Failed Loading E:/wamp64/...`, confira a diretiva `zend_extension` no `php.ini` e corrija para o arquivo que realmente existir em `C:\wamp64`. Esse aviso é independente do PostgreSQL.


## Verificação no Windows

- `INSTALAR_BANCO.bat`: cria tabelas PostgreSQL, configura `.env` e importa produtos.
- `INICIAR_LOJA.bat`: inicia PHP no WampServer sem depender de PATH.
- `py mcp_servidor/testar_consultas.py`: exercita consultas que o MCP usará.
- `py -m pip install -r mcp_servidor/requirements.txt`: instala o SDK MCP.
- `py -m mcp dev mcp_servidor/servidor.py`: abre a ferramenta de inspeção MCP.

A conexão MCP real e o PostgreSQL do aluno precisam de execução local; não foram verificados nesta máquina.


**Publicação orientada:** consulte `PUBLICAR_GITHUB_RENDER.md`. A pasta `docs/` foi preparada para GitHub Pages, mas só funcionará com API publicada e URL ajustada.
