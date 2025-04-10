// Fonction pour charger les statistiques
async function loadDonationStats() {
    try {
        const response = await fetch('../../html/php/blood_donation_handler.php?action=get_stats');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        
        if (data.error) {
            console.error('Erreur:', data.error);
            return;
        }
        
        // Mettre à jour les statistiques
        document.querySelector('[data-stat="pending-requests"]').textContent = data.pending_requests;
        document.querySelector('[data-stat="completed-donations"]').textContent = data.completed_donations;
        document.querySelector('[data-stat="total-donations"]').textContent = data.total_donations;
        
        // Mettre à jour le graphique des groupes sanguins
        updateBloodTypeChart(data.blood_types);
    } catch (error) {
        console.error('Erreur lors du chargement des statistiques:', error);
    }
}

// Fonction pour charger les demandes en attente
async function loadPendingRequests() {
    try {
        const response = await fetch('../../html/php/blood_donation_handler.php?action=get_pending_requests');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const requests = await response.json();
        
        if (requests.error) {
            console.error('Erreur:', requests.error);
            return;
        }
        
        const donationList = document.querySelector('.donation-list');
        donationList.innerHTML = '';
        
        if (requests.length === 0) {
            donationList.innerHTML = '<p>Aucune demande en attente</p>';
            return;
        }
        
        requests.forEach(request => {
            const donationItem = document.createElement('div');
            donationItem.className = 'donation-item';
            donationItem.dataset.donationId = request.id;
            
            donationItem.innerHTML = `
                <div class="donation-info">
                    <h4>Donneur: ${request.donor_name}</h4>
                    <p>Groupe sanguin: ${request.blood_type}</p>
                    <p>Lieu: ${request.location}</p>
                    <p>Date demandée: ${new Date(request.requested_date).toLocaleDateString()}</p>
                    <span class="donation-status">${request.status}</span>
                </div>
                <div class="donation-actions">
                    <button class="btn btn-success" onclick="updateDonationStatus(${request.id}, 'confirme')">Accepter</button>
                    <button class="btn btn-danger" onclick="updateDonationStatus(${request.id}, 'annule')">Refuser</button>
                </div>
            `;
            
            donationList.appendChild(donationItem);
        });
    } catch (error) {
        console.error('Erreur lors du chargement des demandes:', error);
        const donationList = document.querySelector('.donation-list');
        donationList.innerHTML = '<p>Erreur lors du chargement des demandes</p>';
    }
}

// Fonction pour mettre à jour le statut d'un don
async function updateDonationStatus(donationId, newStatus) {
    try {
        const response = await fetch('../../html/php/blood_donation_handler.php?action=update_status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `donation_id=${donationId}&status=${newStatus}`
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.error) {
            console.error('Erreur:', result.error);
            return;
        }
        
        if (result.success) {
            // Recharger les données
            loadDonationStats();
            loadPendingRequests();
        } else {
            console.error('Erreur lors de la mise à jour du statut:', result.error);
        }
    } catch (error) {
        console.error('Erreur lors de la mise à jour du statut:', error);
    }
}

// Fonction pour mettre à jour le graphique des groupes sanguins
function updateBloodTypeChart(bloodTypes) {
    const ctx = document.getElementById('bloodTypeChart');
    if (!ctx) {
        console.error('Canvas non trouvé');
        return;
    }

    // Détruire le graphique existant s'il existe
    if (window.bloodTypeChart) {
        window.bloodTypeChart.destroy();
    }

    // Couleurs des groupes sanguins
    const bloodTypeColors = {
        'A+': '#FF6384', // Rouge
        'A-': '#FF9F40', // Orange
        'B+': '#FFCD56', // Jaune
        'B-': '#4BC0C0', // Turquoise
        'AB+': '#36A2EB', // Bleu
        'AB-': '#9966FF', // Violet
        'O+': '#FF6384', // Rouge
        'O-': '#C9CBCF'  // Gris
    };

    // Créer le nouveau graphique avec les données et couleurs dynamiques
    window.bloodTypeChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(bloodTypes),
            datasets: [{
                label: 'Nombre de dons par groupe sanguin',
                data: Object.values(bloodTypes),
                backgroundColor: Object.keys(bloodTypes).map(type => bloodTypeColors[type]),
                borderWidth: 1,
                borderColor: '#fff',
                hoverBackgroundColor: Object.keys(bloodTypes).map(type => {
                    // Assombrir légèrement la couleur au survol
                    return Chart.helpers.color(bloodTypeColors[type]).darken(0.2).rgbString();
                })
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 14
                    },
                    callbacks: {
                        label: function(context) {
                            return `${context.parsed.y} dons`;
                        }
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });
}

