// Fonction pour charger les patients
async function loadPatients() {
    try {
        console.log('Début du chargement des patients');
        
        // Récupérer l'ID du docteur depuis un attribut data
        const doctorId = document.body.getAttribute('data-doctor-id');
        console.log('Doctor ID récupéré:', doctorId);
        
        if (!doctorId) {
            throw new Error('ID du docteur non trouvé');
        }

        console.log('Envoi de la requête à get_patients.php');
        const response = await fetch('../../php/get_patients.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                doctor_id: doctorId
            })
        });
        
        console.log('Réponse reçue, status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Données reçues:', data);
        
        if (data.success) {
            console.log('Affichage des patients:', data.patients);
            displayPatients(data.patients);
        } else {
            console.error('Erreur lors du chargement des patients:', data.message);
            showNotification('Erreur lors du chargement des patients', 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Erreur de connexion au serveur', 'error');
    }
}

// Fonction pour afficher les patients
function displayPatients(patients) {
    const patientsList = document.querySelector('.patients-list');
    if (!patientsList) {
        console.error('Element .patients-list non trouvé');
        return;
    }
    
    patientsList.innerHTML = '';

    if (patients.length === 0) {
        patientsList.innerHTML = '<div class="no-patients">Aucun patient trouvé</div>';
        return;
    }

    patients.forEach(patient => {
        const patientCard = document.createElement('div');
        patientCard.className = 'patient-card';
        
        // Formater la date du prochain rendez-vous
        let nextAppointment = 'Aucun';
        if (patient.next_appointment) {
            const date = new Date(patient.next_appointment);
            nextAppointment = date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        patientCard.innerHTML = `
            <h4>${patient.prenom} ${patient.nom}</h4>
            <div class="patient-info">
                <p><i class="fas fa-envelope"></i> ${patient.email}</p>
                <p><i class="fas fa-phone"></i> ${patient.telephone}</p>
                <p><i class="fas fa-calendar"></i> Prochain RDV: ${nextAppointment}</p>
            </div>
            <button class="view-more-btn" onclick="showPatientDetails(${patient.id})">
                Voir plus
            </button>
        `;
        
        patientsList.appendChild(patientCard);
    });
}

// Fonction pour afficher les détails d'un patient
async function showPatientDetails(patientId) {
    try {
        const response = await fetch('../../php/get_patient_details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                patient_id: patientId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayPatientDetails(data.patient);
        } else {
            console.error('Erreur lors du chargement des détails:', data.message);
            showNotification('Erreur lors du chargement des détails du patient', 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Erreur de connexion au serveur', 'error');
    }
}

// Fonction pour afficher les détails dans le popup
function displayPatientDetails(patient) {
    const popup = document.getElementById('patientDetailsPopup');
    if (!popup) {
        console.error('Element #patientDetailsPopup non trouvé');
        return;
    }
    
    const detailsContainer = popup.querySelector('.patient-details');
    if (!detailsContainer) {
        console.error('Element .patient-details non trouvé');
        return;
    }
    
    // Formater la date de naissance
    let birthDate = 'Non spécifié';
    if (patient.date_naissance) {
        const date = new Date(patient.date_naissance);
        birthDate = date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
    
    detailsContainer.innerHTML = `
        <h3>Informations Personnelles</h3>
        <div class="detail-group">
            <h4>Nom Complet</h4>
            <p>${patient.prenom} ${patient.nom}</p>
        </div>
        <div class="detail-group">
            <h4>Email</h4>
            <p>${patient.email}</p>
        </div>
        <div class="detail-group">
            <h4>Téléphone</h4>
            <p>${patient.telephone}</p>
        </div>
        <div class="detail-group">
            <h4>Date de Naissance</h4>
            <p>${birthDate}</p>
        </div>
        
        <h3>Informations Médicales</h3>
        <div class="detail-group">
            <h4>Groupe Sanguin</h4>
            <p>${patient.groupe_sanguin || 'Non spécifié'}</p>
        </div>
        <div class="detail-group">
            <h4>Poids</h4>
            <p>${patient.poids ? patient.poids + ' kg' : 'Non spécifié'}</p>
        </div>
        <div class="detail-group">
            <h4>Taille</h4>
            <p>${patient.taille ? patient.taille + ' cm' : 'Non spécifié'}</p>
        </div>
        <div class="detail-group">
            <h4>Fumeur</h4>
            <p>${patient.fumeur || 'Non spécifié'}</p>
        </div>
        
        <h3>Antécédents Médicaux</h3>
        <div class="detail-group">
            <h4>Allergies</h4>
            <p>${patient.allergies || 'Aucune'}</p>
        </div>
        <div class="detail-group">
            <h4>Maladies Chroniques</h4>
            <p>${patient.maladies_chroniques || 'Aucune'}</p>
        </div>
        <div class="detail-group">
            <h4>Médicaments</h4>
            <p>${patient.medicaments || 'Aucun'}</p>
        </div>
        
        <h3>Personne à Contacter en Cas d'Urgence</h3>
        <div class="detail-group">
            <h4>Nom</h4>
            <p>${patient.urgence_nom || 'Non spécifié'}</p>
        </div>
        <div class="detail-group">
            <h4>Relation</h4>
            <p>${patient.urgence_relation || 'Non spécifié'}</p>
        </div>
        <div class="detail-group">
            <h4>Téléphone</h4>
            <p>${patient.urgence_telephone || 'Non spécifié'}</p>
        </div>
    `;
    
    popup.style.display = 'flex';
}

// Fonction pour afficher une notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Gestionnaire d'événements pour fermer le popup
document.addEventListener('DOMContentLoaded', () => {
    const closePopup = document.querySelector('.close-popup');
    if (closePopup) {
        closePopup.addEventListener('click', () => {
            const popup = document.getElementById('patientDetailsPopup');
            if (popup) {
                popup.style.display = 'none';
            }
        });
    }
    
    // Charger les patients au chargement de la page
    loadPatients();
}); 