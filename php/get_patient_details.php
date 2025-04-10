<?php
session_start();
require_once '../config/database.php';

// Définir les headers pour la réponse JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['doctor_id'])) {
    error_log("Session doctor_id non défini");
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

// Récupérer les données POST
$raw_data = file_get_contents('php://input');
error_log("Données POST reçues: " . $raw_data);

$data = json_decode($raw_data, true);
if (!$data || !isset($data['patient_id'])) {
    error_log("Données POST invalides ou patient_id manquant");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit();
}

$patient_id = $data['patient_id'];
error_log("Patient ID reçu: " . $patient_id);

try {
    // Vérifier si le patient a déjà eu un rendez-vous avec ce docteur
    $check_query = "SELECT 1 FROM appointments WHERE user_id = :patient_id AND doctor_id = :doctor_id LIMIT 1";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $check_stmt->bindParam(':doctor_id', $_SESSION['doctor_id'], PDO::PARAM_INT);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        error_log("Aucun rendez-vous trouvé pour ce patient avec ce docteur");
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Accès non autorisé à ces informations'
        ]);
        exit();
    }
    
    // Récupérer les informations détaillées du patient
    $query = "SELECT * FROM users WHERE id = :patient_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        error_log("Patient non trouvé dans la base de données");
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Patient non trouvé'
        ]);
        exit();
    }
    
    $patient = $stmt->fetch();
    
    // Formater les dates
    if ($patient['date_naissance']) {
        $patient['date_naissance'] = date('Y-m-d', strtotime($patient['date_naissance']));
    }
    
    echo json_encode([
        'success' => true,
        'patient' => $patient
    ]);
    
} catch (PDOException $e) {
    error_log("Erreur dans get_patient_details.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des détails du patient: ' . $e->getMessage()
    ]);
}
?> 