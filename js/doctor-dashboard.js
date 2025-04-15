document.addEventListener('DOMContentLoaded', function() {
  // Initialiser les onglets des rendez-vous
  initAppointmentTabs();
  
  // Charger les rendez-vous pour chaque onglet
  loadAppointments('en_attente');
  loadAppointments('confirme');
  loadAppointments('termine');
  
  // Initialiser le calendrier
  initCalendar();
  
  // Initialiser les statistiques de don de sang
  initBloodDonationStats();
  
  // Initialiser le formulaire de prescription
  initPrescriptionForm();
  
  // Initialiser le graphique des groupes sanguins
  initBloodTypeChart();
});

// Fonction pour initialiser les onglets des rendez-vous
function initAppointmentTabs() {
  const tabs = document.querySelectorAll('.appointments-tab');
  const tabContents = document.querySelectorAll('.appointment-tab-content');
  
  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => {
      // Retirer la classe active de tous les onglets
      tabs.forEach(t => t.classList.remove('active'));
      tabContents.forEach(c => c.style.display = 'none');
      
      // Ajouter la classe active à l'onglet cliqué
      tab.classList.add('active');
      tabContents[index].style.display = 'block';
      
      // Charger les rendez-vous pour l'onglet sélectionné
      const status = tab.getAttribute('data-tab');
      loadAppointments(status);
    });
  });
  
  // Activer le premier onglet par défaut
  if (tabs.length > 0) {
    tabs[0].classList.add('active');
    tabContents[0].style.display = 'block';
  }
}

// Fonction pour charger les rendez-vous
function loadAppointments(status) {
  const appointmentList = document.querySelector(`#${status}-appointments .appointment-list`);
  if (!appointmentList) return;
  
  // Afficher un indicateur de chargement
  appointmentList.innerHTML = '<div class="loading">Chargement des rendez-vous...</div>';
  
  // Récupérer les rendez-vous depuis le serveur
  fetch(`../php/get_appointments.php?status=${status}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        displayAppointments(data.appointments, status);
      } else {
        appointmentList.innerHTML = `<div class="error">${data.message}</div>`;
      }
    })
    .catch(error => {
      console.error('Erreur lors du chargement des rendez-vous:', error);
      appointmentList.innerHTML = '<div class="error">Erreur lors du chargement des rendez-vous</div>';
    });
}

// Fonction pour afficher les rendez-vous
function displayAppointments(appointments, status) {
  const appointmentList = document.querySelector(`#${status}-appointments .appointment-list`);
  if (!appointmentList) return;
  
  if (appointments.length === 0) {
    appointmentList.innerHTML = '<div class="no-appointments">Aucun rendez-vous trouvé</div>';
    return;
  }
  
  let html = '';
  
  appointments.forEach(appointment => {
    const date = new Date(appointment.date_rdv);
    const formattedDate = date.toLocaleDateString('fr-FR', { 
      weekday: 'long', 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    });
    const formattedTime = date.toLocaleTimeString('fr-FR', { 
      hour: '2-digit', 
      minute: '2-digit' 
    });
    
    html += `
      <div class="appointment-item" data-id="${appointment.id}">
        <div class="appointment-info">
          <h4>Patient: ${appointment.patient_prenom} ${appointment.patient_nom}</h4>
          <p>Date: ${formattedDate} à ${formattedTime}</p>
          <p>Téléphone: ${appointment.patient_telephone}</p>
          <p>Email: ${appointment.patient_email}</p>
          <p>Motif: ${appointment.motif || 'Non spécifié'}</p>
        </div>
        <div class="appointment-actions">
    `;
    
    // Ajouter les boutons d'action en fonction du statut
    if (status === 'en_attente') {
      html += `
          <button class="btn btn-success" onclick="updateAppointmentStatus(${appointment.id}, 'confirme')">Accepter</button>
          <button class="btn btn-danger" onclick="updateAppointmentStatus(${appointment.id}, 'annule')">Refuser</button>
      `;
    } else if (status === 'confirme') {
      html += `
          <button class="btn btn-primary" onclick="openPrescriptionPopup(${appointment.id})">Ajouter prescription</button>
          <button class="btn btn-success" onclick="updateAppointmentStatus(${appointment.id}, 'termine')">Terminer</button>
      `;
    } else if (status === 'termine') {
      html += `
          <button class="btn btn-info" onclick="viewPrescription(${appointment.id})">Voir prescription</button>
      `;
    }
    
    html += `
        </div>
      </div>
    `;
  });
  
  appointmentList.innerHTML = html;
}

// Fonction pour mettre à jour le statut d'un rendez-vous
function updateAppointmentStatus(appointmentId, status) {
  if (!confirm('Êtes-vous sûr de vouloir modifier le statut de ce rendez-vous ?')) {
    return;
  }
  
  const formData = new FormData();
  formData.append('appointment_id', appointmentId);
  formData.append('status', status);

  fetch('../php/update_appointment.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert(data.message);
      // Recharger les rendez-vous pour tous les onglets
      loadAppointments('en_attente');
      loadAppointments('confirme');
      loadAppointments('termine');

      // Si le rendez-vous est accepté, remplir le formulaire d'email
      if (status === 'confirme') {
        // Récupérer les détails du rendez-vous directement par son ID
        fetch(`../php/get_appointment_details.php?id=${appointmentId}`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              mailPatient(data.appointment);
            } else {
              console.error('Erreur lors de la récupération des détails du rendez-vous:', data.message);
            }
          })
          .catch(error => {
            console.error('Erreur lors de la récupération des détails du rendez-vous:', error);
          });
      }
    } else {
      alert(data.message);
    }
  })
  .catch(error => {
    console.error('Erreur lors de la mise à jour du statut:', error);
    alert('Erreur lors de la mise à jour du statut du rendez-vous');
  });
}

