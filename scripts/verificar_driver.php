<?php
if (!extension_loaded('pdo_pgsql')) {
    fwrite(STDERR, "pdo_pgsql nao habilitado no PHP CLI.\n");
    exit(1);
}
echo "Driver pdo_pgsql: OK\n";
