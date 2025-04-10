<?php
require_once __DIR__ . '/../../config/database.php';

// Vérifier si l'utilisateur est connecté en tant qu'administrateur
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo "Accès non autorisé. Vous devez être connecté en tant qu'administrateur.";
    exit;
}

try {
    // Récupérer un docteur existant
    $doctor_query = "SELECT id FROM doctors LIMIT 1";
    $doctor_stmt = $conn->prepare($doctor_query);
    $doctor_stmt->execute();
    
    if ($doctor_stmt->rowCount() === 0) {
        echo "Aucun docteur trouvé dans la base de données. Veuillez d'abord créer un compte docteur.";
        exit;
    }
    
    $doctor = $doctor_stmt->fetch(PDO::FETCH_ASSOC);
    $doctor_id = $doctor['id'];
    
    // Récupérer des utilisateurs existants
    $users_query = "SELECT id FROM users LIMIT 5";
    $users_stmt = $conn->prepare($users_query);
    $users_stmt->execute();
    
    if ($users_stmt->rowCount() === 0) {
        echo "Aucun utilisateur trouvé dans la base de données. Veuillez d'abord créer des comptes utilisateurs.";
        exit;
    }
    
    $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Créer des rendez-vous pour les 30 prochains jours
    $today = new DateTime();
    $appointments_created = 0;
    
    foreach ($users as $user) {
        $user_id = $user['id'];
        
        // Créer 1-3 rendez-vous par utilisateur
        $num_appointments = rand(1, 3);
        
        for ($i = 0; $i < $num_appointments; $i++) {
            // Date aléatoire dans les 30 prochains jours
            $days_to_add = rand(1, 30);
            $hours_to_add = rand(9, 17); // Heures de consultation entre 9h et 17h
            $minutes_to_add = rand(0, 3) * 15; // Minutes en multiples de 15
            
            $appointment_date = clone $today;
            $appointment_date->modify("+$days_to_add days");
            $appointment_date->setTime($hours_to_add, $minutes_to_add, 0);
            
            // Statut aléatoire
            $statuses = ['en_attente', 'confirme', 'termine'];
            $status = $statuses[array_rand($statuses)];
            
            // Motif de consultation
            $motifs = [
                'Consultation de routine',
                'Suivi de traitement',
                'Douleurs chroniques',
                'Problème respiratoire',
                'Examen annuel',
                'Vaccination',
                'Conseil médical'
            ];
            $motif = $motifs[array_rand($motifs)];
            
            // Insérer le rendez-vous
            $insert_query = "INSERT INTO appointments (user_id, doctor_id, date_rdv, motif, statut) 
                            VALUES (:user_id, :doctor_id, :date_rdv, :motif, :statut)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->execute([
                'user_id' => $user_id,
                'doctor_id' => $doctor_id,
                'date_rdv' => $appointment_date->format('Y-m-d H:i:s'),
                'motif' => $motif,
                'statut' => $status
            ]);
            
            $appointments_created++;
            
            // Si le rendez-vous est terminé, créer un dossier médical
            if ($status === 'termine') {
                $appointment_id = $conn->lastInsertId();
                
                // Diagnostic et prescription aléatoires
                $diagnostics = [
                    'Grippe saisonnière',
                    'Hypertension artérielle',
                    'Diabète de type 2',
                    'Allergie alimentaire',
                    'Anxiété',
                    'Dépression',
                    'Arthrite',
                    'Migraine'
                ];
                $diagnostic = $diagnostics[array_rand($diagnostics)];
                
                $prescriptions = [
                    'Paracétamol 1000mg, 3 fois par jour pendant 5 jours',
                    'Amoxicilline 500mg, 2 fois par jour pendant 7 jours',
                    'Lisinopril 10mg, 1 fois par jour',
                    'Metformine 850mg, 2 fois par jour',
                    'Cetirizine 10mg, 1 fois par jour',
                    'Sertraline 50mg, 1 fois par jour',
                    'Ibuprofène 400mg, 3 fois par jour pendant 3 jours',
                    'Sumatriptan 50mg, 1 comprimé en cas de crise'
                ];
                $prescription = $prescriptions[array_rand($prescriptions)];
                
                $notes = [
                    'Revoir dans 3 mois',
                    'Contrôle de la tension artérielle à domicile recommandé',
                    'Suivre un régime pauvre en sel',
                    'Éviter les aliments allergènes',
                    'Pratiquer des exercices de respiration',
                    'Maintenir une activité physique régulière',
                    'Éviter les situations stressantes',
                    'Tenir un journal des crises'
                ];
                $note = $notes[array_rand($notes)];
                
                // Insérer le dossier médical
                $insert_record_query = "INSERT INTO medical_records (user_id, doctor_id, appointment_id, diagnostic, prescription, notes) 
                                      VALUES (:user_id, :doctor_id, :appointment_id, :diagnostic, :prescription, :notes)";
                $insert_record_stmt = $conn->prepare($insert_record_query);
                $insert_record_stmt->execute([
                    'user_id' => $user_id,
                    'doctor_id' => $doctor_id,
                    'appointment_id' => $appointment_id,
                    'diagnostic' => $diagnostic,
                    'prescription' => $prescription,
                    'notes' => $note
                ]);
            }
        }
    }
    
    echo "Création réussie de $appointments_created rendez-vous avec des dossiers médicaux pour les rendez-vous terminés.";
} catch (PDOException $e) {
    echo "Erreur lors de la création des rendez-vous: " . $e->getMessage();
}
?> 