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
    // Récupérer les notifications de l'utilisateur
    $stmt = $conn->prepare("
        SELECT n.*, 
               CASE 
                   WHEN n.type = 'appointment' THEN a.date_rdv
                   ELSE n.created_at
               END as event_date
        FROM notifications n
        LEFT JOIN appointments a ON n.appointment_id = a.id
        WHERE n.user_id = :user_id
        ORDER BY n.created_at DESC
        LIMIT 10
    ");
    
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $notifications = $stmt->fetchAll();

    // Formater les notifications
    $formattedNotifications = array_map(function($notification) {
        $message = '';
        switch($notification['type']) {
            case 'appointment':
                $message = "Rappel: RDV avec " . $notification['doctor_name'] . " le " . 
                          date('d/m/Y à H:i', strtotime($notification['event_date']));
                break;
            case 'reminder':
                $message = $notification['message'];
                break;
            case 'update':
                $message = "Mise à jour: " . $notification['message'];
                break;
            default:
                $message = $notification['message'];
        }

        return [
            'id' => $notification['id'],
            'type' => $notification['type'],
            'message' => $message,
            'read' => (bool)$notification['read'],
            'created_at' => $notification['created_at']
        ];
    }, $notifications);

    echo json_encode([
        'success' => true,
        'notifications' => $formattedNotifications
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des notifications: ' . $e->getMessage()
    ]);
}
?> 