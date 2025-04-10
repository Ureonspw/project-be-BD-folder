<?php
require_once __DIR__ . '/../../config/database.php';

// Vérifier si l'utilisateur est connecté en tant que docteur
session_start();
if (!isset($_SESSION['doctor_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Accès non autorisé. Vous devez être connecté en tant que docteur.'
    ]);
    exit;
}

// Vérifier si la date est fournie
if (!isset($_GET['date']) || empty($_GET['date'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Date non fournie.'
    ]);
    exit;
}

$date = $_GET['date'];
$doctor_id = $_SESSION['doctor_id'];

try {
    // Construire la requête pour récupérer les rendez-vous pour la date spécifiée
    $query = "SELECT a.*, u.prenom as patient_prenom, u.nom as patient_nom, u.telephone as patient_telephone, u.email as patient_email 
              FROM appointments a 
              JOIN users u ON a.user_id = u.id 
              WHERE a.doctor_id = :doctor_id 
              AND DATE(a.date_rdv) = :date 
              AND a.statut = 'confirme'
              ORDER BY a.date_rdv ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([
        'doctor_id' => $doctor_id,
        'date' => $date
    ]);
    
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'appointments' => $appointments
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des rendez-vous: ' . $e->getMessage()
    ]);
}
?> 