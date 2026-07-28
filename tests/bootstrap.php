<?php
// Test bootstrap for UPC2Item
// Load FAMock first to provide FA function stubs (db_query, db_escape, TB_PREF, etc.)
if (file_exists(__DIR__ . '/../vendor/ksfraser/famock/php/FAMock.php')) {
    require_once __DIR__ . '/../vendor/ksfraser/famock/php/FAMock.php';
}

// Load Composer autoloader
require __DIR__ . '/../vendor/autoload.php';
