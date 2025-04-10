<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Traitement de la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
    try {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        
        // Vérifier si l'email existe dans la table admin
        $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            // Connexion réussie
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['nom_complet'];
            $_SESSION['success_message'] = "Connexion réussie !";
            
            // Redirection vers la page principale de l'admin après connexion
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Email ou mot de passe incorrect";
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Erreur de connexion à la base de données";
        error_log("Erreur PDO: " . $e->getMessage());
    } catch(Exception $e) {
        $_SESSION['error_message'] = "Une erreur est survenue";
        error_log("Erreur: " . $e->getMessage());
    }
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylelog.css">
    <title>Connexion Admin - MediConnect</title>
</head>
<body>

    <form class="form-structor" method="POST" action="">
        
        <div class="signup">
            <h2 class="form-title" id="signup"><span>ou</span>Bienvenue Admin</h2>
            <?php if(isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger" style="color: red; margin-bottom: 15px; padding: 10px; border-radius: 5px; background-color: #ffe6e6;">
                    <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="form-holder">
                <input required type="email" id="email" name="email" class="input" placeholder="Email" />
                <input required type="password" id="password" name="password" class="input" placeholder="Mot de passe" />
            </div>
            <button type="submit" class="submit-btn">Se connecter</button>
        </div>
        <div class="login slide-up">
            <div class="center">
                <h2 class="form-title" id="login"><span>ou</span>Informations</h2>
                <div class="form-holder2">
                    Cette partie est strictement réservée aux docteurs autorisés et ajoutés par l'administrateur afin de pouvoir gérer leurs rendez-vous et leurs patients. 
                    Si ce n'est pas votre cas, je vous invite à retourner au menu principal via ce bouton ▾
                    <br>
                </div>
                <button class="submit-btn" onclick="window.location.href='../../index.php'">Accueil</button>
            </div>
        </div>
    </form>
    <script src="mainlog.js"></script>
</body>
</html>