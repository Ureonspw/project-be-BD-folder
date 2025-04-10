<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Veuillez vous connecter pour accéder à cette page.";
    header("Location: ../index.php");
    exit();
}

// L'utilisateur est connecté, on peut afficher la page
$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="../../css/mainpagecon.css" />
   <link rel="icon" href="../../assets/images/image.png"> 
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MediRdv</title>
    
    <!-- Include FullCalendar CSS and JS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js'></script>
    <style>
        /* Styles pour la section des docteurs */
        .doctors-section {
            padding: 1.5rem;
            background-color: #f8f9fa;
        }

        .doctors-section h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #2c3e50;
            font-size: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .doctors-section h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, #3498db, #2ecc71);
            border-radius: 3px;
        }

        .specialty-section {
            margin-bottom: 1.5rem;
        }

        .specialty-section h3 {
            color: #3498db;
            margin-bottom: 0.8rem;
            padding-bottom: 0.4rem;
            border-bottom: 2px solid #3498db;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
        }

        .specialty-section h3:before {
            content: '\f0fa';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: 0.5rem;
            color: #3498db;
        }

        .doctors-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
            padding: 0.5rem;
        }

        .doctor-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            border: 1px solid #eee;
            position: relative;
        }

        .doctor-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-color: #3498db;
        }

        .doctor-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, #3498db, #2ecc71);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .doctor-card:hover:before {
            opacity: 1;
        }

        .doctor-photo {
            width: 100%;
            height: 120px;
            overflow: hidden;
            position: relative;
        }

        .doctor-photo:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 30%;
            background: linear-gradient(to top, rgba(0,0,0,0.3), transparent);
        }

        .doctor-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .doctor-card:hover .doctor-photo img {
            transform: scale(1.05);
        }

        .doctor-info {
            padding: 0.8rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .doctor-info h3 {
            color: #2c3e50;
            margin-bottom: 0.3rem;
            border: none;
            font-size: 1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 600;
        }

        .doctor-info p {
            color: #666;
            margin-bottom: 0.2rem;
            display: flex;
            align-items: center;
            font-size: 0.8rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .doctor-info p i {
            margin-right: 0.3rem;
            color: #3498db;
            width: 14px;
            text-align: center;
            flex-shrink: 0;
        }

        .doctor-info .specialty {
            font-weight: bold;
            color: #3498db;
            background-color: rgba(52, 152, 219, 0.1);
            padding: 0.1rem 0.3rem;
            border-radius: 3px;
            display: inline-block;
            margin-bottom: 0.3rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doctor-info .description {
            display: none; /* Masquer la description dans la carte principale */
        }

        .doctor-actions {
            margin-top: auto;
            display: flex;
            gap: 0.3rem;
        }

        .book-appointment-btn, .view-profile-btn {
            flex: 1;
            padding: 0.4rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .book-appointment-btn {
            background-color: #3498db;
            color: white;
        }

        .book-appointment-btn:hover {
            background-color: #2980b9;
            box-shadow: 0 2px 5px rgba(52, 152, 219, 0.3);
        }

        .view-profile-btn {
            background-color: #2ecc71;
            color: white;
        }

        .view-profile-btn:hover {
            background-color: #27ae60;
            box-shadow: 0 2px 5px rgba(46, 204, 113, 0.3);
        }

        .book-appointment-btn i, .view-profile-btn i {
            margin-right: 0.3rem;
            font-size: 0.7rem;
        }

        .loading {
            text-align: center;
            padding: 1.5rem;
            color: #666;
        }

        .no-doctors {
            text-align: center;
            padding: 1.5rem;
            color: #666;
            font-style: italic;
        }

        .error {
            text-align: center;
            padding: 1.5rem;
            color: #e74c3c;
        }

        /* Styles pour le modal de profil du docteur */
        .profile-modal {
            max-width: 800px;
        }

        .doctor-profile {
            display: flex;
            flex-direction: column;
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 2rem;
            border: 3px solid #3498db;
        }

        .profile-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-title h3 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.5rem;
        }

        .profile-title .specialty {
            color: #3498db;
            font-weight: bold;
            margin-top: 0.5rem;
        }

        .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .detail-item {
            display: flex;
            align-items: flex-start;
        }

        .detail-item i {
            color: #3498db;
            margin-right: 1rem;
            font-size: 1.2rem;
            margin-top: 0.2rem;
        }

        .detail-item h4 {
            margin: 0 0 0.5rem 0;
            color: #2c3e50;
        }

        .detail-item p {
            margin: 0;
            color: #666;
        }

        .profile-actions {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }

        .profile-actions .book-appointment-btn {
            padding: 1rem 2rem;
            font-size: 1.1rem;
        }

        /* Styles pour le modal de rendez-vous */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            width: 90%;
            max-width: 600px;
            border-radius: 10px;
            position: relative;
        }

        .close-modal {
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .close-modal:hover {
            color: #333;
        }

        #appointment-form {
            display: grid;
            gap: 1rem;
        }

        #appointment-form select,
        #appointment-form input,
        #appointment-form textarea {
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
        }

        #appointment-form textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .form-buttons button {
            flex: 1;
            padding: 0.8rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .submit-btn {
            background-color: #3498db;
            color: white;
        }

        .submit-btn:hover {
            background-color: #2980b9;
        }

        .cancel-btn {
            background-color: #e74c3c;
            color: white;
        }

        .cancel-btn:hover {
            background-color: #c0392b;
        }

        /* Styles pour la section des dons de sang */
        .blood-donation-section {
            padding: 1.5rem;
            background-color: #f8f9fa;
            margin-top: 2rem;
        }

        .blood-donation-section .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .blood-donation-section h2 {
            color: #2c3e50;
            font-size: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .blood-donation-section h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, #e74c3c, #c0392b);
            border-radius: 3px;
        }

        .new-donation-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .new-donation-btn:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
        }

        .donation-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }

        .donation-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 1rem;
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }

        .donation-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-color: #e74c3c;
        }

        .donation-info {
            margin-bottom: 1rem;
        }

        .donation-info h3 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .donation-info p {
            color: #666;
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .donation-info i {
            color: #e74c3c;
            width: 16px;
        }

        .donation-time {
            color: #666;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .donation-actions {
            display: flex;
            gap: 0.5rem;
        }

        .donation-actions button {
            flex: 1;
            padding: 0.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .reschedule-btn {
            background-color: #3498db;
            color: white;
        }

        .reschedule-btn:hover {
            background-color: #2980b9;
        }

        .status {
            padding: 0.2rem 0.5rem;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status.pending {
            background-color: #f1c40f;
            color: #fff;
        }

        .status.confirmed {
            background-color: #2ecc71;
            color: #fff;
        }

        .status.cancelled {
            background-color: #e74c3c;
            color: #fff;
        }

        .status.completed {
            background-color: #3498db;
            color: #fff;
        }

        .no-donations {
            text-align: center;
            padding: 2rem;
            color: #666;
            font-style: italic;
            grid-column: 1 / -1;
        }

        .error {
            text-align: center;
            padding: 2rem;
            color: #e74c3c;
            grid-column: 1 / -1;
        }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="headercontainer">
        <div class="logo"></div>
        <div class="boxhdcontainer">
          <a href="index.html">Acceuil</a>
          <a href="about.html">A Propos de nous</a>
          <a href="contact.html">Contacts</a>
          <a href="login.html">Rendez vous</a>
        </div>
        <button class="login" popovertarget="popover"><?php echo $user_name; ?></button>
      </div>
      <div class="contenuemainpage">
        <h1>
          Bienvenue sur votre plateforme de rendez vous santé pour votre bien
          être personnel
        </h1>
        <div class="contentmainpage">
          <h2>Bienvenue <?php echo $user_name; ?></h2>
        </div>
      </div>
    </div>
    <div id="popover" class="popover" popover>
      <div class="containerpop">
        <div class="containerpopbox1">
          <h2>Info Compte</h2>
        </div>
        <div class="containerpopbox2">
          <div class="infcon">
            <h3>Nom Complet: </h3> <samp><?php echo $user_name; ?></samp>
          </div>
          <div class="infcon">
            <h3>Sexe: </h3> <samp><?php echo isset($_SESSION['user_sexe']) ? $_SESSION['user_sexe'] : 'Non spécifié'; ?></samp>
          </div>
          <div class="infcon">
            <h3>Téléphone: </h3> <samp><?php echo isset($_SESSION['user_telephone']) ? $_SESSION['user_telephone'] : 'Non spécifié'; ?></samp>
          </div>
          <div class="infcon">
            <h3>Email: </h3> <samp><?php echo isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'Non spécifié'; ?></samp>
          </div>
          <div class="buttoncontainerpopup" onclick="window.location.href='../../index.php'">Deconnexion</div>
        </div>
      </div>
    </div>

    <div class="dashboard-container">
      <!-- Sidebar -->
      <aside class="sidebar">
        <div class="sidebar-header">
          <img src="../../assets/images/image.png" alt="MediConnect Logo" class="logo">
          <h2>MediConnect</h2>
        </div>
        
        <nav class="sidebar-nav">
          <a href="#" class="nav-item active">
            <i class="fas fa-home"></i>
            <span>Tableau de bord</span>
          </a>
          <a href="#" class="nav-item">
            <i class="fas fa-calendar-alt"></i>
            <span>Mes rendez-vous</span>
          </a>
          <a href="#" class="nav-item">
            <i class="fas fa-user-md"></i>
            <span>Docteurs</span>
          </a>
          <a href="#" class="nav-item">
            <i class="fas fa-history"></i>
            <span>Historique</span>
          </a>
          <a href="#" class="nav-item">
            <i class="fas fa-cog"></i>
            <span>Paramètres</span>
          </a>
        </nav>

        <div class="sidebar-footer">
          <a href="mainpage.html" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Déconnexion</span>
          </a>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="main-content">
        <!-- Top Header -->
        <header class="dashboard-header">
          <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Rechercher...">
          </div>
          
          <div class="header-right">
            <div class="notifications" id="notif-toggle">
              <i class="fas fa-bell"></i>
              <span class="notif-badge">3</span>
              
              <!-- Notifications Dropdown -->
              <div class="notifications-dropdown" id="notif-dropdown">
                <div class="notif-header">
                  <h3>Notifications</h3>
                  <button class="mark-all-read">Tout marquer comme lu</button>
                </div>
                
                <div class="notif-list">
                  <!-- Les notifications seront chargées dynamiquement ici -->
                </div>
              </div>
            </div>
            
            <div class="user-profile">
              <img src="../../assets/images/profile-placeholder.jpg" alt="Photo de profil">
              <span><?php echo $user_name; ?></span>
              <i class="fas fa-chevron-down"></i>
            </div>
          </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
          <!-- Quick Stats -->
          <?php
          // Récupérer les statistiques des rendez-vous
          $connection_file = __DIR__ . '/../../../config/database.php';
          if (file_exists($connection_file)) {
              require_once $connection_file;
              
              // Récupérer l'ID de l'utilisateur connecté
              $user_id = $_SESSION['user_id'];
              
              // Compter les prochains rendez-vous (non passés et non annulés)
              $query_next = "SELECT COUNT(*) as count FROM rendez_vous 
                            WHERE id_patient = ? 
                            AND date_rdv >= CURDATE() 
                            AND statut != 'annule'";
              $stmt = $conn->prepare($query_next);
              $stmt->bind_param("i", $user_id);
              $stmt->execute();
              $result_next = $stmt->get_result();
              $next_appointments = $result_next->fetch_assoc()['count'];
              
              // Compter les rendez-vous en attente de confirmation
              $query_pending = "SELECT COUNT(*) as count FROM rendez_vous 
                               WHERE id_patient = ? 
                               AND statut = 'en_attente'";
              $stmt = $conn->prepare($query_pending);
              $stmt->bind_param("i", $user_id);
              $stmt->execute();
              $result_pending = $stmt->get_result();
              $pending_appointments = $result_pending->fetch_assoc()['count'];
              
              // Compter l'historique des consultations (rendez-vous passés)
              $query_history = "SELECT COUNT(*) as count FROM rendez_vous 
                               WHERE id_patient = ? 
                               AND date_rdv < CURDATE() 
                               AND statut != 'annule'";
              $stmt = $conn->prepare($query_history);
              $stmt->bind_param("i", $user_id);
              $stmt->execute();
              $result_history = $stmt->get_result();
              $history_count = $result_history->fetch_assoc()['count'];
          } else {
              // Valeurs par défaut si le fichier de connexion n'existe pas
              $next_appointments = 0;
              $pending_appointments = 0;
              $history_count = 0;
              error_log("Fichier de connexion non trouvé: " . $connection_file);
          }
          ?>
          <section class="stats-section">
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
              </div>
              <div class="stat-info">
                <h3>Prochains RDV</h3>
                <p><?php echo $next_appointments; ?> rendez-vous</p>
              </div>
            </div>
            
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-clock"></i>
              </div>
              <div class="stat-info">
                <h3>En attente</h3>
                <p><?php echo $pending_appointments; ?> confirmations</p>
              </div>
            </div>
            
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-history"></i>
              </div>
              <div class="stat-info">
                <h3>Historique</h3>
                <p><?php echo $history_count; ?> consultations</p>
              </div>
            </div>
          </section>

          <!-- Enhanced Appointments Section -->
          <section class="appointments-section">
            <div class="section-header">
              <h2>Prochains rendez-vous</h2>
              <button class="new-appointment-btn">
                <i class="fas fa-plus"></i>
                Nouveau rendez-vous
              </button>
            </div>

            <div class="appointments-list">
              <!-- Les rendez-vous seront chargés dynamiquement ici -->
            </div>
          </section>

          <!-- Enhanced Doctors Section -->
          <section class="doctors-section">
            <div class="section-header">
              <h2>Docteurs Disponibles</h2>
              <button class="new-appointment-btn">
                <i class="fas fa-plus"></i>
                Nouveau rendez-vous
              </button>
            </div>
            <div class="doctors-list" id="doctors-list">
              <!-- Les docteurs seront chargés dynamiquement -->
              <div class="loading">Chargement des docteurs...</div>
            </div>
          </section>

          <!-- Blood Donation Section -->
          <section class="blood-donation-section">
            <div class="section-header">
              <h2>Dons de Sang</h2>
              <button class="new-donation-btn">
                <i class="fas fa-plus"></i>
                Nouveau don de sang
              </button>
            </div>
            <div class="donation-list" id="donation-list">
              <!-- Les dons seront chargés dynamiquement ici -->
              <div class="loading">Chargement des dons de sang...</div>
            </div>
          </section>

          <!-- Enhanced History Section -->
          <section class="history-section">
            <div class="section-header">
              <h2>Historique des consultations</h2>
            </div>
            <div class="history-list">
              <!-- L'historique sera chargé dynamiquement ici -->
            </div>
          </section>
        </div>
      </main>
    </div>

    <!-- Modals -->
    <div class="modal" id="new-appointment-modal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Nouveau rendez-vous</h2>
          <button class="close-modal"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="modal-body">
          <form id="appointment-form">
            <div class="form-group">
              <label>Spécialité</label>
              <select name="specialite" id="specialite-select" required>
                <option value="">Choisir une spécialité</option>
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
            
            <div class="form-group">
              <label>Docteur</label>
              <select name="doctor_id" id="doctor-select" required>
                <option value="">Choisir un docteur</option>
                <!-- Les docteurs seront chargés dynamiquement -->
              </select>
            </div>
            
            <div class="form-group">
              <label>Date</label>
              <input type="date" name="date" required>
            </div>
            
            <div class="form-group">
              <label>Heure</label>
              <select name="time" required>
                <option value="">Choisir une heure</option>
                <option value="09:00">09:00</option>
                <option value="09:30">09:30</option>
                <option value="10:00">10:00</option>
                <option value="10:30">10:30</option>
                <option value="11:00">11:00</option>
                <option value="11:30">11:30</option>
                <option value="14:00">14:00</option>
                <option value="14:30">14:30</option>
                <option value="15:00">15:00</option>
                <option value="15:30">15:30</option>
                <option value="16:00">16:00</option>
                <option value="16:30">16:30</option>
              </select>
            </div>
            
            <div class="form-group">
              <label>Motif de la consultation</label>
              <textarea name="motif" placeholder="Décrivez brièvement le motif de votre consultation" required></textarea>
            </div>
            
            <div class="form-actions">
              <button type="button" class="cancel-btn">Annuler</button>
              <button type="submit" class="submit-btn">Confirmer le rendez-vous</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Blood Donation Modal -->
    <div class="modal" id="new-donation-modal">
      <div class="modal-content">
        <div class="modal-header">
          <h2><i class="fas fa-tint"></i> Nouveau Don de Sang</h2>
          <button class="close-modal"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
          <form id="donation-form">
            <div class="form-group">
              <label>Groupe Sanguin</label>
              <select name="groupe_sanguin" required>
                <option value="">Choisir votre groupe sanguin</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
              </select>
            </div>
            <div class="form-group">
              <label>Lieu</label>
              <select name="lieu" required>
                <option value="">Choisir un lieu</option>
                <option value="Centre de Don ABC">Centre de Don ABC</option>
                <option value="Centre de Don XYZ">Centre de Don XYZ</option>
                <option value="Hôpital Central">Hôpital Central</option>
                <option value="Clinique Saint-Joseph">Clinique Saint-Joseph</option>
              </select>
            </div>
            <div class="form-group">
              <label>Date</label>
              <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
              <label>Heure</label>
              <select name="heure" required>
                <option value="">Choisir une heure</option>
                <option value="09:00">09:00</option>
                <option value="09:30">09:30</option>
                <option value="10:00">10:00</option>
                <option value="10:30">10:30</option>
                <option value="11:00">11:00</option>
                <option value="11:30">11:30</option>
                <option value="14:00">14:00</option>
                <option value="14:30">14:30</option>
                <option value="15:00">15:00</option>
                <option value="15:30">15:30</option>
                <option value="16:00">16:00</option>
                <option value="16:30">16:30</option>
              </select>
            </div>
            <div class="form-actions">
              <button type="button" class="cancel-btn">Annuler</button>
              <button type="submit" class="submit-btn">Confirmer le don</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fonction pour charger les notifications
        function loadNotifications() {
            fetch('get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    const notifList = document.querySelector('.notif-list');
                    notifList.innerHTML = '';
                    
                    if (data.success && data.notifications.length > 0) {
                        data.notifications.forEach(notification => {
                            const notifItem = document.createElement('div');
                            notifItem.className = `notif-item ${notification.read ? '' : 'unread'}`;
                            
                            notifItem.innerHTML = `
                                <div class="notif-icon ${notification.type}">
                                    <i class="fas ${getNotificationIcon(notification.type)}"></i>
                                </div>
                                <div class="notif-content">
                                    <p>${notification.message}</p>
                                    <span class="notif-time">${formatTime(notification.created_at)}</span>
                                </div>
                            `;
                            
                            notifList.appendChild(notifItem);
                        });
                    } else {
                        notifList.innerHTML = '<div class="no-notifications">Aucune notification</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.querySelector('.notif-list').innerHTML = 
                        '<div class="error">Erreur lors du chargement des notifications</div>';
                });
        }

        // Fonction pour obtenir l'icône de notification
        function getNotificationIcon(type) {
            switch(type) {
                case 'appointment':
                    return 'fa-calendar-check';
                case 'reminder':
                    return 'fa-bell';
                case 'update':
                    return 'fa-info-circle';
                default:
                    return 'fa-bell';
            }
        }

        // Fonction pour formater le temps
        function formatTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diff = now - date;
            
            // Convertir en minutes
            const minutes = Math.floor(diff / 60000);
            
            if (minutes < 1) {
                return 'À l\'instant';
            } else if (minutes < 60) {
                return `Il y a ${minutes} minute${minutes > 1 ? 's' : ''}`;
            } else if (minutes < 1440) {
                const hours = Math.floor(minutes / 60);
                return `Il y a ${hours} heure${hours > 1 ? 's' : ''}`;
            } else {
                const days = Math.floor(minutes / 1440);
                return `Il y a ${days} jour${days > 1 ? 's' : ''}`;
            }
        }

        // Gérer l'affichage/masquage du dropdown des notifications
        const notifToggle = document.getElementById('notif-toggle');
        const notifDropdown = document.getElementById('notif-dropdown');
        
        notifToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            notifDropdown.classList.toggle('show');
            loadNotifications();
        });
        
        // Fermer le dropdown en cliquant ailleurs
        document.addEventListener('click', function(e) {
            if (!notifToggle.contains(e.target) && !notifDropdown.contains(e.target)) {
                notifDropdown.classList.remove('show');
            }
        });

        // Marquer toutes les notifications comme lues
        document.querySelector('.mark-all-read').addEventListener('click', function() {
            fetch('mark_notifications_read.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

        // Charger les notifications au chargement de la page
        loadNotifications();
        
        // Recharger les notifications toutes les minutes
        setInterval(loadNotifications, 60000);
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fonction pour charger les docteurs disponibles
        function loadAllDoctors() {
            const doctorsList = document.getElementById('doctors-list');
            doctorsList.innerHTML = '<div class="loading">Chargement des docteurs...</div>';
            
            fetch('get_all_doctors.php')
                .then(response => response.json())
                .then(data => {
                    doctorsList.innerHTML = '';
                    
                    if (data.success && data.doctors.length > 0) {
                        // Grouper les docteurs par spécialité
                        const doctorsBySpecialty = {};
                        data.doctors.forEach(doctor => {
                            if (!doctorsBySpecialty[doctor.specialite]) {
                                doctorsBySpecialty[doctor.specialite] = [];
                            }
                            doctorsBySpecialty[doctor.specialite].push(doctor);
                        });
                        
                        // Afficher les docteurs par spécialité
                        Object.keys(doctorsBySpecialty).forEach(specialty => {
                            const specialtySection = document.createElement('div');
                            specialtySection.className = 'specialty-section';
                            specialtySection.innerHTML = `<h3>${specialty}</h3>`;
                            
                            const doctorsContainer = document.createElement('div');
                            doctorsContainer.className = 'doctors-container';
                            
                            doctorsBySpecialty[specialty].forEach(doctor => {
                                const doctorCard = document.createElement('div');
                                doctorCard.className = 'doctor-card';
                                
                                // Utiliser une image par défaut si aucune photo n'est disponible
                                const photoUrl = doctor.photo ? `../../uploads/doctors/${doctor.photo}` : '../../assets/images/doctor-default.jpg';
                                
                                doctorCard.innerHTML = `
                                    <div class="doctor-photo">
                                        <img src="${photoUrl}" alt="Dr. ${doctor.prenom} ${doctor.nom}">
                                    </div>
                                    <div class="doctor-info">
                                        <h3>Dr. ${doctor.prenom} ${doctor.nom}</h3>
                                        <p class="specialty"><i class="fas fa-stethoscope"></i> ${doctor.specialite}</p>
                                        <p class="hospital"><i class="fas fa-hospital"></i> ${doctor.hopital || 'Non spécifié'}</p>
                                        <p class="phone"><i class="fas fa-phone"></i> ${doctor.telephone || 'Non spécifié'}</p>
                                        <p class="email"><i class="fas fa-envelope"></i> ${doctor.email || 'Non spécifié'}</p>
                                        <p class="address"><i class="fas fa-map-marker-alt"></i> ${doctor.adresse || 'Non spécifié'}</p>
                                        ${doctor.description ? `<p class="description"><i class="fas fa-info-circle"></i> ${doctor.description}</p>` : ''}
                                        <div class="doctor-actions">
                                            <button class="book-appointment-btn" data-doctor-id="${doctor.id}" data-specialty="${doctor.specialite}">
                                                <i class="fas fa-calendar-plus"></i> Prendre rendez-vous
                                            </button>
                                            <button class="view-profile-btn" data-doctor-id="${doctor.id}">
                                                <i class="fas fa-user-md"></i> Voir le profil
                                            </button>
                                        </div>
                                    </div>
                                `;
                                
                                doctorsContainer.appendChild(doctorCard);
                            });
                            
                            specialtySection.appendChild(doctorsContainer);
                            doctorsList.appendChild(specialtySection);
                        });
                        
                        // Ajouter des écouteurs d'événements pour les boutons de rendez-vous
                        document.querySelectorAll('.book-appointment-btn').forEach(button => {
                            button.addEventListener('click', function() {
                                const doctorId = this.getAttribute('data-doctor-id');
                                const specialty = this.getAttribute('data-specialty');
                                
                                // Ouvrir le modal de nouveau rendez-vous
        document.getElementById('new-appointment-modal').style.display = 'block';
                                
                                // Pré-remplir la spécialité et le docteur
                                document.getElementById('specialite-select').value = specialty;
                                
                                // Charger les docteurs pour cette spécialité
                                loadDoctors(specialty);
                                
                                // Attendre que les docteurs soient chargés, puis sélectionner le bon docteur
                                setTimeout(() => {
                                    const doctorSelect = document.getElementById('doctor-select');
                                    for (let i = 0; i < doctorSelect.options.length; i++) {
                                        if (doctorSelect.options[i].value === doctorId) {
                                            doctorSelect.selectedIndex = i;
                                            break;
                                        }
                                    }
                                }, 500);
                            });
                        });
                        
                        // Ajouter des écouteurs d'événements pour les boutons de profil
                        document.querySelectorAll('.view-profile-btn').forEach(button => {
                            button.addEventListener('click', function() {
                                const doctorId = this.getAttribute('data-doctor-id');
                                // Ouvrir le modal de profil du docteur
                                showDoctorProfile(doctorId);
                            });
                        });
                    } else {
                        doctorsList.innerHTML = '<div class="no-doctors">Aucun docteur disponible</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    doctorsList.innerHTML = '<div class="error">Erreur lors du chargement des docteurs</div>';
                });
        }
        
        // Fonction pour afficher le profil du docteur
        function showDoctorProfile(doctorId) {
            // Récupérer les informations du docteur
            fetch(`get_doctor_profile.php?id=${doctorId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const doctor = data.doctor;
                        const photoUrl = doctor.photo ? `../../uploads/doctors/${doctor.photo}` : '../../assets/images/doctor-default.jpg';
                        
                        // Créer le modal de profil
                        const modal = document.createElement('div');
                        modal.className = 'modal';
                        modal.id = 'doctor-profile-modal';
                        
                        modal.innerHTML = `
                            <div class="modal-content profile-modal">
                                <div class="modal-header">
                                    <h2>Profil du Docteur</h2>
                                    <button class="close-modal"><i class="fas fa-times"></i></button>
                                </div>
                                <div class="modal-body">
                                    <div class="doctor-profile">
                                        <div class="profile-header">
                                            <div class="profile-photo">
                                                <img src="${photoUrl}" alt="Dr. ${doctor.prenom} ${doctor.nom}">
                                            </div>
                                            <div class="profile-title">
                                                <h3>Dr. ${doctor.prenom} ${doctor.nom}</h3>
                                                <p class="specialty">${doctor.specialite}</p>
                                            </div>
                                        </div>
                                        <div class="profile-details">
                                            <div class="detail-item">
                                                <i class="fas fa-hospital"></i>
                                                <div>
                                                    <h4>Établissement</h4>
                                                    <p>${doctor.hopital || 'Non spécifié'}</p>
                                                </div>
                                            </div>
                                            <div class="detail-item">
                                                <i class="fas fa-phone"></i>
                                                <div>
                                                    <h4>Téléphone</h4>
                                                    <p>${doctor.telephone || 'Non spécifié'}</p>
                                                </div>
                                            </div>
                                            <div class="detail-item">
                                                <i class="fas fa-envelope"></i>
                                                <div>
                                                    <h4>Email</h4>
                                                    <p>${doctor.email || 'Non spécifié'}</p>
                                                </div>
                                            </div>
                                            <div class="detail-item">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <div>
                                                    <h4>Adresse</h4>
                                                    <p>${doctor.adresse || 'Non spécifié'}</p>
                                                </div>
                                            </div>
                                            ${doctor.description ? `
                                            <div class="detail-item">
                                                <i class="fas fa-info-circle"></i>
                                                <div>
                                                    <h4>Description</h4>
                                                    <p>${doctor.description}</p>
                                                </div>
                                            </div>
                                            ` : ''}
                                            ${doctor.horaires ? `
                                            <div class="detail-item">
                                                <i class="fas fa-clock"></i>
                                                <div>
                                                    <h4>Horaires de consultation</h4>
                                                    <p>${doctor.horaires}</p>
                                                </div>
                                            </div>
                                            ` : ''}
                                        </div>
                                        <div class="profile-actions">
                                            <button class="book-appointment-btn" data-doctor-id="${doctor.id}" data-specialty="${doctor.specialite}">
                                                <i class="fas fa-calendar-plus"></i> Prendre rendez-vous
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        document.body.appendChild(modal);
                        modal.style.display = 'block';
                        
                        // Fermer le modal
                        modal.querySelector('.close-modal').addEventListener('click', function() {
                            modal.remove();
                        });
                        
                        // Gérer le bouton de rendez-vous
                        modal.querySelector('.book-appointment-btn').addEventListener('click', function() {
                            const doctorId = this.getAttribute('data-doctor-id');
                            const specialty = this.getAttribute('data-specialty');
                            
                            // Fermer le modal de profil
                            modal.remove();
                            
                            // Ouvrir le modal de nouveau rendez-vous
                            document.getElementById('new-appointment-modal').style.display = 'block';
                            
                            // Pré-remplir la spécialité et le docteur
                            document.getElementById('specialite-select').value = specialty;
                            
                            // Charger les docteurs pour cette spécialité
                            loadDoctors(specialty);
                            
                            // Attendre que les docteurs soient chargés, puis sélectionner le bon docteur
                            setTimeout(() => {
                                const doctorSelect = document.getElementById('doctor-select');
                                for (let i = 0; i < doctorSelect.options.length; i++) {
                                    if (doctorSelect.options[i].value === doctorId) {
                                        doctorSelect.selectedIndex = i;
                                        break;
                                    }
                                }
                            }, 500);
                        });
                    } else {
                        alert('Erreur lors du chargement du profil du docteur');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Une erreur est survenue lors du chargement du profil du docteur');
                });
        }
        
        // Fonction pour charger les docteurs en fonction de la spécialité
        function loadDoctors(specialite) {
            const doctorSelect = document.getElementById('doctor-select');
            doctorSelect.innerHTML = '<option value="">Chargement des docteurs...</option>';
            
            fetch(`get_doctors.php?specialite=${encodeURIComponent(specialite)}`)
                .then(response => response.json())
                .then(data => {
                    doctorSelect.innerHTML = '<option value="">Choisir un docteur</option>';
                    
                    if (data.success && data.doctors.length > 0) {
                        data.doctors.forEach(doctor => {
                            const option = document.createElement('option');
                            option.value = doctor.id;
                            option.textContent = `Dr. ${doctor.prenom} ${doctor.nom} - ${doctor.hopital}`;
                            doctorSelect.appendChild(option);
                        });
                    } else {
                        doctorSelect.innerHTML = '<option value="">Aucun docteur disponible pour cette spécialité</option>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    doctorSelect.innerHTML = '<option value="">Erreur lors du chargement des docteurs</option>';
                });
        }
        
        // Écouter les changements de spécialité
        document.getElementById('specialite-select').addEventListener('change', function() {
            const specialite = this.value;
            if (specialite) {
                loadDoctors(specialite);
            } else {
                document.getElementById('doctor-select').innerHTML = '<option value="">Choisir un docteur</option>';
            }
        });
        
        // Fonction pour charger les rendez-vous
        function loadAppointments() {
            fetch('afficher_rdv.php')
                .then(response => response.json())
                .then(data => {
                    const appointmentsList = document.querySelector('.appointments-list');
                    appointmentsList.innerHTML = '';
                    
                    if (data.success && data.appointments.length > 0) {
                        data.appointments.forEach(appointment => {
                            const date = new Date(appointment.date_rdv);
                            const statusClass = appointment.statut === 'confirme' ? 'confirmed' : 
                                             appointment.statut === 'en_attente' ? 'pending' : 
                                             appointment.statut === 'annule' ? 'cancelled' : 'completed';
                            
                            const appointmentCard = `
                                <div class="appointment-card">
                                    <div class="appointment-status ${statusClass}">
                                        <i class="fas fa-${statusClass === 'confirmed' ? 'check-circle' : 
                                                         statusClass === 'pending' ? 'clock' : 
                                                         statusClass === 'cancelled' ? 'times-circle' : 'check-double'}"></i>
                                        ${appointment.statut.charAt(0).toUpperCase() + appointment.statut.slice(1)}
                                    </div>
                                    <div class="appointment-info">
                                        <div class="doctor-info">
                                            <h3>Dr. ${appointment.doctor_prenom} ${appointment.doctor_nom}</h3>
                                            <p>${appointment.specialite}</p>
                                            <p>Motif: ${appointment.motif}</p>
                                        </div>
                                        <div class="appointment-time">
                                            <i class="fas fa-calendar"></i>
                                            <span>${date.toLocaleDateString()} à ${date.toLocaleTimeString()}</span>
                                        </div>
                                        ${appointment.statut === 'en_attente' ? `
                                            <div class="appointment-actions">
                                                <button class="cancel-btn" onclick="cancelAppointment(${appointment.id})">
                                                    <i class="fas fa-times"></i>
                                                    Annuler
                                                </button>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            `;
                            appointmentsList.innerHTML += appointmentCard;
                        });
                    } else {
                        appointmentsList.innerHTML = '<div class="no-appointments">Aucun rendez-vous trouvé</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.querySelector('.appointments-list').innerHTML = 
                        '<div class="error">Erreur lors du chargement des rendez-vous</div>';
                });
        }

        // Fonction pour annuler un rendez-vous
        window.cancelAppointment = function(appointmentId) {
            if (confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')) {
                const formData = new FormData();
                formData.append('appointment_id', appointmentId);
                formData.append('action', 'annuler');

                fetch('traitement_rdv.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Rendez-vous annulé avec succès');
                        loadAppointments();
                    } else {
                        alert(data.message || 'Erreur lors de l\'annulation du rendez-vous');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Une erreur est survenue lors de l\'annulation du rendez-vous');
                });
            }
        };

        // Gestion du formulaire de nouveau rendez-vous
      document.getElementById('appointment-form').addEventListener('submit', function(event) {
        event.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('traitement_rdv.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Rendez-vous créé avec succès');
                    document.getElementById('new-appointment-modal').style.display = 'none';
                    loadAppointments();
                } else {
                    alert(data.message || 'Erreur lors de la création du rendez-vous');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue lors de la création du rendez-vous');
            });
        });

        // Ouvrir le modal de nouveau rendez-vous
        document.querySelector('.new-appointment-btn').addEventListener('click', function() {
            document.getElementById('new-appointment-modal').style.display = 'block';
        });

        // Fermer le modal de nouveau rendez-vous
        document.querySelector('.close-modal').addEventListener('click', function() {
        document.getElementById('new-appointment-modal').style.display = 'none';
        });

        // Fermer le modal en cliquant sur le bouton Annuler
        document.querySelector('#appointment-form .cancel-btn').addEventListener('click', function() {
            document.getElementById('new-appointment-modal').style.display = 'none';
        });

        // Fermer le modal en cliquant en dehors du modal
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('new-appointment-modal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Charger les docteurs et les rendez-vous au chargement de la page
        loadAllDoctors();
        loadAppointments();
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fonction pour charger les dons de sang
        function loadBloodDonations() {
            fetch('afficher_dons_sang.php')
                .then(response => response.json())
                .then(data => {
                    const donationList = document.getElementById('donation-list');
                    donationList.innerHTML = '';
                    
                    if (data.success && data.dons.length > 0) {
                        data.dons.forEach(don => {
                            const donationCard = document.createElement('div');
                            donationCard.className = 'donation-card';
                            
                            let statusClass = '';
                            let statusText = '';
                            
                            switch(don.statut) {
                                case 'en_attente':
                                    statusClass = 'pending';
                                    statusText = 'En attente';
                                    break;
                                case 'confirme':
                                    statusClass = 'confirmed';
                                    statusText = 'Confirmé';
                                    break;
                                case 'annule':
                                    statusClass = 'cancelled';
                                    statusText = 'Annulé';
                                    break;
                                case 'termine':
                                    statusClass = 'completed';
                                    statusText = 'Terminé';
                                    break;
                            }
                            
                            donationCard.innerHTML = `
                                <div class="donation-info">
                                    <h3><i class="fas fa-tint"></i> Don de Sang</h3>
                                    <p><i class="fas fa-map-marker-alt"></i> Lieu: ${don.lieu}</p>
                                    <p><i class="fas fa-tint"></i> Groupe sanguin: ${don.groupe_sanguin}</p>
                                    <p><i class="fas fa-info-circle"></i> Statut: <span class="status ${statusClass}">${statusText}</span></p>
                                </div>
                                <div class="donation-time">
                                    <i class="fas fa-calendar"></i>
                                    <span>Date: ${don.date} à ${don.heure}</span>
                                </div>
                                ${don.statut === 'en_attente' ? `
                                <div class="donation-actions">
                                    <button class="reschedule-btn" data-id="${don.id}">
                                        <i class="fas fa-clock"></i>
                                        Reprogrammer
                                    </button>
                                    <button class="cancel-btn" data-id="${don.id}">
                                        <i class="fas fa-times"></i>
                                        Annuler
                                    </button>
                                </div>
                                ` : ''}
                            `;
                            
                            donationList.appendChild(donationCard);
                        });
                        
                        // Ajouter les écouteurs d'événements pour les boutons
                        document.querySelectorAll('.cancel-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const donId = this.getAttribute('data-id');
                                if (confirm('Êtes-vous sûr de vouloir annuler ce don de sang ?')) {
                                    cancelDonation(donId);
                                }
                            });
                        });
                        
                        document.querySelectorAll('.reschedule-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const donId = this.getAttribute('data-id');
                                showRescheduleModal(donId);
                            });
                        });
                    } else {
                        donationList.innerHTML = '<div class="no-donations">Vous n\'avez pas encore de dons de sang programmés.</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('donation-list').innerHTML = 
                        '<div class="error">Erreur lors du chargement des dons de sang.</div>';
                });
        }
        
        // Fonction pour annuler un don de sang
        function cancelDonation(donId) {
            const formData = new FormData();
            formData.append('don_id', donId);
            formData.append('action', 'annuler');
            
            fetch('traitement_don_sang.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    loadBloodDonations();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue lors de l\'annulation du don de sang.');
            });
        }
        
        // Fonction pour afficher le modal de reprogrammation
        function showRescheduleModal(donId) {
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.id = 'reschedule-modal';
            
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Reprogrammer un don de sang</h2>
                        <button class="close-modal"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <form id="reschedule-form">
                            <input type="hidden" name="don_id" value="${donId}">
                            <input type="hidden" name="action" value="reprogrammer">
                            <div class="form-group">
                                <label>Nouvelle date</label>
                                <input type="date" name="date" required>
                            </div>
                            <div class="form-group">
                                <label>Nouvelle heure</label>
                                <select name="heure" required>
                                    <option value="">Choisir une heure</option>
                                    <option value="09:00">09:00</option>
                                    <option value="09:30">09:30</option>
                                    <option value="10:00">10:00</option>
                                    <option value="10:30">10:30</option>
                                    <option value="11:00">11:00</option>
                                    <option value="11:30">11:30</option>
                                    <option value="14:00">14:00</option>
                                    <option value="14:30">14:30</option>
                                    <option value="15:00">15:00</option>
                                    <option value="15:30">15:30</option>
                                    <option value="16:00">16:00</option>
                                    <option value="16:30">16:30</option>
                                </select>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="cancel-btn">Annuler</button>
                                <button type="submit" class="submit-btn">Confirmer</button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            modal.style.display = 'block';
            
            // Fermer le modal
            modal.querySelector('.close-modal').addEventListener('click', function() {
                modal.remove();
            });
            
            modal.querySelector('.cancel-btn').addEventListener('click', function() {
                modal.remove();
            });
            
            // Soumettre le formulaire
            modal.querySelector('#reschedule-form').addEventListener('submit', function(event) {
                event.preventDefault();
                
                const formData = new FormData(this);
                
                fetch('traitement_don_sang.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        modal.remove();
                        loadBloodDonations();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Une erreur est survenue lors de la reprogrammation du don de sang.');
                });
            });
        }
        
        // Charger les dons de sang au chargement de la page
        loadBloodDonations();
        
        // Ouvrir le modal de nouveau don
        document.querySelector('.new-donation-btn').addEventListener('click', function() {
            document.getElementById('new-donation-modal').style.display = 'block';
        });
        
        // Gérer le formulaire de nouveau don
        document.getElementById('donation-form').addEventListener('submit', function(event) {
            event.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'ajouter');
            
            fetch('traitement_don_sang.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    document.getElementById('new-donation-modal').style.display = 'none';
                    loadBloodDonations();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue lors de l\'envoi du formulaire.');
            });
        });
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fonction pour charger l'historique des consultations
        function loadConsultationHistory() {
            fetch('get_consultation_history.php')
                .then(response => response.json())
                .then(data => {
                    const historyList = document.querySelector('.history-list');
                    historyList.innerHTML = '';
                    
                    if (data.success && data.consultations.length > 0) {
                        data.consultations.forEach(consultation => {
                            const historyCard = document.createElement('div');
                            historyCard.className = 'history-card';
                            
                            historyCard.innerHTML = `
                                <div class="history-info">
                                    <div class="doctor-profile">
                                        <img src="${consultation.doctor_photo}" alt="${consultation.doctor_name}">
                                        <div class="doctor-details">
                                            <h3>${consultation.doctor_name}</h3>
                                            <p class="specialty">${consultation.specialty}</p>
                                        </div>
                                    </div>
                                    <div class="consultation-details">
                                        <p><i class="fas fa-calendar"></i> Date: ${consultation.date} à ${consultation.time}</p>
                                        <p><i class="fas fa-stethoscope"></i> Motif: ${consultation.reason}</p>
                                        <p><i class="fas fa-file-medical"></i> Notes: ${consultation.notes}</p>
                                        <p><i class="fas fa-prescription"></i> Prescription: ${consultation.prescription}</p>
                                    </div>
                                </div>
                            `;
                            
                            historyList.appendChild(historyCard);
                        });
                    } else {
                        historyList.innerHTML = '<div class="no-history">Aucune consultation dans l\'historique</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.querySelector('.history-list').innerHTML = 
                        '<div class="error">Erreur lors du chargement de l\'historique</div>';
                });
        }

        // Charger l'historique des consultations au chargement de la page
        loadConsultationHistory();
    });
    </script>

    <script src="../../js/mainpage.js"></script>
  </body>
</html>
