<?php
require_once __DIR__ . '/../../src/http.php';
exigirMetodo('GET');
$subtotal = filter_var($_GET['subtotal_centavos'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>0,'max_range'=>100000000]]);
if ($subtotal === false || $subtotal === null) responder(['sucesso'=>false,'erro'=>'Subtotal inválido.'],400);
executar(fn() => LojaStore::calcularFrete((string)($_GET['cep'] ?? ''), (int)$subtotal));
