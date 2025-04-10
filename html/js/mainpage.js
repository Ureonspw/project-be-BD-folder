document.addEventListener('DOMContentLoaded', function() {
  // Initialisation du calendrier
  var calendarEl = document.getElementById('calendar');
  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    headerToolbar: {
      left: '',
      center: '',
      right: ''
    },
    locale: 'fr',
    events: [
      {
        title: 'Jean Dupont',
        start: '2023-06-15T10:30:00',
        end: '2023-06-15T11:00:00',
        color: '#4CAF50'
      },
      {
        title: 'Marie Martin',
        start: '2023-06-16T14:00:00',
        end: '2023-06-16T14:30:00',
        color: '#2196F3'
      }
    ],
    eventClick: function(info) {
      // Afficher les détails du rendez-vous
      var appointmentDetails = document.getElementById('appointment-details');
      appointmentDetails.innerHTML = `
        <h4>${info.event.title}</h4>
        <p><strong>Date:</strong> ${info.event.start.toLocaleDateString()}</p>
        <p><strong>Heure:</strong> ${info.event.start.toLocaleTimeString()} - ${info.event.end.toLocaleTimeString()}</p>
        <p><strong>Statut:</strong> Confirmé</p>
        <div class="appointment-actions">
          <button class="btn-view-details">Voir détails</button>
          <button class="btn-edit">Modifier</button>
          <button class="btn-cancel">Annuler</button>
        </div>
      `;
    }
  });
  calendar.render();

  // Gestion des onglets
  const tabButtons = document.querySelectorAll('.tab-button');
  const tabContents = document.querySelectorAll('.tab-content');
  
  tabButtons.forEach(button => {
    button.addEventListener('click', () => {
      // Retirer la classe active de tous les boutons et contenus
      tabButtons.forEach(btn => btn.classList.remove('active'));
      tabContents.forEach(content => content.classList.remove('active'));
      
      // Ajouter la classe active au bouton cliqué
      button.classList.add('active');
      
      // Afficher le contenu correspondant
      const tabId = button.getAttribute('data-tab');
      document.getElementById(`${tabId}-tab`).classList.add('active');
    });
  });
  
  // Gestion des vues du calendrier
  const viewOptions = document.querySelectorAll('.view-option');
  viewOptions.forEach(option => {
    option.addEventListener('click', () => {
      viewOptions.forEach(opt => opt.classList.remove('active'));
      option.classList.add('active');
      
      const view = option.getAttribute('data-view');
      calendar.changeView(view);
    });
  });
  
  // Navigation du calendrier
  document.getElementById('prev-month').addEventListener('click', () => {
    calendar.prev();
    updateMonthDisplay(calendar);
  });
  
  document.getElementById('next-month').addEventListener('click', () => {
    calendar.next();
    updateMonthDisplay(calendar);
  });
  
  function updateMonthDisplay(calendar) {
    const currentDate = calendar.getDate();
    const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    document.getElementById('current-month').textContent = `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
  }
  
  // Gestion du popup de connexion
  const loginButton = document.querySelector('.login');
  const popover = document.getElementById('popover');
  
  if (loginButton && popover) {
    loginButton.addEventListener('click', () => {
      popover.togglePopover();
    });
    
    // Fermer le popup si on clique en dehors
    document.addEventListener('click', (e) => {
      if (!popover.contains(e.target) && !loginButton.contains(e.target)) {
        popover.hidePopover();
      }
    });
  }
  
  // Gestion des actions sur les rendez-vous
  document.querySelectorAll('.btn-accept').forEach(button => {
    button.addEventListener('click', function() {
      const appointmentCard = this.closest('.appointment-card');
      if (appointmentCard) {
        appointmentCard.classList.remove('pending');
        appointmentCard.classList.add('completed');
        alert('Rendez-vous accepté !');
      }
    });
  });
  
  document.querySelectorAll('.btn-reject').forEach(button => {
    button.addEventListener('click', function() {
      const appointmentCard = this.closest('.appointment-card');
      if (appointmentCard) {
        appointmentCard.remove();
        alert('Rendez-vous refusé !');
      }
    });
  });
  
  // Gestion du formulaire de prescription
  const prescriptionForm = document.getElementById('prescription-form');
  if (prescriptionForm) {
    prescriptionForm.addEventListener('submit', function(e) {
      e.preventDefault();
      alert('Prescription enregistrée avec succès !');
      this.reset();
    });
  }
  
  // Gestion des médicaments dans la prescription
  const addMedicationButton = document.querySelector('.btn-add-medication');
  const medicationsContainer = document.getElementById('medications-container');
  
  if (addMedicationButton && medicationsContainer) {
    addMedicationButton.addEventListener('click', () => {
      const newEntry = document.createElement('div');
      newEntry.className = 'medication-entry';
      newEntry.innerHTML = `
        <input type="text" placeholder="Nom du médicament" required>
        <input type="text" placeholder="Posologie" required>
        <input type="text" placeholder="Durée du traitement" required>
        <button type="button" class="btn-remove-medication">X</button>
      `;
      medicationsContainer.appendChild(newEntry);
    });
    
    medicationsContainer.addEventListener('click', (e) => {
      if (e.target.classList.contains('btn-remove-medication')) {
        e.target.closest('.medication-entry').remove();
      }
    });
  }
}); 