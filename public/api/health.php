<?php
require_once __DIR__ . '/../../src/http.php';
exigirMetodo('GET');
executar(fn() => ['sucesso'=>true, 'aplicacao'=>'NEXUS Hardware', 'produtos'=>count(LojaStore::produtos()), 'armazenamento' => str_starts_with(envv('DB_DSN'), 'pgsql:') ? 'PostgreSQL' : (envv('DB_DSN') === '' ? 'Arquivo local (demo)' : 'Banco SQL')]);
