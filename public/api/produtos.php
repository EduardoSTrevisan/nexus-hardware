<?php
require_once __DIR__ . '/../../src/http.php';
exigirMetodo('GET');
executar(fn() => LojaStore::produtos());
