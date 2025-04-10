<?php
session_start();

// Vérifier si l'utilisateur est connecté en tant que docteur
if (!isset($_SESSION['doctor_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté en tant que docteur pour effectuer cette action']);
    exit();
}

// Connexion à la base de données
require_once '../../config/database.php';

// Récupérer l'action à effectuer
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'accepter':
            if (!isset($_POST['don_id'])) {
                throw new Exception('ID du don manquant');
            }

            // Vérifier si le don existe et est en attente
            $stmt = $conn->prepare("SELECT id FROM blood_donations WHERE id = :don_id AND statut = 'en_attente'");
            $stmt->execute([':don_id' => $_POST['don_id']]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Don non trouvé ou ne peut pas être accepté');
            }

            // Mettre à jour le statut du don
            $stmt = $conn->prepare("UPDATE blood_donations SET statut = 'confirme' WHERE id = :don_id");
            $stmt->execute([':don_id' => $_POST['don_id']]);

            echo json_encode(['success' => true, 'message' => 'Don accepté avec succès']);
            break;

        case 'refuser':
            if (!isset($_POST['don_id'])) {
                throw new Exception('ID du don manquant');
            }

            // Vérifier si le don existe et est en attente
            $stmt = $conn->prepare("SELECT id FROM blood_donations WHERE id = :don_id AND statut = 'en_attente'");
            $stmt->execute([':don_id' => $_POST['don_id']]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Don non trouvé ou ne peut pas être refusé');
            }

            // Mettre à jour le statut du don
            $stmt = $conn->prepare("UPDATE blood_donations SET statut = 'annule' WHERE id = :don_id");
            $stmt->execute([':don_id' => $_POST['don_id']]);

            echo json_encode(['success' => true, 'message' => 'Don refusé avec succès']);
            break;

        case 'terminer':
            if (!isset($_POST['don_id'])) {
                throw new Exception('ID du don manquant');
            }

            // Vérifier si le don existe et est confirmé
            $stmt = $conn->prepare("SELECT id FROM blood_donations WHERE id = :don_id AND statut = 'confirme'");
            $stmt->execute([':don_id' => $_POST['don_id']]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Don non trouvé ou ne peut pas être marqué comme terminé');
            }

            // Mettre à jour le statut du don
            $stmt = $conn->prepare("UPDATE blood_donations SET statut = 'termine' WHERE id = :don_id");
            $stmt->execute([':don_id' => $_POST['don_id']]);

            echo json_encode(['success' => true, 'message' => 'Don marqué comme terminé avec succès']);
            break;

        default:
            throw new Exception('Action non reconnue');
    }
} catch (Exception $e) {
    error_log("Erreur dans traitement_don_sang_doctor.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 