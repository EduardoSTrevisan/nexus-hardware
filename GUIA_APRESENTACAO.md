# NEXUS Hardware — guia para finalizar e apresentar

> Projeto acadêmico demonstrativo. Não há vendas, pagamentos, transportadoras ou estoque reais. Prazo de apresentação informado: **9 de novembro de 2026**.

## O que efetivamente foi desenvolvido

| Item | Estado | Como mostrar |
| --- | --- | --- |
| Site responsivo HTML/CSS/JS | Implementado e exibido no WampServer | Abrir `http://localhost:8000` |
| Catálogo, busca, categorias e detalhes | Implementado; validado pela API | Pesquisar **placa de vídeo** |
| Carrinho, finalização simulada | Implementado; testes de API passaram | Criar pedido de teste |
| API REST em PHP | Implementado; testes automatizados passaram | `http://localhost:8000/api/produtos.php` |
| Estoque, validação e transação | Implementado; testes em armazenamento local passaram | Comprar e verificar a quantidade cair |
| PostgreSQL 18 | Estrutura e scripts prontos; precisa ser configurado e testado no seu PC | Rodar `INSTALAR_BANCO.bat` e mostrar tabelas no pgAdmin |
| MCP Python | Ferramentas prontas; precisa instalar SDK e validar protocolo em cliente real | Rodar o Inspector, chamar `buscar_produtos` |
| SEO/GEO | Metadados, JSON-LD de produto, sitemap e HTML renderizado por PHP | Abrir código-fonte de `p.php?id=1` |
| GitHub e hospedagem pública | **Não publicados automaticamente** | Publicar apenas se necessário e houver tempo |

**O professor disponibilizou** <https://github.com/renatoviolin/mcp-encontrabilidade>. Na data da preparação o repositório aparecia público, mas sem README que especificasse a rubrica completa. Assim, este projeto foi orientado pelas demonstrações das aulas e pela atividade de loja e MCP descrita em aula, não por um enunciado escrito verificável.

## Instalação — seu Windows / WampServer / pgAdmin

1. Extraia a pasta e verifique se o PostgreSQL está ligado. O banco `nexus_hardware` já foi criado por você; não use `washtec_v4`.
2. Execute **`INSTALAR_BANCO.bat`**. Insira a senha do usuário `postgres` somente no seu computador. O programa cria `.env`, executa `sql/postgresql.sql` e importa 10 produtos. Se já existe `.env`, ele salva cópia em `.env.backup`.
3. Execute **`INICIAR_LOJA.bat`**; deixe a janela aberta e abra <http://localhost:8000>.
4. Acesse <http://localhost:8000/api/health.php> e <http://localhost:8000/api/produtos.php>. O segundo deve retornar uma lista com 10 produtos.
5. Confira no pgAdmin: banco `nexus_hardware` → Schemas → public → Tables → `produtos`, `pedidos`, `pedido_itens`. Antes da compra: `SELECT id, nome, estoque FROM produtos ORDER BY id;`. Depois da compra, execute a mesma consulta.
6. Faça uma compra **simulada** no carrinho. Confirme o número `NX-...` e a baixa do estoque. Execute no pgAdmin: `SELECT id, cliente_nome, total_centavos, criado_em FROM pedidos ORDER BY id DESC LIMIT 5;`.

Se não quiser usar o assistente, configure manualmente `.env` copiando `.env.example` com `DB_DSN=pgsql:host=127.0.0.1;port=5432;dbname=nexus_hardware`, `DB_USER=postgres` e `DB_PASSWORD=...`, rode `php scripts/instalar_postgresql.php` e ligue o servidor PHP. **Nunca inclua a senha em print ou apresentação.**

## Testar a encontrabilidade (MCP)

1. Mantenha o PHP ligado, pois as tools MCP consultam a API PHP.
2. Abra um segundo terminal no VS Code e rode `py mcp_servidor/testar_consultas.py`. Isso prova que as consultas Python enxergam o catálogo real da API, **mas ainda não prova negociação do protocolo MCP**.
3. Instale o SDK: `py -m pip install -r mcp_servidor/requirements.txt`. Requer Python 3.11+.
4. Abra o MCP Inspector: `py -m mcp dev mcp_servidor/servidor.py` (se o comando não estiver disponível, use `mcp dev mcp_servidor/servidor.py`). Ele pode precisar instalar ferramentas Node para abrir a interface; siga a mensagem do SDK.
5. Dentro do Inspector, execute `buscar_produtos` com `nome` = `placa de vídeo`; `filtrar_preco` com `preco_maximo` = `400`; `verificar_estoque` com `produto_id` = `1`.

