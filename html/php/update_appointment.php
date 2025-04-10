<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Vérifier si le docteur est connecté
if (!isset($_SESSION['doctor_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté en tant que docteur pour accéder à cette fonctionnalité'
    ]);
    exit;
}

header('Content-Type: application/json');

// Vérifier si la méthode est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

// Vérifier les données requises
if (!isset($_POST['appointment_id']) || !isset($_POST['status'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Paramètres manquants'
    ]);
    exit;
}

$appointment_id = intval($_POST['appointment_id']);
$status = $_POST['status'];
$doctor_id = $_SESSION['doctor_id'];

// Vérifier si le statut est valide
$valid_statuses = ['en_attente', 'confirme', 'annule', 'termine'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode([
        'success' => false,
        'message' => 'Statut invalide'
    ]);
    exit;
}

try {
    // Vérifier que le rendez-vous appartient au docteur connecté
    $check_query = "SELECT id FROM appointments WHERE id = :id AND doctor_id = :doctor_id";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute([
        'id' => $appointment_id,
        'doctor_id' => $doctor_id
    ]);
    
    if ($check_stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Rendez-vous non trouvé ou vous n\'êtes pas autorisé à le modifier'
        ]);
        exit;
    }
    
    // Mettre à jour le statut du rendez-vous
    $update_query = "UPDATE appointments SET statut = :status WHERE id = :id";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->execute([
        'id' => $appointment_id,
        'status' => $status
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Statut du rendez-vous mis à jour avec succès'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la mise à jour du statut du rendez-vous: ' . $e->getMessage()
    ]);
}
?> 