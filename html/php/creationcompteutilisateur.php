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

        // Gérer les champs optionnels
        $allergies = null;
        $maladies = null;

        // Vérifier les allergies
        if (isset($_POST['allergies_check']) && $_POST['allergies_check'] === 'oui') {
            if (isset($_POST['allergies_details']) && !empty($_POST['allergies_details'])) {
                $allergies = $_POST['allergies_details'];
            }
        }

        // Vérifier les maladies chroniques
        if (isset($_POST['maladies_check']) && $_POST['maladies_check'] === 'oui') {
            if (isset($_POST['maladies_details']) && !empty($_POST['maladies_details'])) {
                $maladies = $_POST['maladies_details'];
            }
        }

        // Préparer et exécuter la requête d'insertion
        $stmt = $conn->prepare("INSERT INTO users (prenom, nom, email, telephone, password, sexe, date_naissance, adresse, code_postal, ville, pays, groupe_sanguin, poids, taille, fumeur, allergies, maladies_chroniques, medicaments, antecedents, urgence_nom, urgence_relation, urgence_telephone, urgence_email, medecin_traitant, commentaires) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
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
            $_POST['poids'] ?: null,
            $_POST['taille'] ?: null,
            $_POST['fumeur'],
            $allergies,
            $maladies,
            $_POST['medicaments'] ?: null,
            $_POST['antecedents'] ?: null,
            $_POST['urgence_nom'],
            $_POST['urgence_relation'],
            $_POST['urgence_telephone'],
            $_POST['urgence_email'] ?: null,
            $_POST['medecin_traitant'] ?: null,
            $_POST['commentaires'] ?: null
        ])) {
            $_SESSION['success_message'] = "Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.";
            header("Location: confirmation_inscription.php");
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
    <title>Inscription Patient - MediConnect</title>
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
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <h5>Rendez-vous simplifiés</h5>
                            <p>Prenez rendez-vous avec vos médecins en quelques clics</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div>
                            <h5>Accès aux meilleurs docteurs</h5>
                            <p>Consultez les profils et avis sur nos professionnels de santé</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <div>
                            <h5>Dossier médical sécurisé</h5>
                            <p>Vos informations de santé sont protégées et accessibles</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="right-panel">
                    <div class="form-header">
                        <h2>Création de compte patient</h2>
                        <p>Remplissez les informations ci-dessous pour créer votre compte</p>
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
                            <div class="step-title">Informations médicales</div>
                        </div>
                        <div class="step" id="step3-indicator">
                            <div class="step-circle">3</div>
                            <div class="step-title">Contact d'urgence</div>
                        </div>
                    </div>

                    <form id="inscriptionForm" method="POST" action="creationcompteutilisateur.php">
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
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="sexe" class="form-label required-field">Sexe</label>
                                    <select class="form-select" id="sexe" name="sexe" required>
                                        <option value="" selected disabled>Sélectionnez</option>
                                        <option value="Masculin">Masculin</option>
                                        <option value="Féminin">Féminin</option>
                                        <option value="Autre">Autre</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="date_naissance" class="form-label required-field">Date de naissance</label>
                                    <input type="date" class="form-control" id="date_naissance" name="date_naissance" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="adresse" class="form-label required-field">Adresse</label>
                                    <textarea class="form-control" id="adresse" name="adresse" rows="2" required></textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="code_postal" class="form-label required-field">Code postal</label>
                                    <input type="text" class="form-control" id="code_postal" name="code_postal" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="ville" class="form-label required-field">Ville</label>
                                    <input type="text" class="form-control" id="ville" name="ville" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="pays" class="form-label required-field">Pays</label>
                                    <input type="text" class="form-control" id="pays" name="pays" value="France" required>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="window.location.href='../../index.php'">Annuler</button>
                                <button type="button" class="btn btn-next" onclick="nextStep(1)">Suivant <i class="fas fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Étape 2: Informations médicales -->
                        <div class="form-section" id="step2">
                            <h3><i class="fas fa-heartbeat me-2"></i>Informations médicales</h3>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="groupe_sanguin" class="form-label">Groupe sanguin</label>
                                    <select class="form-select" id="groupe_sanguin" name="groupe_sanguin">
                                        <option value="" selected disabled>Sélectionnez</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                        <option value="inconnu">Je ne sais pas</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="poids" class="form-label">Poids (kg)</label>
                                    <input type="number" step="0.1" class="form-control" id="poids" name="poids">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="taille" class="form-label">Taille (cm)</label>
                                    <input type="number" class="form-control" id="taille" name="taille">
                                </div>
                                <div class="col-md-6">
                                    <label for="fumeur" class="form-label">Fumeur</label>
                                    <select class="form-select" id="fumeur" name="fumeur">
                                        <option value="" selected disabled>Sélectionnez</option>
                                        <option value="non">Non</option>
                                        <option value="oui">Oui</option>
                                        <option value="ancien">Ancien fumeur</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Avez-vous des allergies connues ?</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="allergies_check" id="allergies_non" value="non" checked>
                                    <label class="form-check-label" for="allergies_non">Non</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="allergies_check" id="allergies_oui" value="oui">
                                    <label class="form-check-label" for="allergies_oui">Oui</label>
                                </div>
                                <div class="mt-2 hidden" id="allergies_details_div">
                                    <label for="allergies_details" class="form-label">Précisez vos allergies</label>
                                    <textarea class="form-control" id="allergies_details" name="allergies_details" rows="2"></textarea>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Avez-vous des maladies chroniques ?</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="maladies_check" id="maladies_non" value="non" checked>
                                    <label class="form-check-label" for="maladies_non">Non</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="maladies_check" id="maladies_oui" value="oui">
                                    <label class="form-check-label" for="maladies_oui">Oui</label>
                                </div>
                                <div class="mt-2 hidden" id="maladies_details_div">
                                    <label for="maladies_details" class="form-label">Précisez vos maladies chroniques</label>
                                    <textarea class="form-control" id="maladies_details" name="maladies_details" rows="2"></textarea>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="medicaments" class="form-label">Médicaments actuels</label>
                                <textarea class="form-control" id="medicaments" name="medicaments" rows="2" placeholder="Listez les médicaments que vous prenez régulièrement"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="antecedents" class="form-label">Antécédents médicaux importants</label>
                                <textarea class="form-control" id="antecedents" name="antecedents" rows="2" placeholder="Ex: Opérations chirurgicales, hospitalisations majeures..."></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn btn-prev" onclick="prevStep(2)"><i class="fas fa-arrow-left me-2"></i> Retour</button>
                                <button type="button" class="btn btn-next" onclick="nextStep(2)">Suivant <i class="fas fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Étape 3: Contact d'urgence -->
                        <div class="form-section" id="step3">
                            <h3><i class="fas fa-first-aid me-2"></i>Contact d'urgence</h3>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="urgence_nom" class="form-label required-field">Nom du contact</label>
                                    <input type="text" class="form-control" id="urgence_nom" name="urgence_nom" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="urgence_relation" class="form-label required-field">Relation</label>
                                    <input type="text" class="form-control" id="urgence_relation" name="urgence_relation" placeholder="Ex: Conjoint, Parent, Ami" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="urgence_telephone" class="form-label required-field">Téléphone</label>
                                    <input type="tel" class="form-control" id="urgence_telephone" name="urgence_telephone" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="urgence_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="urgence_email" name="urgence_email">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="medecin_traitant" class="form-label">Médecin traitant</label>
                                <input type="text" class="form-control" id="medecin_traitant" name="medecin_traitant" placeholder="Nom de votre médecin traitant habituel">
                            </div>

                            <div class="mb-3">
                                <label for="commentaires" class="form-label">Autres informations importantes</label>
                                <textarea class="form-control" id="commentaires" name="commentaires" rows="3" placeholder="Informations supplémentaires que vous souhaitez partager avec les médecins"></textarea>
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

        // Gestion des détails d'allergies et maladies
        document.querySelectorAll('input[name="allergies_check"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('allergies_details_div').classList.toggle('hidden', this.value === 'non');
            });
        });

        document.querySelectorAll('input[name="maladies_check"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('maladies_details_div').classList.toggle('hidden', this.value === 'non');
            });
        });

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