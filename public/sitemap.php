<?php
require_once __DIR__ . '/../src/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');
$url = base_url();
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
if (envv('PUBLIC_INDEXING') === '1' && envv('STORE_REAL') === '1') {
    echo '<url><loc>' . h($url . '/index.html') . '</loc></url>';
    foreach (LojaStore::produtos() as $p) echo '<url><loc>' . h($url . '/p.php?id=' . $p['id']) . '</loc></url>';
}
echo '</urlset>';