// Fonction pour rafraîchir les statistiques
function refreshBloodStats() {
    const refreshBtn = document.querySelector('.refresh-btn');
    if (refreshBtn) {
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Chargement...';
    }

    fetch('../php/get_blood_type_stats.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateBloodTypeChart(data.bloodTypes);
                updateBloodDonationStats(data.stats);
                showNotification('Statistiques mises à jour avec succès', 'success');
            } else {
                throw new Error(data.message || 'Erreur lors du chargement des statistiques');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification(error.message, 'error');
        })
        .finally(() => {
            if (refreshBtn) {
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Rafraîchir';
            }
        });
}

// Fonction pour charger l'historique des dons
async function loadDonationHistory() {
    try {
        const historyContainer = document.querySelector('.donation-history');
        historyContainer.innerHTML = '<p class="loading-message"><i class="fas fa-spinner fa-spin"></i> Chargement de l\'historique...</p>';
        
        const date = document.getElementById('history-date').value;
        let url = '../../html/php/blood_donation_handler.php?action=get_history';
        
        // Ajouter le filtre de date seulement si une date est sélectionnée
        if (date) {
            url += `&date=${date}`;
        }
        
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const history = await response.json();
        
        if (history.error) {
            console.error('Erreur:', history.error);
            historyContainer.innerHTML = '<p class="error-message">Erreur lors du chargement de l\'historique</p>';
            return;
        }
        
        historyContainer.innerHTML = '';
        
        if (history.length === 0) {
            historyContainer.innerHTML = '<p class="no-data-message">Aucun don trouvé</p>';
            return;
        }
        
        // Trier les dons par date (du plus récent au plus ancien)
        history.sort((a, b) => new Date(b.date) - new Date(a.date));
        
        history.forEach(donation => {
            const historyItem = document.createElement('div');
            historyItem.className = `history-item ${donation.status}`;
            
            // Déterminer le texte du statut et la classe CSS
            let statusText, statusClass;
            switch(donation.status) {
                case 'confirme':
                    statusText = 'Confirmé';
                    statusClass = 'completed';
                    break;
                case 'annule':
                    statusText = 'Annulé';
                    statusClass = 'cancelled';
                    break;
                case 'en_attente':
                    statusText = 'En attente';
                    statusClass = 'pending';
                    break;
                case 'termine':
                    statusText = 'Terminé';
                    statusClass = 'completed';
                    break;
                default:
                    statusText = donation.status;
                    statusClass = donation.status;
            }
            
            historyItem.innerHTML = `
                <div class="history-item-header">
                    <div class="history-item-title">Don de sang #${donation.id}</div>
                    <div class="history-item-date">${new Date(donation.date).toLocaleDateString()}</div>
                </div>
                <div class="history-item-details">
                    <div class="history-item-detail">
                        <span class="history-item-detail-label">Donneur</span>
                        <span class="history-item-detail-value">${donation.donor_name}</span>
                    </div>
                    <div class="history-item-detail">
                        <span class="history-item-detail-label">Groupe sanguin</span>
                        <span class="history-item-detail-value">${donation.blood_type}</span>
                    </div>
                    <div class="history-item-detail">
                        <span class="history-item-detail-label">Lieu</span>
                        <span class="history-item-detail-value">${donation.location}</span>
                    </div>
                    <div class="history-item-detail">
                        <span class="history-item-detail-label">Statut</span>
                        <span class="history-item-status ${statusClass}">${statusText}</span>
                    </div>
                </div>
            `;
            
            historyContainer.appendChild(historyItem);
        });
    } catch (error) {
        console.error('Erreur lors du chargement de l\'historique:', error);
        const historyContainer = document.querySelector('.donation-history');
        historyContainer.innerHTML = '<p class="error-message">Erreur lors du chargement de l\'historique</p>';
    }
}

// Fonction pour afficher les détails d'un don
function viewDonationDetails(donationId) {
    // Implémenter la logique pour afficher les détails du don
    console.log('Voir détails du don:', donationId);
}

// Fonction pour imprimer les détails d'un don
function printDonationDetails(donationId) {
    // Implémenter la logique pour imprimer les détails du don
    console.log('Imprimer détails du don:', donationId);
}

// Charger les données au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    loadDonationStats();
    loadPendingRequests();
    loadDonationHistory();
    
    // Recharger les données toutes les 5 minutes
    setInterval(() => {
        loadDonationStats();
        loadPendingRequests();
        loadDonationHistory();
    }, 300000);
}); 