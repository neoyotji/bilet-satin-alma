<?php
    session_start();

    ini_set('display_errors', 0);
    error_reporting(0);

    $db_path = getenv('DATABASE_PATH') ?: __DIR__ . '/bilet.db';
    try {
        $db = new PDO("sqlite:$db_path");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Veritabanı bağlantı hatası: " . $e->getMessage());
    }
?>