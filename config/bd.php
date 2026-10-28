<?php
/**
 * Cine Palácio - Conexão Segura com Banco de Dados via PDO
 * Compatível com MySQL (e fallback automático para SQLite em desenvolvimento local)
 */

require_once __DIR__ . '/database.php';

function getDBConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $driver = DB_DRIVER;
    $errors = [];

    // Tentativa 1: Conectar ao MySQL via PDO (Requisito Principal do Projeto)
    if ($driver === 'mysql' || $driver === 'auto') {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_CHARSET
            );
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            $tempPdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            // Garante que o banco de dados exista
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $tempPdo->exec("USE `" . DB_NAME . "`");
            $pdo = $tempPdo;

            // Inicializa tabelas se ainda não existirem
            verificarEInicializarSchema($pdo, 'mysql');
            return $pdo;
        } catch (PDOException $e) {
            $errors[] = "MySQL: " . $e->getMessage();
        }
    }

    // Tentativa 2: Fallback transparente para SQLite para permitir execução imediata sem travas
    try {
        $sqliteFile = SQLITE_PATH;
        $dir = dirname($sqliteFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $dsn = "sqlite:" . $sqliteFile;
        $pdo = new PDO($dsn, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);

        // Ativa chaves estrangeiras no SQLite
        $pdo->exec("PRAGMA foreign_keys = ON;");
        verificarEInicializarSchema($pdo, 'sqlite');
        return $pdo;
    } catch (PDOException $e) {
        $errors[] = "SQLite: " . $e->getMessage();
        die("<h3>Erro ao conectar ao Banco de Dados</h3><p>Verifique o arquivo <code>config/database.php</code></p><pre>" . htmlspecialchars(implode("\n", $errors)) . "</pre>");
    }
}

/**
 * Cria as tabelas e insere os filmes se a base estiver vazia
 */
function verificarEInicializarSchema(PDO $pdo, string $driver) {
    try {
        $check = $pdo->query("SELECT 1 FROM filmes LIMIT 1");
        if ($check) {
            return; // Banco já possui a tabela filmes
        }
    } catch (Exception $e) {
        // Tabela não existe, criar schema
    }

    if ($driver === 'mysql') {
        if (file_exists(SCHEMA_SQL_PATH)) {
            $sql = file_get_contents(SCHEMA_SQL_PATH);
            $pdo->exec($sql);
        }
    } else {
        // Schema adaptado para SQLite
        criarSchemaSqlite($pdo);
    }
}

/**
 * Cria tabelas e insere filmes na base SQLite
 */
function criarSchemaSqlite(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE,
            senha TEXT NOT NULL,
            token_recuperacao TEXT DEFAULT NULL,
            token_expira TEXT DEFAULT NULL,
            criado_em TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS filmes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            titulo TEXT NOT NULL,
            ano INTEGER NOT NULL,
            diretor TEXT NOT NULL,
            generos TEXT NOT NULL,
            classificacao_indicativa TEXT NOT NULL,
            duracao_min INTEGER DEFAULT 100,
            sinopse TEXT NOT NULL,
            capa_url TEXT DEFAULT NULL,
            imdb_id TEXT DEFAULT NULL,
            imdb_url TEXT DEFAULT NULL,
            nota_imdb REAL DEFAULT 7.5,
            popularidade TEXT NOT NULL DEFAULT 'mais_conhecido',
            criado_em TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS interacoes_usuario (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            filme_id INTEGER NOT NULL,
            assistido INTEGER DEFAULT 0,
            gostou INTEGER DEFAULT 0,
            avaliacao_estrelas INTEGER DEFAULT NULL,
            criado_em TEXT DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TEXT DEFAULT CURRENT_TIMESTAMP,
            UNIQUE (usuario_id, filme_id),
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            FOREIGN KEY (filme_id) REFERENCES filmes(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS historico_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER DEFAULT NULL,
            acao TEXT NOT NULL,
            detalhes TEXT DEFAULT NULL,
            ip_address TEXT DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            criado_em TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
        );
    ");

    // Extrai e executa os INSERTs do schema.sql
    if (file_exists(SCHEMA_SQL_PATH)) {
        $sql = file_get_contents(SCHEMA_SQL_PATH);
        if (preg_match('/INSERT INTO filmes.*?;/is', $sql, $matches)) {
            $pdo->exec($matches[0]);
        }
    }
}

// Inicializa a instância global de conexão PDO
$pdo = getDBConnection();
