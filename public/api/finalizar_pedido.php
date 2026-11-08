<?php
require_once __DIR__ . '/../../src/http.php';
exigirMetodo('POST');
$dados = entrada();
executar(fn() => LojaStore::pedido($dados['cliente'] ?? [], $dados['itens'] ?? []));
