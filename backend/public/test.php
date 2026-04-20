<?php
$start = microtime(true);
try {
    $pdo = new PDO('pgsql:host=pgsql;port=5432;dbname=pipitnesan', 'sail', 'password', [
        PDO::ATTR_TIMEOUT => 3 // force short timeout
    ]);
    echo "DB OK: " . (microtime(true) - $start) . "\n";
} catch (Exception $e) {
    echo "DB ERR: " . (microtime(true) - $start) . " " . $e->getMessage() . "\n";
}
