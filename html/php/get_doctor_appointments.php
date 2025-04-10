<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Code de débogage pour vérifier la session
error_log("Session data: " . print_r($_SESSION, true));

// Vérifier si le docteur est connecté
if (!isset($_SESSION['doctor_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté en tant que docteur pour accéder à cette fonctionnalité',
        'debug' => 'Session doctor_id non trouvé'
    ]);
    exit;
}

header('Content-Type: application/json');

try {
    $doctor_id = $_SESSION['doctor_id'];
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    
    // Code de débogage pour vérifier les paramètres
    error_log("Doctor ID: " . $doctor_id);
    error_log("Status: " . $status);
    
    // Construire la requête en fonction du statut demandé
    $query = "SELECT a.*, u.nom as patient_nom, u.prenom as patient_prenom, u.email as patient_email, u.telephone as patient_telephone 
              FROM appointments a 
              JOIN users u ON a.user_id = u.id 
              WHERE a.doctor_id = :doctor_id";
    
    $params = ['doctor_id' => $doctor_id];
    
    if ($status) {
        $query .= " AND a.statut = :status";
        $params['status'] = $status;
    }
    
    $query .= " ORDER BY a.date_rdv ASC";
    
    // Code de débogage pour vérifier la requête SQL
    error_log("SQL Query: " . $query);
    error_log("Parameters: " . print_r($params, true));
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Code de débogage pour vérifier les résultats
    error_log("Nombre de rendez-vous trouvés: " . count($appointments));
    
    echo json_encode([
        'success' => true,
        'appointments' => $appointments,
        'debug' => [
            'doctor_id' => $doctor_id,
            'status' => $status,
            'count' => count($appointments)
        ]
    ]);
} catch (PDOException $e) {
    error_log("Erreur PDO: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des rendez-vous: ' . $e->getMessage()
    ]);
}
?> 