<?php
require_once __DIR__ . '/../../config/database.php';

try {
    // Vérifier si la table blood_donations existe déjà
    $check_table_query = "SHOW TABLES LIKE 'blood_donations'";
    $check_table_stmt = $conn->prepare($check_table_query);
    $check_table_stmt->execute();
    
    if ($check_table_stmt->rowCount() === 0) {
        // Créer la table blood_donations
        $create_table_query = "CREATE TABLE `blood_donations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `groupe_sanguin` enum('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-') NOT NULL,
            `lieu` varchar(255) NOT NULL,
            `date_don` datetime NOT NULL,
            `statut` enum('en_attente', 'confirme', 'annule', 'termine') NOT NULL DEFAULT 'en_attente',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        
        $create_table_stmt = $conn->prepare($create_table_query);
        $create_table_stmt->execute();
        
        echo "Table 'blood_donations' créée avec succès.";
    } else {
        echo "La table 'blood_donations' existe déjà.";
    }
} catch (PDOException $e) {
    echo "Erreur lors de la création de la table: " . $e->getMessage();
}
?> 