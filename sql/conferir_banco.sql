-- Execute no Query Tool CONECTADO AO BANCO nexus_hardware.
-- Sao apenas consultas de leitura: nao apagam nem alteram nada.
SELECT current_database() AS banco_atual;
SELECT id, nome, categoria, preco_centavos / 100.0 AS preco_reais, estoque
FROM produtos ORDER BY id;
SELECT id, cliente_nome, total_centavos / 100.0 AS total_reais, criado_em
FROM pedidos ORDER BY id DESC LIMIT 10;
SELECT pedido_id, produto_id, quantidade, preco_centavos / 100.0 AS preco_unitario
FROM pedido_itens ORDER BY pedido_id DESC LIMIT 20;
