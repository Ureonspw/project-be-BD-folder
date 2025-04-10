<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../assets/images/image.png" alt="MediConnect Logo" class="logo">
            <h2>MediConnect</h2>
        </div>
        
        <nav class="sidebar-nav">
            <a href="#" class="nav-item active">
                <i class="fas fa-home"></i>
                <span>Tableau de bord</span>
            </a>
            <a href="appointments.php" class="nav-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Mes rendez-vous</span>
            </a>
            <a href="doctors.php" class="nav-item">
                <i class="fas fa-user-md"></i>
                <span>Docteurs</span>
            </a>
            <a href="history.php" class="nav-item">
                <i class="fas fa-history"></i>
                <span>Historique</span>
            </a>
            <a href="settings.php" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Paramètres</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Appointments Section -->
        <section class="appointments-section">
            <div class="section-header">
                <h2>Mes Rendez-vous</h2>
                <button class="new-appointment-btn">
                    <i class="fas fa-plus"></i> Nouveau Rendez-vous
                </button>
            </div>
            <div class="appointments-list">
                <?php foreach($appointments as $appointment): ?>
                <div class="appointment-card">
                    <div class="appointment-info">
                        <h3>Dr. <?php echo htmlspecialchars($appointment['doctor_prenom'] . ' ' . $appointment['doctor_nom']); ?></h3>
                        <p>Spécialité: <?php echo htmlspecialchars($appointment['specialite']); ?></p>
                        <p>Date: <?php echo date('d/m/Y H:i', strtotime($appointment['date_rdv'])); ?></p>
                        <p>Statut: <?php echo htmlspecialchars($appointment['statut']); ?></p>
                    </div>
                    <div class="appointment-actions">
                        <button class="btn-edit" onclick="editAppointment(<?php echo $appointment['id']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-cancel" onclick="cancelAppointment(<?php echo $appointment['id']; ?>)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Calendar Section -->
        <section class="calendar-section">
            <div class="section-header">
                <h2>Calendrier des rendez-vous</h2>
                <div class="calendar-nav">
                    <button class="prev-month"><i class="fas fa-chevron-left"></i></button>
                    <h3 id="calendar-month"><?php echo date('F Y'); ?></h3>
                    <button class="next-month"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
            <div id='calendar'></div>
        </section>

        <!-- Blood Donation Section -->
        <section class="blood-donation-section">
            <div class="section-header">
                <h2>Don de Sang</h2>
                <button class="new-donation-btn">
                    <i class="fas fa-plus"></i> Nouveau Don
                </button>
            </div>
            <div class="donation-list">
                <?php
                // Récupération des dons de sang
                $sql_donations = "SELECT * FROM blood_donations WHERE user_id = :user_id ORDER BY date_don DESC";
                $stmt_donations = $conn->prepare($sql_donations);
                $stmt_donations->bindParam(':user_id', $user_id);
                $stmt_donations->execute();
                $donations = $stmt_donations->fetchAll(PDO::FETCH_ASSOC);

                foreach($donations as $donation):
                ?>
                <div class="donation-card">
                    <div class="donation-info">
                        <h3>Don de Sang</h3>
                        <p>Groupe sanguin: <?php echo htmlspecialchars($donation['groupe_sanguin']); ?></p>
                        <p>Lieu: <?php echo htmlspecialchars($donation['lieu']); ?></p>
                        <p>Date: <?php echo date('d/m/Y', strtotime($donation['date_don'])); ?></p>
                        <p>Statut: <?php echo htmlspecialchars($donation['statut']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</div> 