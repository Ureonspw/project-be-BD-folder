<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Vérifier si les mots de passe correspondent
        if ($_POST['password'] !== $_POST['confirm_password']) {
            throw new Exception("Les mots de passe ne correspondent pas.");
        }

        // Vérifier si l'email existe déjà
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("Cet email est déjà utilisé.");
        }

        // Gérer les champs conditionnels
        $allergies = isset($_POST['allergies_check']) && $_POST['allergies_check'] === 'oui' ? $_POST['allergies_details'] : '';
        $maladies = isset($_POST['maladies_check']) && $_POST['maladies_check'] === 'oui' ? $_POST['maladies_details'] : '';

        // Préparer et exécuter la requête d'insertion
        $stmt = $conn->prepare("INSERT INTO users (
            prenom, nom, email, telephone, password, sexe, date_naissance, 
            adresse, code_postal, ville, pays, groupe_sanguin, poids, taille, 
            fumeur, allergies, maladies_chroniques, medicaments, antecedents, 
            urgence_nom, urgence_relation, urgence_telephone, urgence_email, 
            medecin_traitant, commentaires, date_creation
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
        )");
        
        if ($stmt->execute([
            $_POST['prenom'],
            $_POST['nom'],
            $_POST['email'],
            $_POST['telephone'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $_POST['sexe'],
            $_POST['date_naissance'],
            $_POST['adresse'],
            $_POST['code_postal'],
            $_POST['ville'],
            $_POST['pays'],
            $_POST['groupe_sanguin'],
            $_POST['poids'],
            $_POST['taille'],
            $_POST['fumeur'],
            $allergies,
            $maladies,
            $_POST['medicaments'],
            $_POST['antecedents'],
            $_POST['urgence_nom'],
            $_POST['urgence_relation'],
            $_POST['urgence_telephone'],
            $_POST['urgence_email'],
            $_POST['medecin_traitant'],
            $_POST['commentaires']
        ])) {
            $_SESSION['success_message'] = "Votre compte a été créé avec succès !";
            header("Location: confirmation_inscription.php");
            exit();
        } else {
            throw new Exception("Une erreur est survenue lors de la création du compte.");
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: creationcompteutilisateur.php");
        exit();
    }
} else {
    // Si quelqu'un accède directement à ce fichier sans POST
    header("Location: creationcompteutilisateur.php");
    exit();
}
?> 