// Fonction pour initialiser le calendrier
function initCalendar() {
  const calendarContainer = document.querySelector('.calendar-grid');
  if (!calendarContainer) return;
  
  // Obtenir la date actuelle
  const today = new Date();
  const currentMonth = today.getMonth();
  const currentYear = today.getFullYear();
  
  // Variables pour suivre l'état du calendrier
  let currentView = 'month'; // 'month' ou 'week'
  let currentDate = new Date(currentYear, currentMonth, 1);
  
  // Générer le calendrier pour le mois actuel
  generateCalendar(currentDate, currentView);
  
  // Ajouter les événements pour la navigation du calendrier
  const prevMonthBtn = document.querySelector('.prev-month');
  const nextMonthBtn = document.querySelector('.next-month');
  const monthYearDisplay = document.querySelector('.month-year');
  const weekViewBtn = document.querySelector('.calendar-view-options .btn:first-child');
  const monthViewBtn = document.querySelector('.calendar-view-options .btn:last-child');
  
  if (prevMonthBtn && nextMonthBtn && monthYearDisplay) {
    prevMonthBtn.addEventListener('click', () => {
      if (currentView === 'month') {
        // Navigation par mois
        currentDate.setMonth(currentDate.getMonth() - 1);
      } else {
        // Navigation par semaine
        currentDate.setDate(currentDate.getDate() - 7);
      }
      generateCalendar(currentDate, currentView);
      updateMonthYearDisplay(currentDate, currentView);
    });
    
    nextMonthBtn.addEventListener('click', () => {
      if (currentView === 'month') {
        // Navigation par mois
        currentDate.setMonth(currentDate.getMonth() + 1);
      } else {
        // Navigation par semaine
        currentDate.setDate(currentDate.getDate() + 7);
      }
      generateCalendar(currentDate, currentView);
      updateMonthYearDisplay(currentDate, currentView);
    });
    
    // Ajouter les événements pour les boutons de vue
    if (weekViewBtn && monthViewBtn) {
      weekViewBtn.addEventListener('click', () => {
        currentView = 'week';
        weekViewBtn.classList.add('active');
        monthViewBtn.classList.remove('active');
        generateCalendar(currentDate, currentView);
        updateMonthYearDisplay(currentDate, currentView);
      });
      
      monthViewBtn.addEventListener('click', () => {
        currentView = 'month';
        monthViewBtn.classList.add('active');
        weekViewBtn.classList.remove('active');
        generateCalendar(currentDate, currentView);
        updateMonthYearDisplay(currentDate, currentView);
      });
    }
    
    updateMonthYearDisplay(currentDate, currentView);
  }
}

