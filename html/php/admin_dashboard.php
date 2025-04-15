<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Traitement de la suppression
if (isset($_POST['delete_doctor'])) {
    $doctor_id = $_POST['doctor_id'];
    $stmt = $conn->prepare("DELETE FROM doctors WHERE id = ?");
    $stmt->execute([$doctor_id]);
    $_SESSION['success_message'] = "Docteur supprimé avec succès";
}

if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $_SESSION['success_message'] = "Utilisateur supprimé avec succès";
}

// Recherche
$search = isset($_GET['search']) ? $_GET['search'] : '';
$doctors = [];
$users = [];

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM doctors WHERE CONCAT(prenom, ' ', nom) LIKE ? OR email LIKE ?");
    $stmt->execute(["%$search%", "%$search%"]);
    $doctors = $stmt->fetchAll();

    $stmt = $conn->prepare("SELECT * FROM users WHERE CONCAT(prenom, ' ', nom) LIKE ? OR email LIKE ?");
    $stmt->execute(["%$search%", "%$search%"]);
    $users = $stmt->fetchAll();
} else {
    $stmt = $conn->query("SELECT * FROM doctors");
    $doctors = $stmt->fetchAll();
    
    $stmt = $conn->query("SELECT * FROM users");
    $users = $stmt->fetchAll();
}

// Statistiques
$total_doctors = count($doctors);
$total_users = count($users);
$stmt = $conn->query("SELECT COUNT(*) as total FROM admin");
$total_admins = $stmt->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin - MediConnect</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2D5A77;
            --secondary-color: #4A90E2;
            --accent-color: #67B26F;
            --danger-color: #E53E3E;
            --warning-color: #F6AD55;
            --text-color: #2C3E50;
            --light-bg: #F8FAFC;
        }

        body {
            background: var(--light-bg);
            color: var(--text-color);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .card-icon.doctor {
            background: rgba(74, 144, 226, 0.1);
            color: var(--secondary-color);
        }

        .card-icon.user {
            background: rgba(103, 178, 111, 0.1);
            color: var(--accent-color);
        }

        .card-icon.admin {
            background: rgba(245, 101, 101, 0.1);
            color: var(--danger-color);
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .search-container {
            margin: 20px 0;
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .table-container {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
        }

        .btn-danger {
            background: var(--danger-color);
            border: none;
        }

        .btn-danger:hover {
            background: #C53030;
        }

        .management-section {
            margin-bottom: 2rem;
        }

        .section-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--secondary-color);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">MediConnect Admin</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link text-light">Bienvenue, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <a class="nav-item nav-link" href="logout.php">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if(isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Section Statistiques -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: rgba(74, 144, 226, 0.1); color: var(--secondary-color);">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0">Total Docteurs</h6>
                            <h3 class="mb-0"><?php echo $total_doctors; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: rgba(103, 178, 111, 0.1); color: var(--accent-color);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0">Total Patients</h6>
                            <h3 class="mb-0"><?php echo $total_users; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: rgba(245, 101, 101, 0.1); color: var(--danger-color);">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0">Total Admins</h6>
                            <h3 class="mb-0"><?php echo $total_admins; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Gestion -->
        <div class="management-section">
            <h2 class="section-title">Gestion des Utilisateurs</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="card-icon doctor">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <h5 class="card-title">Gérer les Docteurs</h5>
                            <p class="card-text">Ajoutez, modifiez ou supprimez des comptes docteurs. Gérez leurs spécialités et disponibilités.</p>
                            <a href="creationcomptedocteur.php" class="btn btn-primary btn-action">
                                <i class="fas fa-plus me-2"></i>Ajouter un Docteur
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="card-icon user">
                                <i class="fas fa-users"></i>
                            </div>
                            <h5 class="card-title">Gérer les Patients</h5>
                            <p class="card-text">Créez et gérez les comptes patients. Consultez leurs dossiers médicaux et rendez-vous.</p>
                            <a onclick="window.location.href='./creationcompteutilisateur.php'" class="btn btn-primary btn-action">
                                <i class="fas fa-plus me-2"></i>Ajouter un Patient
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="card-icon admin">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <h5 class="card-title">Gérer les Admins</h5>
                            <p class="card-text">Créez de nouveaux comptes administrateurs pour gérer la plateforme.</p>
                            <a href="admin_register.php" class="btn btn-primary btn-action">
                                <i class="fas fa-plus me-2"></i>Ajouter un Admin
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Recherche -->
        <div class="search-container">
            <h2 class="section-title">Recherche</h2>
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Rechercher un docteur ou un utilisateur..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Rechercher</button>
            </form>
        </div>

        <!-- Section Tableaux -->
        <div class="table-container">
            <h2 class="section-title">Docteurs</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Spécialité</th>
                            <th>Téléphone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($doctors as $doctor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($doctor['id']); ?></td>
                            <td><?php echo htmlspecialchars($doctor['prenom'] . ' ' . $doctor['nom']); ?></td>
                            <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                            <td><?php echo htmlspecialchars($doctor['specialite']); ?></td>
                            <td><?php echo htmlspecialchars($doctor['telephone']); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="doctor_id" value="<?php echo $doctor['id']; ?>">
                                    <button type="submit" name="delete_doctor" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce docteur ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <h2 class="section-title mt-5">Utilisateurs</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['telephone']); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 