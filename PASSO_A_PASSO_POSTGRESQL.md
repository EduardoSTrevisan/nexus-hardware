# NEXUS Hardware — configuração do PostgreSQL 18

Este guia foi feito para **Windows + WampServer + pgAdmin 4**, usando o banco `nexus_hardware` que você já criou. **Não use o banco `washtec_v4`.**

## Método recomendado: instalador automático

1. Extraia o ZIP para uma pasta nova, por exemplo `C:\wamp64\www\nexus_hardware_entrega_pronta`. Abra-a no VS Code.
2. Feche o servidor antigo (terminal com PHP: `Ctrl+C`) para evitar que uma cópia antiga fique aberta.
3. Execute **`INSTALAR_BANCO.bat`** com dois cliques. Ele pedirá o usuário (geralmente `postgres`) e a senha do seu PostgreSQL **somente no seu computador**.
4. Deve aparecer `BANCO CONFIGURADO!`. Se aparecer erro, confira se o serviço PostgreSQL está iniciado, se o banco `nexus_hardware` existe, e se a porta é `5432`.
5. Execute **`VERIFICAR_BANCO.bat`**. Ele deve listar as tabelas e 10 produtos.
6. Execute **`INICIAR_LOJA.bat`**. Acesse `http://localhost:8000` e teste a compra fictícia.
7. Execute **`MOSTRAR_PEDIDOS.bat`**. O pedido concluído deverá aparecer com número `NX-...`, total e quantidade de itens.
8. Se quiser ver os registros no pgAdmin, abra o Query Tool **do `nexus_hardware`** e execute `sql/conferir_banco.sql`.

**O instalador cria o arquivo `.env` na pasta principal.** Ele contém credenciais locais, é ignorado pelo Git e **não deve ser publicado**. Se houver um `.env` antigo, ele é copiado para `.env.backup` antes da alteração.

## Método manual (sem BAT)

No pgAdmin, expanda `Servers → PostgreSQL 18 → Databases → nexus_hardware`. Clique no banco `nexus_hardware`, abra **Tools → Query Tool** e execute o conteúdo de `sql/postgresql.sql` (criação das tabelas). Depois crie o arquivo `.env` na raiz da pasta, copiando `.env.example`, e defina:

```ini
SITE_URL=http://localhost:8000
PUBLIC_INDEXING=0
STORE_REAL=0
DB_DSN=pgsql:host=127.0.0.1;port=5432;dbname=nexus_hardware
DB_USER=postgres
DB_PASSWORD=COLOQUE_A_SENHA_APENAS_NO_SEU_PC
ADMIN_TOKEN=
ALLOWED_ORIGINS=
```

No PowerShell do VS Code, encontre seu PHP do WampServer:

```powershell
$php = Get-ChildItem 'C:\wamp64\bin\php\php*\php.exe' | Sort-Object FullName -Descending | Select-Object -First 1
& $php.FullName scripts/instalar_postgresql.php
& $php.FullName scripts/verificar_banco.php
& $php.FullName -S 127.0.0.1:8000 -t public public/router.php
```

O `instalar_postgresql.php` cria as tabelas e importa os dez produtos sem apagar pedidos existentes. O modo PHP usa o PostgreSQL quando `DB_DSN` está configurado. **Nunca publique sua senha nem seus dados de acesso.**

## Como mostrar que é banco de verdade

Após finalizar uma compra na página:

```sql
-- Executar SOMENTE no Query Tool do nexus_hardware
SELECT id, cliente_nome, total_centavos / 100.0 AS total_reais, criado_em
FROM pedidos ORDER BY id DESC LIMIT 10;

SELECT pedido_id, produto_id, quantidade
FROM pedido_itens ORDER BY pedido_id DESC LIMIT 20;

SELECT id, nome, estoque FROM produtos ORDER BY id;
```

O fluxo real é **HTML/CSS/JavaScript → API PHP → PostgreSQL**. O MCP Python chama essa mesma API PHP para consultar o catálogo. Nenhum pagamento nem frete real é processado.

## Testar MCP

Mantenha a loja/servidor PHP em funcionamento, e clique em `TESTAR_MCP.bat` (o computador precisa de Python 3.11+ e internet para instalar as dependências). O teste usa **um cliente MCP verdadeiro**, descobre as 6 ferramentas e executa uma busca e um filtro consultando a API. Também dá para usar o Inspector:

```powershell
py -3 -m pip install -r mcp_servidor/requirements.txt
py -3 -m mcp dev mcp_servidor/servidor.py
```

A SDK Python oficial v2 expõe `MCPServer` e as tools via o protocolo MCP. **MCP é uma integração com agentes conectados; não garante que a loja seja encontrada espontaneamente em pesquisas públicas de IA.**

## O que ainda depende do seu computador

Eu consigo entregar e testar a programação, mas não acesso o PostgreSQL nem o WampServer do seu PC diretamente. A confirmação da conexão com **seu** banco precisa acontecer executando os scripts acima. A publicação na internet também depende de configurar suas contas.
