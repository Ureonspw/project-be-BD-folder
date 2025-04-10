// Fonction pour éditer un rendez-vous
function editAppointment(appointmentId) {
    // Récupérer les informations du rendez-vous via AJAX
    fetch(`get_appointment.php?id=${appointmentId}`)
        .then(response => response.json())
        .then(data => {
            // Remplir le formulaire avec les données
            document.getElementById('doctor_id').value = data.doctor_id;
            document.getElementById('date_rdv').value = data.date_rdv;
            document.getElementById('motif').value = data.motif;
            
            // Afficher le formulaire
            document.querySelector('.new-appointment-form').style.display = 'block';
            document.querySelector('.new-appointment-form h2').textContent = 'Modifier le Rendez-vous';
            
            // Ajouter l'ID du rendez-vous au formulaire
            const form = document.querySelector('.new-appointment-form form');
            form.action = `update_appointment.php?id=${appointmentId}`;
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la récupération des informations du rendez-vous');
        });
}

// Fonction pour annuler un rendez-vous
function cancelAppointment(appointmentId) {
    if(confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')) {
        fetch(`cancel_appointment.php?id=${appointmentId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Supprimer la carte du rendez-vous de l'interface
                const appointmentCard = document.querySelector(`[data-appointment-id="${appointmentId}"]`);
                if(appointmentCard) {
                    appointmentCard.remove();
                }
                alert('Rendez-vous annulé avec succès');
            } else {
                alert('Erreur lors de l\'annulation du rendez-vous');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de l\'annulation du rendez-vous');
        });
    }
}

// Validation du formulaire de rendez-vous
document.addEventListener('DOMContentLoaded', function() {
    const appointmentForm = document.querySelector('.new-appointment-form form');
    if(appointmentForm) {
        appointmentForm.addEventListener('submit', function(e) {
            const dateRdv = new Date(document.getElementById('date_rdv').value);
            const now = new Date();
            
            if(dateRdv < now) {
                e.preventDefault();
                alert('La date du rendez-vous doit être dans le futur');
            }
        });
    }
});

// Initialisation du calendrier
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    if(calendarEl) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'fr',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: 'get_appointments_calendar.php',
            eventClick: function(info) {
                editAppointment(info.event.id);
            }
        });
        calendar.render();
    }
}); 