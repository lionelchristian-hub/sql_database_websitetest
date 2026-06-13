<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_lat_hrd');
define('BASE_URL', 'http://localhost/proyek_kepegawaian/');

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../classes/' . $class . '.php';
    if (file_exists($file)) require_once $file;
});

require_once __DIR__ . '/../fpdf/fpdf.php';
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi Gagal: " . $e->getMessage());
}
?>