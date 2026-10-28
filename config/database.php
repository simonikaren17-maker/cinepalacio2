<?php
/**
 * Cine Palácio - Configurações de Banco de Dados
 * Estética Cinema Brasileiro Anos 70
 */

// Permite sobrescrever via variáveis de ambiente se disponíveis
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'mysql'); // 'mysql' ou 'sqlite'
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'cinepalacio');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

// Caminho do arquivo SQLite de fallback/desenvolvimento imediato
define('SQLITE_PATH', __DIR__ . '/../database/cinepalacio.sqlite');
define('SCHEMA_SQL_PATH', __DIR__ . '/../database/schema.sql');
