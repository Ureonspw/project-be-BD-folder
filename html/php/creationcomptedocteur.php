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
        $stmt = $conn->prepare("SELECT id FROM doctors WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("Cet email est déjà utilisé.");
        }

        // Gérer l'upload de photo
        $photo_path = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../uploads/doctors/';
            
            // Créer le répertoire s'il n'existe pas
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('doctor_') . '.' . $file_extension;
            $target_file = $upload_dir . $file_name;
            
            // Vérifier le type de fichier
            $allowed_types = ['jpg', 'jpeg', 'png'];
            if (!in_array(strtolower($file_extension), $allowed_types)) {
                throw new Exception("Seuls les fichiers JPG, JPEG et PNG sont autorisés.");
            }
            
            // Déplacer le fichier
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                $photo_path = 'uploads/doctors/' . $file_name;
            } else {
                throw new Exception("Erreur lors de l'upload de la photo.");
            }
        }

        // Préparer et exécuter la requête d'insertion
        $stmt = $conn->prepare("INSERT INTO doctors (prenom, nom, email, telephone, password, specialite, description, photo, disponibilite, hopital) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([
            $_POST['prenom'],
            $_POST['nom'],
            $_POST['email'],
            $_POST['telephone'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $_POST['specialite'],
            $_POST['description'] ?: null,
            $photo_path,
            $_POST['disponibilite'] ?: null,
            $_POST['hopital'] ?: null
        ])) {
            $_SESSION['success_message'] = "Votre compte docteur a été créé avec succès ! Vous pouvez maintenant vous connecter.";
            header("Location: confirmation_inscription_docteur.php");
            exit();
        } else {
            throw new Exception("Une erreur est survenue lors de la création du compte. Veuillez réessayer.");
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Une erreur de base de données est survenue. Veuillez réessayer plus tard.";
        error_log("Erreur PDO: " . $e->getMessage()); // Pour le débogage
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
    <title>Inscription Docteur - MediConnect</title>
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
            max-width: 1200px;
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
            min-height: 100vh;
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

        .form-section h3 {
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .form-control, .form-select {
            border: 2px solid #E2E8F0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
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

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #E2E8F0;
            z-index: 1;
        }

        .step {
            position: relative;
            z-index: 2;
            background: white;
            padding: 0 1rem;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #E2E8F0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .step.active .step-circle {
            background: var(--primary-color);
            color: white;
        }

        .step.completed .step-circle {
            background: var(--accent-color);
            color: white;
        }

        .step-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #64748B;
        }

        .step.active .step-title {
            color: var(--primary-color);
        }

        .step.completed .step-title {
            color: var(--accent-color);
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }

        .btn-next {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-next:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-prev {
            background: #E2E8F0;
            color: var(--text-color);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-prev:hover {
            background: #CBD5E0;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container inscription-container">
        <div class="row g-0">
            <div class="col-lg-4">
                <div class="left-panel">
                    <h1>MediConnect</h1>
                    <p class="lead mb-4">Votre plateforme de rendez-vous médicaux en ligne</p>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div>
                            <h5>Gestion de votre cabinet</h5>
                            <p>Gérez facilement vos rendez-vous et votre agenda</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <h5>Rendez-vous en ligne</h5>
                            <p>Permettez à vos patients de prendre rendez-vous 24/7</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <h5>Suivi de votre activité</h5>
                            <p>Visualisez vos statistiques et améliorez votre pratique</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="right-panel">
                    <div class="form-header">
                        <h2>Inscription Docteur</h2>
                        <p>Remplissez les informations ci-dessous pour créer votre compte docteur</p>
                    </div>

                    <?php
                    if(isset($_SESSION['error_message'])) {
                        echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                        unset($_SESSION['error_message']);
                    }
                    ?>

                    <div class="step-indicator">
                        <div class="step active" id="step1-indicator">
                            <div class="step-circle">1</div>
                            <div class="step-title">Informations personnelles</div>
                        </div>
                        <div class="step" id="step2-indicator">
                            <div class="step-circle">2</div>
                            <div class="step-title">Informations professionnelles</div>
                        </div>
                        <div class="step" id="step3-indicator">
                            <div class="step-circle">3</div>
                            <div class="step-title">Disponibilités</div>
                        </div>
                    </div>

                    <form id="inscriptionForm" method="POST" action="creationcomptedocteur.php" enctype="multipart/form-data">
                        <!-- Étape 1: Informations personnelles -->
                        <div class="form-section active" id="step1">
                            <h3><i class="fas fa-user me-2"></i>Informations personnelles</h3>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="prenom" class="form-label required-field">Prénom</label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="nom" class="form-label required-field">Nom</label>
                                    <input type="text" class="form-control" id="nom" name="nom" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label required-field">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="telephone" class="form-label required-field">Téléphone</label>
                                    <input type="tel" class="form-control" id="telephone" name="telephone" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label required-field">Mot de passe</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label required-field">Confirmer le mot de passe</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="photo" class="form-label">Photo de profil</label>
                                <input type="file" class="form-control" id="photo" name="photo" accept="image/jpeg,image/png">
                                <small class="text-muted">Formats acceptés: JPG, JPEG, PNG. Taille maximale: 2MB</small>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="window.location.href='../../index.php'">Annuler</button>
                                <button type="button" class="btn btn-next" onclick="nextStep(1)">Suivant <i class="fas fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Étape 2: Informations professionnelles -->
                        <div class="form-section" id="step2">
                            <h3><i class="fas fa-stethoscope me-2"></i>Informations professionnelles</h3>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="specialite" class="form-label required-field">Spécialité</label>
                                    <select class="form-select" id="specialite" name="specialite" required>
                                        <option value="" selected disabled>Sélectionnez</option>
                                        <option value="Médecin généraliste">Médecin généraliste</option>
                                        <option value="Cardiologue">Cardiologue</option>
                                        <option value="Dermatologue">Dermatologue</option>
                                        <option value="Endocrinologue">Endocrinologue</option>
                                        <option value="Gynécologue">Gynécologue</option>
                                        <option value="Ophtalmologue">Ophtalmologue</option>
                                        <option value="ORL">ORL</option>
                                        <option value="Pédiatre">Pédiatre</option>
                                        <option value="Psychiatre">Psychiatre</option>
                                        <option value="Radiologue">Radiologue</option>
                                        <option value="Autre">Autre</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="hopital" class="form-label">Hôpital ou Cabinet</label>
                                    <input type="text" class="form-control" id="hopital" name="hopital" placeholder="Nom de votre hôpital ou cabinet">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" placeholder="Décrivez votre parcours, vos compétences et votre approche médicale"></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn btn-prev" onclick="prevStep(2)"><i class="fas fa-arrow-left me-2"></i> Retour</button>
                                <button type="button" class="btn btn-next" onclick="nextStep(2)">Suivant <i class="fas fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Étape 3: Disponibilités -->
                        <div class="form-section" id="step3">
                            <h3><i class="fas fa-clock me-2"></i>Disponibilités</h3>
                            
                            <div class="mb-3">
                                <label for="disponibilite" class="form-label">Disponibilités</label>
                                <textarea class="form-control" id="disponibilite" name="disponibilite" rows="4" placeholder="Ex: Lundi-Vendredi: 9h-18h, Samedi: 9h-13h"></textarea>
                                <small class="text-muted">Décrivez vos horaires de consultation habituels</small>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn btn-prev" onclick="prevStep(3)"><i class="fas fa-arrow-left me-2"></i> Retour</button>
                                <button type="submit" class="btn btn-primary">Finaliser l'inscription</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function nextStep(currentStep) {
            // Validation de l'étape actuelle
            if (!validateStep(currentStep)) {
                return;
            }

            // Masquer l'étape actuelle
            document.getElementById('step' + currentStep).classList.remove('active');
            document.getElementById('step' + currentStep + '-indicator').classList.remove('active');

            // Afficher l'étape suivante
            document.getElementById('step' + (currentStep + 1)).classList.add('active');
            document.getElementById('step' + (currentStep + 1) + '-indicator').classList.add('active');
        }

        function prevStep(currentStep) {
            // Masquer l'étape actuelle
            document.getElementById('step' + currentStep).classList.remove('active');
            document.getElementById('step' + currentStep + '-indicator').classList.remove('active');

            // Afficher l'étape précédente
            document.getElementById('step' + (currentStep - 1)).classList.add('active');
            document.getElementById('step' + (currentStep - 1) + '-indicator').classList.add('active');
        }

        function validateStep(step) {
            const requiredFields = document.querySelectorAll('#step' + step + ' [required]');
            let emptyFields = [];

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    emptyFields.push(field.previousElementSibling.textContent.replace(' *', ''));
                }
            });

            if (emptyFields.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Champs manquants',
                    text: 'Veuillez remplir les champs suivants : ' + emptyFields.join(', '),
                    confirmButtonColor: '#2D5A77'
                });
                return false;
            }

            return true;
        }

        // Validation du formulaire
        document.getElementById('inscriptionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateStep(3)) {
                return;
            }

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