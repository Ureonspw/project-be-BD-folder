<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Confirmée - MediConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .confirmation-container {
            max-width: 800px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding: 3rem;
            text-align: center;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: var(--accent-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: white;
            font-size: 3rem;
        }

        h1 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            color: #64748B;
        }

        .btn {
            padding: 0.75rem 2rem;
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

        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1>Inscription Confirmée !</h1>
        
        <?php if(isset($_SESSION['success_message'])): ?>
            <p><?php echo $_SESSION['success_message']; ?></p>
            <?php unset($_SESSION['success_message']); ?>
        <?php else: ?>
            <p>Votre compte docteur a été créé avec succès ! Vous pouvez maintenant vous connecter à votre espace personnel.</p>
        <?php endif; ?>
        
        <div class="btn-group">
            <a href="../../index.php" class="btn btn-secondary">
                <i class="fas fa-home me-2"></i> Retour à l'accueil
            </a>
            <a href="connexion_docteur.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt me-2"></i> Se connecter
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 