// Fonction pour générer le calendrier
function generateCalendar(date, view) {
  const calendarGrid = document.querySelector('.calendar-grid');
  if (!calendarGrid) return;
  
  // Vider le calendrier existant
  calendarGrid.innerHTML = '';
  
  if (view === 'month') {
    generateMonthView(date);
  } else {
    generateWeekView(date);
  }
}

// Fonction pour générer la vue mensuelle
function generateMonthView(date) {
  const calendarGrid = document.querySelector('.calendar-grid');
  if (!calendarGrid) return;
  
  // Ajouter les en-têtes des jours de la semaine
  const daysOfWeek = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
  daysOfWeek.forEach(day => {
    const dayHeader = document.createElement('div');
    dayHeader.className = 'calendar-day-header';
    dayHeader.textContent = day;
    calendarGrid.appendChild(dayHeader);
  });
  
  // Obtenir le premier jour du mois
  const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
  const startingDay = firstDay.getDay();
  
  // Obtenir le nombre de jours dans le mois
  const daysInMonth = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
  
  // Ajouter les jours vides au début
  for (let i = 0; i < startingDay; i++) {
    const emptyDay = document.createElement('div');
    emptyDay.className = 'calendar-day empty';
    calendarGrid.appendChild(emptyDay);
  }
  
  // Ajouter les jours du mois
  const today = new Date();
  for (let day = 1; day <= daysInMonth; day++) {
    const dayElement = document.createElement('div');
    dayElement.className = 'calendar-day';
    
    // Vérifier si c'est aujourd'hui
    if (day === today.getDate() && 
        date.getMonth() === today.getMonth() && 
        date.getFullYear() === today.getFullYear()) {
      dayElement.classList.add('today');
    }
    
    // Ajouter le numéro du jour
    const dayNumber = document.createElement('div');
    dayNumber.className = 'calendar-day-number';
    dayNumber.textContent = day;
    dayElement.appendChild(dayNumber);
    
    // Charger les rendez-vous pour ce jour
    const currentDate = new Date(date.getFullYear(), date.getMonth(), day);
    loadAppointmentsForDay(dayElement, currentDate);
    
    calendarGrid.appendChild(dayElement);
  }
}

// Fonction pour générer la vue hebdomadaire
function generateWeekView(date) {
  const calendarGrid = document.querySelector('.calendar-grid');
  if (!calendarGrid) return;
  
  // Ajouter les en-têtes des jours de la semaine
  const daysOfWeek = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
  daysOfWeek.forEach(day => {
    const dayHeader = document.createElement('div');
    dayHeader.className = 'calendar-day-header';
    dayHeader.textContent = day;
    calendarGrid.appendChild(dayHeader);
  });
  
  // Trouver le premier jour de la semaine (dimanche)
  const firstDayOfWeek = new Date(date);
  firstDayOfWeek.setDate(date.getDate() - date.getDay());
  
  // Ajouter les jours de la semaine
  const today = new Date();
  for (let i = 0; i < 7; i++) {
    const currentDate = new Date(firstDayOfWeek);
    currentDate.setDate(firstDayOfWeek.getDate() + i);
    
    const dayElement = document.createElement('div');
    dayElement.className = 'calendar-day';
    
    // Vérifier si c'est aujourd'hui
    if (currentDate.getDate() === today.getDate() && 
        currentDate.getMonth() === today.getMonth() && 
        currentDate.getFullYear() === today.getFullYear()) {
      dayElement.classList.add('today');
    }
    
    // Ajouter le numéro du jour
    const dayNumber = document.createElement('div');
    dayNumber.className = 'calendar-day-number';
    dayNumber.textContent = currentDate.getDate();
    dayElement.appendChild(dayNumber);
    
    // Charger les rendez-vous pour ce jour
    loadAppointmentsForDay(dayElement, currentDate);
    
    calendarGrid.appendChild(dayElement);
  }
}

