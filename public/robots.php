<?php
require_once __DIR__ . '/../src/bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');
if (envv('PUBLIC_INDEXING') !== '1' || envv('STORE_REAL') !== '1') {
    echo "User-agent: *\nDisallow: /\n";
} else {
    echo "User-agent: *\nAllow: /\nDisallow: /api/\nDisallow: /carrinho.html\n\n";
    echo "User-agent: OAI-SearchBot\nAllow: /\nDisallow: /api/\n\n";
    echo "Sitemap: " . base_url() . "/sitemap.xml\n";
}
