<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['doctor_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_history':
        try {
            // Récupérer la date de filtrage si elle existe
            $date = $_GET['date'] ?? '';
            
            // Construire la requête SQL
            $sql = "SELECT bd.*, u.nom, u.prenom 
                    FROM blood_donations bd 
                    JOIN users u ON bd.user_id = u.id 
                    ORDER BY bd.date_don DESC";
            
            // Ajouter le filtre de date si spécifié
            if ($date) {
                $sql = "SELECT bd.*, u.nom, u.prenom 
                        FROM blood_donations bd 
                        JOIN users u ON bd.user_id = u.id 
                        WHERE DATE(bd.date_don) = :date 
                        ORDER BY bd.date_don DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':date', $date);
            } else {
                $stmt = $conn->prepare($sql);
            }
            
            $stmt->execute();
            $donations = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $donations[] = [
                    'id' => $row['id'],
                    'donor_name' => $row['prenom'] . ' ' . $row['nom'],
                    'blood_type' => $row['groupe_sanguin'],
                    'location' => $row['lieu'],
                    'date' => $row['date_don'],
                    'status' => $row['statut']
                ];
            }
            
            header('Content-Type: application/json');
            echo json_encode($donations);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
        }
        break;
    case 'get_stats':
        getDonationStats();
        break;
    case 'get_pending_requests':
        getPendingRequests();
        break;
    case 'update_status':
        updateDonationStatus();
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Action non valide']);
        break;
}

function getDonationStats() {
    global $conn;
    
    // Récupérer les statistiques
    $stats = [
        'pending_requests' => 0,
        'completed_donations' => 0,
        'total_donations' => 0,
        'blood_types' => []
    ];
    
    try {
        // Compter les demandes en attente
        $query = "SELECT COUNT(*) as count FROM blood_donations WHERE statut = 'en_attente'";
        $stmt = $conn->query($query);
        if ($stmt) {
            $stats['pending_requests'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        }
        
        // Compter les dons complétés
        $query = "SELECT COUNT(*) as count FROM blood_donations WHERE statut = 'termine'";
        $stmt = $conn->query($query);
        if ($stmt) {
            $stats['completed_donations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        }
        
        // Compter le total des dons
        $query = "SELECT COUNT(*) as count FROM blood_donations";
        $stmt = $conn->query($query);
        if ($stmt) {
            $stats['total_donations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        }
        
        // Récupérer les statistiques par groupe sanguin
        $query = "SELECT groupe_sanguin, COUNT(*) as count FROM blood_donations GROUP BY groupe_sanguin";
        $stmt = $conn->query($query);
        if ($stmt) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stats['blood_types'][$row['groupe_sanguin']] = $row['count'];
            }
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
        exit();
    }
    
    header('Content-Type: application/json');
    echo json_encode($stats);
}

function getPendingRequests() {
    global $conn;
    
    try {
        $query = "SELECT bd.*, u.nom, u.prenom 
                  FROM blood_donations bd 
                  JOIN users u ON bd.user_id = u.id 
                  WHERE bd.statut = 'en_attente' 
                  ORDER BY bd.date_don ASC";
        
        $stmt = $conn->query($query);
        $requests = [];
        
        if ($stmt) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $requests[] = [
                    'id' => $row['id'],
                    'donor_name' => $row['prenom'] . ' ' . $row['nom'],
                    'blood_type' => $row['groupe_sanguin'],
                    'location' => $row['lieu'],
                    'requested_date' => $row['date_don'],
                    'status' => $row['statut']
                ];
            }
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
        exit();
    }
    
    header('Content-Type: application/json');
    echo json_encode($requests);
}

function updateDonationStatus() {
    global $conn;
    
    $donation_id = $_POST['donation_id'] ?? null;
    $new_status = $_POST['status'] ?? null;
    
    if (!$donation_id || !$new_status) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Paramètres manquants']);
        exit();
    }
    
    $valid_statuses = ['en_attente', 'confirme', 'annule', 'termine'];
    if (!in_array($new_status, $valid_statuses)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Statut non valide']);
        exit();
    }
    
    try {
        $stmt = $conn->prepare("UPDATE blood_donations SET statut = ? WHERE id = ?");
        $stmt->execute([$new_status, $donation_id]);
        
        if ($stmt->rowCount() > 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erreur lors de la mise à jour']);
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
    }
}
?> 