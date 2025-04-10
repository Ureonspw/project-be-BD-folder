<?php
try {
    // Pour Mac OS X avec XAMPP, utilisez le socket Unix
    $host = 'localhost';
    $socket = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
    $dbname = 'mediconnect_database';
    $username = 'root';
    $password = '';
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];

    // Utilisation du socket Unix pour la connexion
    $conn = new PDO(
        "mysql:unix_socket=$socket;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        $options
    );

} catch(PDOException $e) {
    // Gestion des erreurs
    die("Erreur de connexion : " . $e->getMessage());
}
?> 