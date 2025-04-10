<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Utilisateur non connecté'
    ]);
    exit;
}

try {
    // Récupérer l'historique des consultations de l'utilisateur
    $stmt = $conn->prepare("
        SELECT a.*, 
                d.nom as doctor_nom, 
                d.prenom as doctor_prenom,
                d.specialite as doctor_specialite,
                d.photo as doctor_photo,
                mr.notes as consultation_notes,
                mr.prescription as consultation_prescription,
                mr.diagnostic as consultation_diagnostic
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        LEFT JOIN medical_records mr ON 
            DATE(a.date_rdv) = DATE(mr.date_consultation) 
            AND a.user_id = mr.user_id 
            AND a.doctor_id = mr.doctor_id
        WHERE a.user_id = :user_id
        AND a.statut = 'termine'
        ORDER BY a.date_rdv DESC
        LIMIT 20
    ");
    
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $consultations = $stmt->fetchAll();

    // Formater les consultations
    $formattedConsultations = array_map(function($consultation) {
        return [
            'id' => $consultation['id'],
            'doctor_name' => 'Dr. ' . $consultation['doctor_prenom'] . ' ' . $consultation['doctor_nom'],
            'specialty' => $consultation['doctor_specialite'],
            'date' => date('d/m/Y', strtotime($consultation['date_rdv'])),
            'time' => date('H:i', strtotime($consultation['date_rdv'])),
            'reason' => $consultation['motif'],
            'notes' => !empty($consultation['consultation_notes']) ? $consultation['consultation_notes'] : 'Aucune note disponible',
            'prescription' => !empty($consultation['consultation_prescription']) ? $consultation['consultation_prescription'] : 'Aucune prescription',
            'diagnostic' => !empty($consultation['consultation_diagnostic']) ? $consultation['consultation_diagnostic'] : 'Aucun diagnostic disponible',
            'doctor_photo' => $consultation['doctor_photo'] ?? '../../assets/images/doctor-default.jpg'
        ];
    }, $consultations);

    echo json_encode([
        'success' => true,
        'consultations' => $formattedConsultations
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération de l\'historique: ' . $e->getMessage()
    ]);
}
?> 