<?php
session_start();

// Vérifier si l'utilisateur vient bien de l'inscription
if (!isset($_SESSION['success_message'])) {
    header("Location: index.php");
    exit();
}

$message = $_SESSION['success_message'];
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation d'inscription - MediConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2D5A77;
            --secondary-color: #4A90E2;
            --accent-color: #67B26F;
        }

        body {
            background: linear-gradient(135deg, #F8FAFC 0%, #E2E8F0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .confirmation-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            text-align: center;
            max-width: 600px;
            width: 90%;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: var(--accent-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
        }

        .success-icon i {
            font-size: 2.5rem;
            color: white;
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-weight: 700;
        }

        p {
            color: #64748B;
            margin-bottom: 2rem;
            font-size: 1.1rem;
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

        .next-steps {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #E2E8F0;
        }

        .next-steps h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .steps-list {
            text-align: left;
            list-style: none;
            padding: 0;
        }

        .steps-list li {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            color: #64748B;
        }

        .steps-list li i {
            color: var(--accent-color);
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h1>Inscription réussie !</h1>
        <p><?php echo htmlspecialchars($message); ?></p>
        
        <div class="next-steps">
            <h3>Prochaines étapes</h3>
            <ul class="steps-list">
                <li><i class="fas fa-envelope"></i> Vérifiez votre boîte mail pour confirmer votre compte</li>
                <li><i class="fas fa-user-circle"></i> Connectez-vous à votre espace personnel</li>
                <li><i class="fas fa-calendar-check"></i> Commencez à prendre des rendez-vous</li>
            </ul>
        </div>

        <a href="/Applications/XAMPP/xamppfiles/htdocs/project be BD folder /index.php" class="btn btn-primary">Se connecter</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 