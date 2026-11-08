<?php
require_once __DIR__ . '/../../src/http.php';
exigirMetodo('GET');
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if (!$id) responder(['sucesso'=>false,'erro'=>'ID inválido.'],400);
executar(function() use ($id) {
    $produto = LojaStore::produto((int)$id);
    if (!$produto) responder(['sucesso'=>false,'erro'=>'Produto não encontrado.'],404);
    return $produto;
});
