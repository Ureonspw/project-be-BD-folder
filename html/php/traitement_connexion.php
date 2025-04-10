<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Vérifier si l'email existe
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_telephone'] = $user['telephone'];
            $_SESSION['user_sexe'] = $user['sexe'];
            $_SESSION['success_message'] = "Connexion réussie !";
            
            // Debug: Afficher les informations stockées dans la session
            // echo "<pre>"; print_r($_SESSION); echo "</pre>"; exit;
            
            // Redirection vers la page principale après connexion
            header("Location: mainpagecon.php");
            exit();
        } else {
            throw new Exception("Email ou mot de passe incorrect.");
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: ../index.php");
        exit();
    }
}
?> 