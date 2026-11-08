# Verificação da NEXUS Hardware para publicação — 08/11/2026

## Concluído nesta versão

- Teste funcional de API PHP: **PASSOU**. Validou 10 produtos, detalhes, frete, pedidos, queda do estoque, bloqueio por falta de estoque, reset autorizado, páginas SSR, robots e sitemap PHP.
- Integração das seis consultas Python usadas pelo MCP com a API PHP: **PASSOU**. Busca de placa de vídeo retornou produto e dados corretos.
- Código Python do MCP: compilação: **PASSOU**.
- Arquivos JS de `docs/`: teste sintático `node --check`: **PASSOU**.
- GitHub Pages preparado em modo demo: arquivo `docs/js/config.js` usa demo e não referencia URL Render inexistente.
- Publicação SEO das fichas: 10 páginas HTML estáticas geradas, ligadas à página inicial e com avisos de conteúdo fictício.
- Sitemap: teste com URL de exemplo: **PASSOU**, XML válido com 11 URLs e URL canônica correspondente; gere com sua URL real depois de publicar.
- `.gitignore`: validado; arquivo `.env` não é adicionado ao repositório.
- PHP código do `router.php`: sintaxe: **PASSOU**.

## Pendente — precisa do seu ambiente e/ou acesso à conta

- O site **ainda não foi publicado** na sua conta GitHub, pois não há uma conexão com permissão de escrita confirmada nem a URL do repositório.
- Render **ainda não foi publicado**: precisa conectar GitHub e configurar o serviço e as variáveis.
- Protocolo MCP real: o SDK Python não pôde ser instalado neste ambiente por falta de acesso ao repositório de pacotes. O arquivo `mcp_servidor/testar_protocolo.py` está preparado para fazer esse teste no seu computador.
- Teste visual automatizado em navegador: Chromium do ambiente travou ao executar, então essa etapa não foi validada; o frontend foi verificado por arquivos/JS, mas deve ser aberto no Chrome no seu computador.
- Google Search e Search Console: **não verificável antes de existir uma URL pública confirmada**. Sitemap e rastreamento não garantem indexação; leva tempo.

## Diferenças entre as buscas

- MCP: a IA conectada consulta seu servidor via protocolo; não é mecanismo de indexação pública do Google.
- Google: precisa de URL pública acessível e de indexação. A versão estática foi preparada para ser rastreável, mas a inclusão do Google é decisão do buscador.
- Google e MCP podem funcionar de forma independente.

Esta é uma **demonstração acadêmica**, sem ofertas de venda reais.