**Para conectar a uma IA que suporte MCP local por `stdio`:** nas configurações do cliente, informe o Python da máquina (`py` ou caminho absoluto para `python.exe`), argumento com caminho absoluto de `mcp_servidor/servidor.py` e `NEXUS_API_URL=http://127.0.0.1:8000/api`. Exemplo para Claude Desktop (ajuste o caminho e a instalação do Python):

```json
{
  "mcpServers": {
    "nexus-hardware": {
      "command": "C:\\CAMINHO\\PARA\\python.exe",
      "args": ["C:\\CAMINHO\\PARA\\nexus_hardware_completo\\mcp_servidor\\servidor.py"],
      "env": {"NEXUS_API_URL": "http://127.0.0.1:8000/api"}
    }
  }
}
```

Cliente MCP + MCP Python → API PHP → PostgreSQL. Sem configurar explicitamente o cliente MCP, nenhuma IA vai acessar a loja por esse protocolo. O MCP desenvolvido é somente de **consulta**. O código está em `mcp_servidor/servidor.py`.

## Sequência sugerida para demonstrar (5 minutos)

1. **30 s — introdução:** "Eu fiz uma loja demonstrativa de hardware chamada NEXUS. O objetivo não era vender de verdade, mas mostrar a comunicação entre frontend, API PHP, banco e ferramentas de IA."
2. **60 s — site:** mostre catálogo, filtro por categoria, busca e um produto com suas especificações.
3. **60 s — backend e PostgreSQL:** abra a API JSON e a tabela `produtos` no pgAdmin. Crie uma compra e mostre `pedidos` e o estoque atualizado.
4. **90 s — MCP:** explique que o servidor MCP expõe tools para ferramentas de IA conectadas. No Inspector ou cliente configurado, teste buscar produto, filtrar por preço, consultar estoque.
5. **30 s — encontrabilidade:** mostre `p.php?id=1` e o JSON-LD; explique **MCP ≠ busca pública**. O MCP integra clientes, enquanto SEO/GEO deixa conteúdo público compreensível para indexadores.
6. **30 s — conclusão:** "A mesma informação do banco é consumida pela loja e pelo servidor MCP, evitando digitar preço ou estoque duas vezes. É um protótipo acadêmico; a publicação pública e a venda real exigem outros cuidados."

## Perguntas que o professor pode fazer

**Por que usar PHP?** Para desenvolver uma API REST simples com uma linguagem que usamos nas aulas. O frontend usa JavaScript puro e `fetch` para consumir o PHP.

**Onde ficam os dados?** No PostgreSQL 18, quando `DB_DSN` está configurado; senão, o projeto usa JSON local somente para testes. O backend calcula o preço do pedido a partir do cadastro, sem confiar no valor enviado pelo navegador.

**O que é MCP?** É um protocolo que permite que um cliente de IA conectado descubra e utilize ferramentas. No nosso caso elas são `listar_produtos`, `buscar_produtos`, `filtrar_preco`, `detalhes_produto`, `verificar_estoque`, `listar_categorias`.

**MCP faz aparecer automaticamente no ChatGPT?** Não. A conexão local precisa ser configurada e sites públicos precisam de SEO/GEO. Este projeto é fictício, logo a indexação pública fica bloqueada por padrão.

**Por que não usar GitHub Pages para tudo?** Porque GitHub Pages só hospeda frontend estático, não executa PHP. A API precisa estar hospedada separadamente (ex.: Render com Docker) e o banco precisa ser acessível pela hospedagem, com segurança.

## Código/links úteis

- Professor: <https://github.com/renatoviolin/mcp-encontrabilidade>
- SDK MCP Python: <https://github.com/modelcontextprotocol/python-sdk>
- `public/index.php`: HTML de catálogo renderizado no PHP.
- `public/p.php`: página de produto renderizada no PHP com Schema.org.
- `public/api/*.php`: API.
- `src/store.php`: regra do estoque, pedidos e banco.
- `sql/postgresql.sql`: estrutura do banco.
- `mcp_servidor/servidor.py`: ferramentas MCP.

## Pendente fora do seu computador

Não tenho acesso às suas contas nem ao seu PostgreSQL local. **Você** ainda precisa executar o instalador do banco, confirmar que ele se conecta, fazer o teste MCP no Inspector/cliente e publicar no GitHub/hosting, se a entrega exigir URL. O servidor PHP incluído (`php -S`) serve para demonstração e não deve ser tratado como e-commerce de produção.
