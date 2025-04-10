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
if (!$data || !isset($data['doctor_id'])) {
    error_log("Données POST invalides ou doctor_id manquant");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit();
}

$doctor_id = $data['doctor_id'];
error_log("Doctor ID reçu: " . $doctor_id);
error_log("Doctor ID en session: " . $_SESSION['doctor_id']);

// Vérifier que l'ID du docteur correspond à celui de la session
if ($doctor_id != $_SESSION['doctor_id']) {
    error_log("ID docteur ne correspond pas à la session");
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

try {
    // Récupérer les patients qui ont pris rendez-vous avec ce docteur
    $query = "SELECT DISTINCT u.*, 
              (SELECT MIN(date_rdv) FROM appointments 
               WHERE user_id = u.id AND doctor_id = :doctor_id1 AND date_rdv > NOW() 
               AND statut != 'annule') as next_appointment
              FROM users u
              JOIN appointments a ON u.id = a.user_id
              WHERE a.doctor_id = :doctor_id2
              GROUP BY u.id
              ORDER BY next_appointment ASC";
    
    error_log("Requête SQL: " . $query);
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':doctor_id1', $doctor_id, PDO::PARAM_INT);
    $stmt->bindParam(':doctor_id2', $doctor_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $patients = $stmt->fetchAll();
    
    // Formater les dates
    foreach ($patients as &$patient) {
        if ($patient['next_appointment']) {
            $patient['next_appointment'] = date('Y-m-d H:i:s', strtotime($patient['next_appointment']));
        }
        if ($patient['date_naissance']) {
            $patient['date_naissance'] = date('Y-m-d', strtotime($patient['date_naissance']));
        }
    }
    
    error_log("Nombre de patients trouvés: " . count($patients));
    
    echo json_encode([
        'success' => true,
        'patients' => $patients
    ]);
    
} catch (PDOException $e) {
    error_log("Erreur dans get_patients.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des patients: ' . $e->getMessage()
    ]);
}
?> 