// Fonction pour charger les rendez-vous pour un jour spécifique
function loadAppointmentsForDay(dayElement, date) {
  // Formater la date au format YYYY-MM-DD
  const formattedDate = date.toISOString().split('T')[0];
  
  // Récupérer les rendez-vous pour cette date
  fetch(`../php/get_appointments_by_date.php?date=${formattedDate}`)
    .then(response => response.json())
    .then(data => {
      if (data.success && data.appointments.length > 0) {
        dayElement.classList.add('has-appointment');
        
        // Limiter à 3 rendez-vous affichés
        const appointmentsToShow = data.appointments.slice(0, 3);
        
        appointmentsToShow.forEach(appointment => {
          const time = new Date(appointment.date_rdv).toLocaleTimeString('fr-FR', { 
            hour: '2-digit', 
            minute: '2-digit' 
          });
          
          const event = document.createElement('div');
          event.className = 'calendar-event';
          event.textContent = `${time} - ${appointment.patient_prenom} ${appointment.patient_nom}`;
          dayElement.appendChild(event);
        });
        
        // Ajouter un indicateur si plus de 3 rendez-vous
        if (data.appointments.length > 3) {
          const moreIndicator = document.createElement('div');
          moreIndicator.className = 'calendar-more';
          moreIndicator.textContent = `+${data.appointments.length - 3} autres`;
          dayElement.appendChild(moreIndicator);
        }
      }
    })
    .catch(error => {
      console.error('Erreur lors du chargement des rendez-vous pour le jour:', error);
    });
}

// Fonction pour mettre à jour l'affichage du mois et de l'année
function updateMonthYearDisplay(date, view) {
  const monthYearDisplay = document.querySelector('.month-year');
  if (!monthYearDisplay) return;
  
  const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
  
  if (view === 'month') {
    monthYearDisplay.textContent = `${months[date.getMonth()]} ${date.getFullYear()}`;
  } else {
    // Pour la vue semaine, afficher la plage de dates
    const firstDayOfWeek = new Date(date);
    firstDayOfWeek.setDate(date.getDate() - date.getDay());
    
    const lastDayOfWeek = new Date(firstDayOfWeek);
    lastDayOfWeek.setDate(firstDayOfWeek.getDate() + 6);
    
    const formatDate = (date) => {
      return `${date.getDate()} ${months[date.getMonth()]}`;
    };
    
    monthYearDisplay.textContent = `${formatDate(firstDayOfWeek)} - ${formatDate(lastDayOfWeek)} ${date.getFullYear()}`;
  }
}

// Fonction pour initialiser les statistiques de don de sang
function initBloodDonationStats() {
  fetch('php/get_blood_type_stats.php')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        updateBloodDonationStats(data.stats);
        updateBloodTypeChart(data.bloodTypes);
      } else {
        console.error('Erreur lors du chargement des statistiques:', data.message);
      }
    })
    .catch(error => {
      console.error('Erreur lors de la récupération des statistiques:', error);
    });
}

// Fonction pour mettre à jour les statistiques de don de sang
function updateBloodDonationStats(stats) {
  // Mettre à jour les compteurs
  const pendingRequestsElement = document.querySelector('.stat-value[data-stat="pending-requests"]');
  const completedDonationsElement = document.querySelector('.stat-value[data-stat="completed-donations"]');
  const totalDonationsElement = document.querySelector('.stat-value[data-stat="total-donations"]');
  
  if (pendingRequestsElement) pendingRequestsElement.textContent = stats.pendingRequests;
  if (completedDonationsElement) completedDonationsElement.textContent = stats.completedDonations;
  if (totalDonationsElement) totalDonationsElement.textContent = stats.totalDonations;
}

// Fonction pour initialiser le graphique des groupes sanguins
function initBloodTypeChart() {
  const ctx = document.getElementById('bloodTypeChart');
  if (!ctx) return;
  
  // Créer le graphique avec des données vides initialement
  window.bloodTypeChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
      datasets: [{
        label: 'Nombre de dons par groupe sanguin',
        data: [0, 0, 0, 0, 0, 0, 0, 0],
        backgroundColor: [
          '#FF6384', // A+
          '#FF9F40', // A-
          '#FFCD56', // B+
          '#4BC0C0', // B-
          '#36A2EB', // AB+
          '#9966FF', // AB-
          '#FF6384', // O+
          '#C9CBCF'  // O-
        ],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0
          }
        }
      },
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return `${context.parsed.y} dons`;
            }
          }
        }
      }
    }
  });
}

