<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['doctor_id'])) {
    $_SESSION['error_message'] = "Veuillez vous connecter pour accéder à cette page.";
    header("Location: ../index.php");
    exit();
}

// L'utilisateur est connecté, on peut afficher la page
$doctor_name = $_SESSION['doctor_name'];
$doctor_specialite = $_SESSION['doctor_specialite'];
$doctor_photo = $_SESSION['doctor_photo'];
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="../../css/mainpageadmin.css" />
    <link rel="stylesheet" href="../../css/doctor-dashboard.css" />
    <link rel="icon" href="../../assets/images/image.png"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MediRdv - Dashboard Docteur</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  </head>
  <body data-doctor-id="<?php echo $_SESSION['doctor_id']; ?>">
    <div class="container">
      <div class="headercontainer">
        <div class="logo"></div>
        <div class="boxhdcontainer">
          <a href="index.html">Acceuil</a>
          <a href="about.html">A Propos de nous</a>
          <a href="contact.html">Contacts</a>
          <a href="login.html">Rendez vous</a>
        </div>
        <button class="login" popovertarget="popover"><?php echo $doctor_name; ?></button>
      </div>
      <div class="contenuemainpage">
        <h1>
          Bienvenue Dr. <?php echo $doctor_name; ?> sur votre plateforme de rendez vous santé pour votre bien
          être personnel
        </h1>
        <div class="contentmainpage">
          <h2>Bienvenue Dr. <?php echo $doctor_name; ?></h2>
          <div class="exploremenu">
            <div class="menucontentexplore">
              <div class="barcontent1">Consultation plus rapide</div>
              <div class="barcontent2">
                <div class="txtcontent">
                  vous permet de consulter rapidement les rendez vous de vos
                  proches
                </div>
                <div class="imgcontent">▸</div>
              </div>
            </div>
            <div class="menucontentexplore">
              <div class="barcontent1">Consultation plus rapide</div>
              <div class="barcontent2">
                <div class="txtcontent">
                  vous permet de consulter rapidement les rendez vous de vos
                  proches
                </div>
                <div class="imgcontent">▸</div>
              </div>
            </div>
            <div class="menucontentexplore">
              <div class="barcontent1">Consultation plus rapide</div>
              <div class="barcontent2">
                <div class="txtcontent">
                  vous permet de consulter rapidement les rendez vous de vos
                  proches
                </div>
                <div class="imgcontent">▸</div>
              </div>
            </div>
          </div>
        </div>
        
        
        <!-- Dashboard du Docteur -->
        <div class="dashboard-container">
          <div class="dashboard-header">
            <h2>Dashboard Docteur</h2>
            <p>Gérez vos rendez-vous, prescriptions et dons de sang</p>
          </div>
          
          <div class="dashboard-grid">
            <!-- Section des rendez-vous -->
            <div class="dashboard-card appointments-section">
              <h3><i class="fas fa-calendar-check"></i> Gestion des Rendez-vous</h3>
              <div class="appointments-tabs">
                <div class="appointments-tab" data-tab="en_attente">En attente</div>
                <div class="appointments-tab" data-tab="confirme">Acceptés</div>
                <div class="appointments-tab" data-tab="termine">Terminés</div>
              </div>
              
              <div class="appointment-tab-content" id="en_attente-appointments">
                <div class="appointment-list">
                  <!-- Les rendez-vous en attente seront chargés dynamiquement -->
                </div>
              </div>
              
              <div class="appointment-tab-content" id="confirme-appointments" style="display: none;">
                <div class="appointment-list">
                  <!-- Les rendez-vous acceptés seront chargés dynamiquement -->
                </div>
              </div>
              
              <div class="appointment-tab-content" id="termine-appointments" style="display: none;">
                <div class="appointment-list">
                  <!-- Les rendez-vous terminés seront chargés dynamiquement -->
                </div>
              </div>
            </div>
            
            <!-- Section du calendrier -->
            <div class="dashboard-card calendar-section">
              <h3><i class="fas fa-calendar-alt"></i> Emploi du temps</h3>
              <div class="calendar-container">
                <div class="calendar-header">
                  <div class="calendar-nav">
                    <button class="prev-month">
                      <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="month-year">Janvier 2023</div>
                    <button class="next-month">
                      <i class="fas fa-chevron-right"></i>
                    </button>
                  </div>
                  <div class="calendar-view-options">
                    <button class="btn active" data-view="week">Semaine</button>
                    <button class="btn" data-view="month">Mois</button>
                  </div>
                </div>
                <div class="calendar-grid">
                  <!-- Le calendrier sera généré dynamiquement par JavaScript -->
                </div>
              </div>
            </div>
            
            <!-- Section des prescriptions -->
            <div class="dashboard-card prescription-section" style="display: none;">
              <h3><i class="fas fa-prescription"></i> Prescriptions</h3>
              <div class="prescription-form">
                <div class="form-group">
                  <label for="patient-name">Nom du patient</label>
                  <input type="text" id="patient-name" class="form-control" required>
                </div>
                <input type="hidden" id="appointment-id" value="">
                <div class="form-group">
                  <label for="diagnosis">Diagnostic</label>
                  <input type="text" id="diagnosis" class="form-control" required>
                </div>
                <div class="form-group">
                  <label for="prescription">Prescription</label>
                  <textarea id="prescription" class="form-control" required></textarea>
                </div>
                <div class="form-group">
                  <label for="notes">Notes supplémentaires</label>
                  <textarea id="notes" class="form-control"></textarea>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                  <button type="button" class="btn btn-primary" onclick="savePrescription()">Enregistrer la prescription</button>
                  <button type="button" class="btn btn-secondary" onclick="document.querySelector('.prescription-section').style.display = 'none';">Annuler</button>
                </div>
              </div>
            </div>
            
            <!-- Section des dons de sang -->
            <div class="dashboard-card blood-donation-section">
              <h3><i class="fas fa-tint"></i> Gestion des dons de sang</h3>
              
              <div class="donation-stats">
                <div class="stat-card">
                  <div class="stat-value" data-stat="pending-requests">0</div>
                  <div class="stat-label">Demandes en attente</div>
                </div>
                <div class="stat-card">
                  <div class="stat-value" data-stat="completed-donations">0</div>
                  <div class="stat-label">Dons complétés</div>
                </div>
                <div class="stat-card">
                  <div class="stat-value" data-stat="total-donations">0</div>
                  <div class="stat-label">Total des dons</div>
                </div>
              </div>
              
              <h4>Demandes en attente</h4>
              <div class="donation-list">
                <!-- Les demandes seront chargées dynamiquement par JavaScript -->
              </div>
              
              <h4>Statistiques des groupes sanguins</h4>
              <div class="blood-type-chart-container">
                <div class="chart-header">
                </div>
                <div class="blood-type-chart">
                  <canvas id="bloodTypeChart"></canvas>
                </div>
              </div>

              <h4>Historique des dons</h4>
              <div class="donation-history-container">
                <div class="history-filters">
                  <input type="date" id="history-date" class="form-control" onchange="loadDonationHistory()" placeholder="Filtrer par date">
                </div>
                <div class="donation-history">
                  <!-- L'historique sera chargé dynamiquement par JavaScript -->
                </div>
              </div>
            </div>

            <!-- Section des patients -->
            <div class="dashboard-card patients-section">
              <h3><i class="fas fa-users"></i> Patients</h3>
              <div class="patients-list">
                <!-- Les patients seront chargés dynamiquement -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Popup pour les détails du patient -->
    <div id="patientDetailsPopup" class="popup" style="display: none;">
      <div class="popup-content">
        <span class="close-popup">&times;</span>
        <h2>Détails du Patient</h2>
        <div class="patient-details">
          <!-- Les détails seront chargés dynamiquement -->
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
            <h3>Nom Complet: </h3> <samp><?php echo $doctor_name; ?></samp>
          </div>
          <div class="infcon">
            <h3>Spécialité: </h3> <samp><?php echo $doctor_specialite; ?></samp>
          </div>
          <div class="infcon">
            <h3>Photo: </h3> 
            <?php if($doctor_photo): ?>
              <img src="../../uploads/doctors/<?php echo $doctor_photo; ?>" alt="Photo du docteur" style="max-width: 100px; border-radius: 50%;">
            <?php else: ?>
              <samp>Non spécifié</samp>
            <?php endif; ?>
          </div>
          <div class="buttoncontainerpopup" onclick="window.location.href='../../index.php'">Deconnexion</div>
        </div>
      </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../js/doctor-dashboard.js"></script>
    <script src="../../js/patient-management.js"></script>
    <script src="../../js/blood-donation.js"></script>
  </body>
</html>

