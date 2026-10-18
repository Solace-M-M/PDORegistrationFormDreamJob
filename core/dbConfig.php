<?php

// ---------------------------------------------------------------
// dbConfig.php - database connection + session (loaded by every page)
// ---------------------------------------------------------------

// Connection details for the local XAMPP MySQL/MariaDB server
$host     = "localhost";
$user     = "root";                    // default XAMPP username
$password = "";                        // default XAMPP password is empty
$dbname   = "game_dev_applications";   // database created by sql/schema.sql

// DSN: driver, host, database and character set (utf8mb4 keeps special characters intact)
$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

// Create the PDO connection used by every query in the project
$pdo = new PDO($dsn, $user, $password);

// Throw exceptions on SQL errors so try/catch blocks can handle them
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Return rows as associative arrays only (no duplicate numeric keys)
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Make date_added and CURDATE() use UTC+8
$pdo->exec("SET time_zone = '+8:00';");

// Sessions store the one-time success/error messages and the CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