// Fonction pour mettre à jour le graphique des groupes sanguins
function updateBloodTypeChart(bloodTypes) {
  if (!window.bloodTypeChart) return;
  
  // Mettre à jour les données du graphique
  window.bloodTypeChart.data.datasets[0].data = [
    bloodTypes['A+'],
    bloodTypes['A-'],
    bloodTypes['B+'],
    bloodTypes['B-'],
    bloodTypes['AB+'],
    bloodTypes['AB-'],
    bloodTypes['O+'],
    bloodTypes['O-']
  ];
  
  // Mettre à jour le graphique
  window.bloodTypeChart.update();
}

// Fonction pour initialiser le formulaire de prescription
function initPrescriptionForm() {
  const prescriptionForm = document.querySelector('.prescription-form');
  if (!prescriptionForm) return;
  
  prescriptionForm.addEventListener('submit', function(e) {
    e.preventDefault();
    savePrescription();
  });
}

// Fonction pour ouvrir la popup de prescription
function openPrescriptionPopup(appointmentId) {
  // Récupérer les informations du rendez-vous
  fetch(`../php/get_appointments.php?id=${appointmentId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success && data.appointments.length > 0) {
        const appointment = data.appointments[0];
        
        // Remplir le formulaire avec les informations du patient
        document.getElementById('patient-name').value = `${appointment.patient_prenom} ${appointment.patient_nom}`;
        
        // Vérifier si un dossier médical existe déjà
        fetch(`../php/get_medical_record.php?appointment_id=${appointmentId}`)
          .then(response => response.json())
          .then(recordData => {
            if (recordData.success && recordData.exists) {
              // Pré-remplir le formulaire avec les données existantes
              document.getElementById('diagnosis').value = recordData.medical_record.diagnostic || '';
              document.getElementById('prescription').value = recordData.medical_record.prescription || '';
              document.getElementById('notes').value = recordData.medical_record.notes || '';
            } else {
              // Réinitialiser le formulaire
              document.getElementById('diagnosis').value = '';
              document.getElementById('prescription').value = '';
              document.getElementById('notes').value = '';
            }
            
            // Stocker l'ID du rendez-vous pour la sauvegarde
            document.getElementById('appointment-id').value = appointmentId;
            
            // Afficher le formulaire
            document.querySelector('.prescription-section').style.display = 'block';
          })
          .catch(error => {
            console.error('Erreur lors de la récupération du dossier médical:', error);
            alert('Erreur lors de la récupération du dossier médical');
          });
      } else {
        alert('Rendez-vous non trouvé');
      }
    })
    .catch(error => {
      console.error('Erreur lors de la récupération du rendez-vous:', error);
      alert('Erreur lors de la récupération du rendez-vous');
    });
}

// Fonction pour sauvegarder la prescription
function savePrescription() {
  const appointmentId = document.getElementById('appointment-id').value;
  const diagnosis = document.getElementById('diagnosis').value;
  const prescription = document.getElementById('prescription').value;
  const notes = document.getElementById('notes').value;
  
  if (!appointmentId || !diagnosis || !prescription) {
    alert('Veuillez remplir tous les champs obligatoires');
    return;
  }
  
  const formData = new FormData();
  formData.append('appointment_id', appointmentId);
  formData.append('diagnostic', diagnosis);
  formData.append('prescription', prescription);
  formData.append('notes', notes);
  
  fetch('../php/add_medical_record.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert(data.message);
      // Recharger les rendez-vous
      loadAppointments('en_attente');
      loadAppointments('confirme');
      loadAppointments('termine');
      
      // Masquer le formulaire
      document.querySelector('.prescription-section').style.display = 'none';
    } else {
      alert(data.message);
    }
  })
  .catch(error => {
    console.error('Erreur lors de la sauvegarde de la prescription:', error);
    alert('Erreur lors de la sauvegarde de la prescription');
  });
}

// Fonction pour voir la prescription
function viewPrescription(appointmentId) {
  fetch(`../php/get_medical_record.php?appointment_id=${appointmentId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success && data.exists) {
        const record = data.medical_record;
        
        // Créer une popup pour afficher la prescription
        const popup = document.createElement('div');
        popup.className = 'prescription-popup';
        popup.innerHTML = `
          <div class="prescription-popup-content">
            <h3>Dossier médical</h3>
            <div class="prescription-details">
              <p><strong>Diagnostic:</strong> ${record.diagnostic}</p>
              <p><strong>Prescription:</strong> ${record.prescription}</p>
              <p><strong>Notes:</strong> ${record.notes || 'Aucune note'}</p>
              <p><strong>Date:</strong> ${new Date(record.date_consultation).toLocaleString('fr-FR')}</p>
            </div>
            <button class="btn btn-primary" onclick="this.parentElement.parentElement.remove()">Fermer</button>
          </div>
        `;
        
        document.body.appendChild(popup);
      } else {
        alert('Aucun dossier médical trouvé pour ce rendez-vous');
      }
    })
    .catch(error => {
      console.error('Erreur lors de la récupération du dossier médical:', error);
      alert('Erreur lors de la récupération du dossier médical');
    });
}

