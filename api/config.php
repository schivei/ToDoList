<?php

/**
 * Database configuration.
 * Edit the constants below to match your environment before running the app.
 */

define('DB_HOST',     getenv('DB_HOST')     ?: 'localhost');
define('DB_PORT',     getenv('DB_PORT') !== false ? (int) getenv('DB_PORT') : 3306);
define('DB_NAME',     getenv('DB_NAME')     ?: 'todolist');
define('DB_USER',     getenv('DB_USER')     ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
define('DB_CHARSET',  'utf8mb4');

/**
 * Returns a PDO connection to the configured MySQL database.
 * Throws PDOException on failure.
 */
function getConnection(): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, DB_USER, DB_PASSWORD, $options);
}
