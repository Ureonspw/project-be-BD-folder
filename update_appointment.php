<?php
session_start();
require_once 'config/database.php';

// Vérification si l'utilisateur est connecté
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    try {
        $appointment_id = $_GET['id'];
        $user_id = $_SESSION['user_id'];
        $doctor_id = $_POST['doctor_id'];
        $date_rdv = $_POST['date_rdv'];
        $motif = $_POST['motif'];

        // Vérifier que le rendez-vous appartient bien à l'utilisateur
        $check_sql = "SELECT * FROM appointments WHERE id = :id AND user_id = :user_id";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':id', $appointment_id);
        $check_stmt->bindParam(':user_id', $user_id);
        $check_stmt->execute();

        if($check_stmt->rowCount() > 0) {
            // Mettre à jour le rendez-vous
            $sql = "UPDATE appointments 
                    SET doctor_id = :doctor_id, 
                        date_rdv = :date_rdv, 
                        motif = :motif 
                    WHERE id = :id AND user_id = :user_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $appointment_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':doctor_id', $doctor_id);
            $stmt->bindParam(':date_rdv', $date_rdv);
            $stmt->bindParam(':motif', $motif);

            if($stmt->execute()) {
                $_SESSION['success_message'] = "Rendez-vous mis à jour avec succès";
                header("Location: appointments.php");
                exit();
            } else {
                throw new Exception("Erreur lors de la mise à jour du rendez-vous");
            }
        } else {
            throw new Exception("Rendez-vous non trouvé ou non autorisé");
        }
    } catch(Exception $e) {
        $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
        header("Location: appointments.php");
        exit();
    }
} else {
    header("Location: appointments.php");
    exit();
}
?> 