// Fonction pour accepter un don de sang
function acceptBloodDonation(donationId) {
  if (!confirm('Êtes-vous sûr de vouloir accepter ce don de sang ?')) {
    return;
  }
  
  // Simuler l'acceptation du don de sang
  alert('Don de sang accepté avec succès');
  
  // Mettre à jour l'interface
  const donationItem = document.querySelector(`.donation-item[data-donation-id="${donationId}"]`);
  if (donationItem) {
    donationItem.remove();
  }
  
  // Mettre à jour les statistiques
  const pendingRequestsElement = document.querySelector('.stat-value[data-stat="pending-requests"]');
  const completedDonationsElement = document.querySelector('.stat-value[data-stat="completed-donations"]');
  const totalDonationsElement = document.querySelector('.stat-value[data-stat="total-donations"]');
  
  if (pendingRequestsElement) {
    const currentValue = parseInt(pendingRequestsElement.textContent);
    pendingRequestsElement.textContent = Math.max(0, currentValue - 1);
  }
  
  if (completedDonationsElement) {
    const currentValue = parseInt(completedDonationsElement.textContent);
    completedDonationsElement.textContent = currentValue + 1;
  }
  
  if (totalDonationsElement) {
    const currentValue = parseInt(totalDonationsElement.textContent);
    totalDonationsElement.textContent = currentValue + 1;
  }
}

// Fonction pour refuser un don de sang
function rejectBloodDonation(donationId) {
  if (!confirm('Êtes-vous sûr de vouloir refuser ce don de sang ?')) {
    return;
  }
  
  // Simuler le refus du don de sang
  alert('Don de sang refusé');
  
  // Mettre à jour l'interface
  const donationItem = document.querySelector(`.donation-item[data-donation-id="${donationId}"]`);
  if (donationItem) {
    donationItem.remove();
  }
  
  // Mettre à jour les statistiques
  const pendingRequestsElement = document.querySelector('.stat-value[data-stat="pending-requests"]');
  
  if (pendingRequestsElement) {
    const currentValue = parseInt(pendingRequestsElement.textContent);
    pendingRequestsElement.textContent = Math.max(0, currentValue - 1);
  }
}

// Fonction pour rafraîchir les statistiques des groupes sanguins
function refreshBloodStats() {
  const refreshBtn = document.querySelector('.refresh-btn');
  if (refreshBtn) {
    refreshBtn.disabled = true;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Chargement...';
  }
  
  fetch('php/get_blood_type_stats.php')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        updateBloodDonationStats(data.stats);
        updateBloodTypeChart(data.bloodTypes);
        
        // Afficher une notification de succès
        showNotification('Statistiques mises à jour avec succès', 'success');
      } else {
        showNotification('Erreur lors de la mise à jour des statistiques', 'error');
        console.error('Erreur lors du chargement des statistiques:', data.message);
      }
    })
    .catch(error => {
      showNotification('Erreur lors de la mise à jour des statistiques', 'error');
      console.error('Erreur lors de la récupération des statistiques:', error);
    })
    .finally(() => {
      if (refreshBtn) {
        refreshBtn.disabled = false;
        refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Rafraîchir';
      }
    });
}

