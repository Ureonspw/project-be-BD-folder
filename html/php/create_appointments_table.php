<?php
require_once __DIR__ . '/../../config/database.php';

try {
    // Vérifier si la table appointments existe déjà
    $check_table_query = "SHOW TABLES LIKE 'appointments'";
    $check_table_stmt = $conn->prepare($check_table_query);
    $check_table_stmt->execute();
    
    if ($check_table_stmt->rowCount() === 0) {
        // Créer la table appointments
        $create_table_query = "CREATE TABLE `appointments` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `doctor_id` int(11) NOT NULL,
            `date_rdv` datetime NOT NULL,
            `motif` text DEFAULT NULL,
            `statut` enum('en_attente', 'confirme', 'annule', 'termine') NOT NULL DEFAULT 'en_attente',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `doctor_id` (`doctor_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        
        $create_table_stmt = $conn->prepare($create_table_query);
        $create_table_stmt->execute();
        
        echo "Table 'appointments' créée avec succès.";
    } else {
        echo "La table 'appointments' existe déjà.";
    }
    
    // Vérifier si la table medical_records existe déjà
    $check_medical_records_query = "SHOW TABLES LIKE 'medical_records'";
    $check_medical_records_stmt = $conn->prepare($check_medical_records_query);
    $check_medical_records_stmt->execute();
    
    if ($check_medical_records_stmt->rowCount() === 0) {
        // Créer la table medical_records
        $create_medical_records_query = "CREATE TABLE `medical_records` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `doctor_id` int(11) NOT NULL,
            `appointment_id` int(11) NOT NULL,
            `diagnostic` text DEFAULT NULL,
            `prescription` text DEFAULT NULL,
            `notes` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `doctor_id` (`doctor_id`),
            KEY `appointment_id` (`appointment_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        
        $create_medical_records_stmt = $conn->prepare($create_medical_records_query);
        $create_medical_records_stmt->execute();
        
        echo "Table 'medical_records' créée avec succès.";
    } else {
        echo "La table 'medical_records' existe déjà.";
    }
} catch (PDOException $e) {
    echo "Erreur lors de la création des tables: " . $e->getMessage();
}
?> 