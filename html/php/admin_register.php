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
        $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("Cet email est déjà utilisé.");
        }

        // Préparer et exécuter la requête d'insertion
        $stmt = $conn->prepare("INSERT INTO admin (nom_complet, email, password) VALUES (?, ?, ?)");
        
        if ($stmt->execute([
            $_POST['nom_complet'],
            $_POST['email'],
            password_hash($_POST['password'], PASSWORD_DEFAULT)
        ])) {
            $_SESSION['success_message'] = "Votre compte administrateur a été créé avec succès ! Vous pouvez maintenant vous connecter.";
            header("Location: admin_login.php");
            exit();
        } else {
            throw new Exception("Une erreur est survenue lors de la création du compte. Veuillez réessayer.");
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Une erreur de base de données est survenue. Veuillez réessayer plus tard.";
        error_log("Erreur PDO: " . $e->getMessage());
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Administrateur - MediConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #2D5A77;
            --secondary-color: #4A90E2;
            --accent-color: #67B26F;
            --text-color: #2C3E50;
            --light-bg: #F8FAFC;
        }

        body {
            background: linear-gradient(135deg, #F8FAFC 0%, #E2E8F0 100%);
            color: var(--text-color);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .inscription-container {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .left-panel {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 3rem 2rem;
            height: 100%;
        }

        .left-panel h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .feature-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .feature-item:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .right-panel {
            padding: 3rem 2rem;
            background: white;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .form-header h2 {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .form-section {
            background: var(--light-bg);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .form-control {
            border: 2px solid #E2E8F0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .required-field::after {
            content: "*";
            color: #E53E3E;
            margin-left: 4px;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #E2E8F0;
            border: none;
            color: var(--text-color);
        }

        .btn-secondary:hover {
            background: #CBD5E0;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }

        .alert {
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container inscription-container">
        <div class="row g-0">
            <div class="col-lg-4">
                <div class="left-panel">
                    <h1>MediConnect</h1>
                    <p class="lead mb-4">Votre plateforme de gestion médicale</p>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h5>Gestion sécurisée</h5>
                            <p>Accès sécurisé à la plateforme d'administration</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <div>
                            <h5>Gestion des utilisateurs</h5>
                            <p>Gérez les comptes docteurs et patients</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <h5>Tableau de bord</h5>
                            <p>Suivez l'activité de la plateforme</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="right-panel">
                    <div class="form-header">
                        <h2>Inscription Administrateur</h2>
                        <p>Créez votre compte administrateur pour gérer la plateforme</p>
                    </div>

                    <?php
                    if(isset($_SESSION['error_message'])) {
                        echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                        unset($_SESSION['error_message']);
                    }
                    ?>

                    <form id="inscriptionForm" method="POST" action="admin_register.php">
                        <div class="form-section">
                            <h3><i class="fas fa-user-shield me-2"></i>Informations administrateur</h3>
                            
                            <div class="mb-3">
                                <label for="nom_complet" class="form-label required-field">Nom complet</label>
                                <input type="text" class="form-control" id="nom_complet" name="nom_complet" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label required-field">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label required-field">Mot de passe</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label required-field">Confirmer le mot de passe</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="window.location.href='../../index.php'">Annuler</button>
                                <button type="submit" class="btn btn-primary">Créer le compte</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation du formulaire
        document.getElementById('inscriptionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validation du mot de passe
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Les mots de passe ne correspondent pas',
                    confirmButtonColor: '#2D5A77'
                });
                return;
            }

            // Validation de l'email
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Veuillez entrer une adresse email valide',
                    confirmButtonColor: '#2D5A77'
                });
                return;
            }

            // Si tout est valide, soumettre le formulaire
            Swal.fire({
                title: 'Création du compte',
                text: 'Veuillez patienter...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Soumettre le formulaire
            this.submit();
        });
    </script>
</body>
</html> 