// Fonction pour afficher une notification
function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.className = `notification ${type}`;
  notification.textContent = message;
  
  document.body.appendChild(notification);
  
  // Supprimer la notification après 3 secondes
  setTimeout(() => {
    notification.remove();
  }, 3000);
}

function sendEmail() {
  const recipient = document.getElementById('email-recipient').value;
  const subject = document.getElementById('email-subject').value;
  const content = document.getElementById('email-content').value;

  if (!recipient || !subject || !content) {
    alert('Veuillez remplir tous les champs');
    return;
  }

  // Créer un objet FormData pour envoyer les données
  const formData = new FormData();
  formData.append('recipient', recipient);
  formData.append('subject', subject);
  formData.append('content', content);

  // Envoyer la requête au serveur
  fetch('send_email.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Email envoyé avec succès!');
      // Réinitialiser le formulaire
      document.getElementById('email-recipient').value = '';
      document.getElementById('email-subject').value = '';
      document.getElementById('email-content').value = '';
    } else {
      alert('Erreur lors de l\'envoi de l\'email: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Une erreur est survenue lors de l\'envoi de l\'email');
  });
}

try {
    emailjs.init("OFTyJSgF1HubqBCmk");
    document.getElementById('status').innerHTML = "EmailJS est prêt";
    document.getElementById('status').style.color = "green";
} catch (error) {
    document.getElementById('status').innerHTML = "Erreur: " + error.message;
    document.getElementById('status').style.color = "red";
}

function sendEmail() {
    if (typeof emailjs === 'undefined') {
        document.getElementById('status').innerHTML = "Erreur: EmailJS n'est pas chargé";
        document.getElementById('status').style.color = "red";
        return;
    }

    var statusDiv = document.getElementById('status');
    statusDiv.innerHTML = "Envoi en cours...";
    statusDiv.style.color = "blue";

    var params = {
        sender_name: "mediconnect", 
        to: document.getElementById('email-recipient').value,
        subject: document.getElementById('email-subject').value,
        replyto: "ureon206@gmail.com",
        message: document.getElementById('email-content').value,
    };
    
    var service_id = "service_tmdjkv7";
    var template_id = "template_y5k8m1p";
    
    emailjs.send(service_id, template_id, params)
        .then(function(response) {
            statusDiv.innerHTML = "Email envoyé avec succès!";
            statusDiv.style.color = "green";
            console.log('SUCCESS!', response.status, response.text);
        })
        .catch(function(error) {
            statusDiv.innerHTML = "Erreur lors de l'envoi: " + error.text;
            statusDiv.style.color = "red";
            console.log('FAILED...', error);
        });
}

// Fonction pour remplir et afficher le formulaire d'email
function mailPatient(appointment) {
    if (!appointment || !appointment.patient_email) {
        console.error('Informations du rendez-vous manquantes');
        return;
    }

    const date = new Date(appointment.date_rdv);
    const formattedDate = date.toLocaleDateString('fr-FR', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    const formattedTime = date.toLocaleTimeString('fr-FR', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });

    // Remplir les champs du formulaire d'email
    const recipientInput = document.getElementById('email-recipient');
    const subjectInput = document.getElementById('email-subject');
    const contentInput = document.getElementById('email-content');

    if (recipientInput && subjectInput && contentInput) {
        recipientInput.value = appointment.patient_email;
        subjectInput.value = `Confirmation de votre rendez-vous du ${formattedDate}`;
        contentInput.value = `Bonjour ${appointment.patient_prenom} ${appointment.patient_nom},\n\nJe vous confirme que votre rendez-vous du ${formattedDate} à ${formattedTime} a été accepté.\n\nN'oubliez pas de vous présenter 10 minutes avant l'heure du rendez-vous.\n\nCordialement,\nDr. ${appointment.doctor_prenom} ${appointment.doctor_nom}`;

        // Faire défiler jusqu'à la section email
        const emailSection = document.querySelector('.dashboard-card.email-section');
        if (emailSection) {
            emailSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    } else {
        console.error('Champs du formulaire d\'email non trouvés');
    }
}
