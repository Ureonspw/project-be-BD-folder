<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour effectuer cette action']);
    exit();
}

// Connexion à la base de données
require_once '../../config/database.php';

// Récupérer l'action à effectuer
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'ajouter':
            // Vérifier les données requises
            if (!isset($_POST['groupe_sanguin']) || !isset($_POST['lieu']) || !isset($_POST['date']) || !isset($_POST['heure'])) {
                throw new Exception('Tous les champs sont requis');
            }

            // Valider le groupe sanguin
            $valid_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
            if (!in_array($_POST['groupe_sanguin'], $valid_groups)) {
                throw new Exception('Groupe sanguin invalide');
            }

            // Valider la date
            $date = DateTime::createFromFormat('Y-m-d', $_POST['date']);
            if (!$date || $date->format('Y-m-d') !== $_POST['date']) {
                throw new Exception('Date invalide');
            }

            // Valider l'heure
            $heure = DateTime::createFromFormat('H:i', $_POST['heure']);
            if (!$heure || $heure->format('H:i') !== $_POST['heure']) {
                throw new Exception('Heure invalide');
            }

            // Combiner la date et l'heure
            $date_don = $date->format('Y-m-d') . ' ' . $heure->format('H:i:00');

            // Insérer le don de sang
            $stmt = $conn->prepare("
                INSERT INTO blood_donations (user_id, groupe_sanguin, lieu, date_don, statut)
                VALUES (:user_id, :groupe_sanguin, :lieu, :date_don, 'en_attente')
            ");

            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':groupe_sanguin' => $_POST['groupe_sanguin'],
                ':lieu' => $_POST['lieu'],
                ':date_don' => $date_don
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Votre don de sang a été programmé avec succès'
            ]);
            break;

        case 'annuler':
            // Vérifier que le don_id est fourni
            if (!isset($_POST['don_id'])) {
                throw new Exception('ID du don manquant');
            }

            // Vérifier que le don appartient à l'utilisateur
            $stmt = $conn->prepare("
                SELECT id FROM blood_donations 
                WHERE id = :don_id AND user_id = :user_id AND statut = 'en_attente'
            ");
            $stmt->execute([
                ':don_id' => $_POST['don_id'],
                ':user_id' => $_SESSION['user_id']
            ]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Don de sang non trouvé ou non annulable');
            }

            // Annuler le don
            $stmt = $conn->prepare("
                UPDATE blood_donations 
                SET statut = 'annule' 
                WHERE id = :don_id
            ");
            $stmt->execute([':don_id' => $_POST['don_id']]);

            echo json_encode([
                'success' => true,
                'message' => 'Votre don de sang a été annulé avec succès'
            ]);
            break;

        case 'reprogrammer':
            // Vérifier que le don_id est fourni
            if (!isset($_POST['don_id']) || !isset($_POST['date']) || !isset($_POST['heure'])) {
                throw new Exception('Données manquantes pour la reprogrammation');
            }

            // Vérifier que le don appartient à l'utilisateur
            $stmt = $conn->prepare("
                SELECT id FROM blood_donations 
                WHERE id = :don_id AND user_id = :user_id AND statut = 'en_attente'
            ");
            $stmt->execute([
                ':don_id' => $_POST['don_id'],
                ':user_id' => $_SESSION['user_id']
            ]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Don de sang non trouvé ou non reprogrammable');
            }

            // Valider la date
            $date = DateTime::createFromFormat('Y-m-d', $_POST['date']);
            if (!$date || $date->format('Y-m-d') !== $_POST['date']) {
                throw new Exception('Date invalide');
            }

            // Valider l'heure
            $heure = DateTime::createFromFormat('H:i', $_POST['heure']);
            if (!$heure || $heure->format('H:i') !== $_POST['heure']) {
                throw new Exception('Heure invalide');
            }

            // Combiner la date et l'heure
            $date_don = $date->format('Y-m-d') . ' ' . $heure->format('H:i:00');

            // Mettre à jour le don
            $stmt = $conn->prepare("
                UPDATE blood_donations 
                SET date_don = :date_don 
                WHERE id = :don_id
            ");
            $stmt->execute([
                ':date_don' => $date_don,
                ':don_id' => $_POST['don_id']
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Votre don de sang a été reprogrammé avec succès'
            ]);
            break;

        default:
            throw new Exception('Action non reconnue');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 