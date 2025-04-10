// Fonction pour charger les dons de sang
function loadBloodDonations() {
    fetch('afficher_dons_sang_doctor.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDonationStats(data.dons);
                displayDonations(data.dons);
                updateBloodTypeChart(data.dons);
            } else {
                console.error('Erreur:', data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
}

// Fonction pour mettre à jour les statistiques
function updateDonationStats(dons) {
    const pendingRequests = dons.filter(don => don.statut === 'en_attente').length;
    const completedDonations = dons.filter(don => don.statut === 'termine').length;
    const totalDonations = dons.length;

    document.querySelector('[data-stat="pending-requests"]').textContent = pendingRequests;
    document.querySelector('[data-stat="completed-donations"]').textContent = completedDonations;
    document.querySelector('[data-stat="total-donations"]').textContent = totalDonations;
}

// Fonction pour afficher les dons
function displayDonations(dons) {
    const donationList = document.querySelector('.donation-list');
    donationList.innerHTML = '';

    // Filtrer les dons en attente
    const pendingDonations = dons.filter(don => don.statut === 'en_attente');

    if (pendingDonations.length === 0) {
        donationList.innerHTML = '<div class="no-donations">Aucune demande de don en attente</div>';
        return;
    }

    pendingDonations.forEach(don => {
        const donationItem = document.createElement('div');
        donationItem.className = 'donation-item';
        donationItem.dataset.donationId = don.id;

        donationItem.innerHTML = `
            <div class="donation-info">
                <h4>Donneur: ${don.prenom} ${don.nom}</h4>
                <p>Groupe sanguin: ${don.groupe_sanguin}</p>
                <p>Date demandée: ${don.date} à ${don.heure}</p>
                <p>Lieu: ${don.lieu}</p>
                <p>Téléphone: ${don.telephone}</p>
                <p>Email: ${don.email}</p>
                <span class="donation-status">En attente</span>
            </div>
            <div class="donation-actions">
                <button class="btn btn-success" onclick="handleDonationAction(${don.id}, 'accepter')">Accepter</button>
                <button class="btn btn-danger" onclick="handleDonationAction(${don.id}, 'refuser')">Refuser</button>
            </div>
        `;

        donationList.appendChild(donationItem);
    });
}

// Fonction pour gérer les actions sur les dons
function handleDonationAction(donId, action) {
    const formData = new FormData();
    formData.append('don_id', donId);
    formData.append('action', action);

    fetch('traitement_don_sang_doctor.php', {
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
        console.error('Erreur:', error);
        alert('Une erreur est survenue');
    });
}

// Fonction pour mettre à jour le graphique des groupes sanguins
function updateBloodTypeChart(dons) {
    const bloodTypes = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
    const counts = {};

    // Initialiser les compteurs
    bloodTypes.forEach(type => {
        counts[type] = 0;
    });

    // Compter les dons par groupe sanguin
    dons.forEach(don => {
        if (counts.hasOwnProperty(don.groupe_sanguin)) {
            counts[don.groupe_sanguin]++;
        }
    });

    // Créer le graphique
    const ctx = document.getElementById('bloodTypeChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: bloodTypes,
            datasets: [{
                label: 'Nombre de dons par groupe sanguin',
                data: bloodTypes.map(type => counts[type]),
                backgroundColor: 'rgba(231, 76, 60, 0.7)',
                borderColor: 'rgba(231, 76, 60, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// Charger les dons de sang au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    loadBloodDonations();
    
    // Recharger les dons toutes les 5 minutes
    setInterval(loadBloodDonations, 300000);
}); 