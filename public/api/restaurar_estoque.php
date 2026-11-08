<?php
require_once __DIR__ . '/../../src/http.php';
exigirMetodo('POST');
$token = envv('ADMIN_TOKEN');
if (strlen($token) < 16) responder(['sucesso'=>false,'erro'=>'Configure ADMIN_TOKEN de pelo menos 16 caracteres no servidor.'],503);
if (!hash_equals($token, (string)($_SERVER['HTTP_X_ADMIN_TOKEN'] ?? ''))) responder(['sucesso'=>false, 'erro'=>'Sem autorização.'],403);
executar(fn() => ['sucesso'=>true, 'produtos_restaurados'=>LojaStore::reset()]);
