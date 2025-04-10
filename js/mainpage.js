document.getElementById('showPassword').addEventListener('change', function() {
    const passwordField = document.getElementById('password');
    passwordField.type = this.checked ? 'text' : 'password';
});

let menuicn = document.querySelector(".menuicn");
let nav = document.querySelector(".navcontainer");

menuicn.addEventListener("click", () => {
    nav.classList.toggle("navclose");
})


document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour vérifier si un élément est visible dans la fenêtre
    function isElementInViewport(el) {
      const rect = el.getBoundingClientRect();
      return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
      );
    }

    // Fonction pour ajouter une classe d'animation lorsqu'un élément est visible
    function handleScrollAnimation() {
      const elements = document.querySelectorAll('.footer-section, .info-item');
      elements.forEach(function(element) {
        if (isElementInViewport(element)) {
          element.classList.add('animate');
        }
      });
    }

    // Écouter l'événement de défilement
    window.addEventListener('scroll', handleScrollAnimation);
    
    // Déclencher une fois au chargement initial
    handleScrollAnimation();
  });
  // Script pour l'animation au défilement
  document.addEventListener('DOMContentLoaded', function() {
    // ... existing code ...
    
    // Script pour le popup de connexion
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const loginButton = document.getElementById('loginButton');
    
    // Fonction pour afficher/masquer le mot de passe
    if (togglePasswordBtn && passwordInput) {
      togglePasswordBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Change l'icône en fonction de l'état
        const icon = togglePasswordBtn.querySelector('i');
        if (type === 'password') {
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
        } else {
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
        }
      });
    }
    
    // Animation lors du hover sur le bouton de connexion
    if (loginButton) {
      loginButton.addEventListener('mouseover', function() {
        const icon = this.querySelector('i');
        icon.style.transform = 'translateX(5px)';
        
        setTimeout(() => {
          icon.style.transform = 'translateX(0)';
        }, 300);
      });
    }
    
    // Animation des boutons sociaux
    const socialButtons = document.querySelectorAll('.social-btn');
    socialButtons.forEach(btn => {
      btn.addEventListener('mouseover', function() {
        this.style.transform = 'scale(1.1) translateY(-3px)';
        setTimeout(() => {
          this.style.transform = 'scale(1) translateY(-3px)';
        }, 200);
      });
      
      btn.addEventListener('mouseout', function() {
        this.style.transform = 'scale(1) translateY(0)';
      });
    });
  });




  // Script pour le popup de connexion
  document.addEventListener('DOMContentLoaded', function() {
    // Fermeture du popup
    const closeBtn = document.getElementById('closePopup');
    if (closeBtn) {
      closeBtn.addEventListener('click', function() {
        document.getElementById('popover').hidePopover();
      });
    }
    
    // Système d'onglets
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    const tabSlider = document.querySelector('.tab-slider');
    
    function switchTab(tabId) {
      // Mise à jour des boutons
      tabBtns.forEach(btn => {
        if (btn.dataset.tab === tabId) {
          btn.classList.add('active');
        } else {
          btn.classList.remove('active');
        }
      });
      
      // Mise à jour du slider
      const activeIndex = Array.from(tabBtns).findIndex(btn => btn.classList.contains('active'));
      tabSlider.style.left = `${activeIndex * 50}%`;
      
      // Mise à jour du contenu
      tabContents.forEach(content => {
        if (content.id === `${tabId}-tab`) {
          content.classList.add('active');
        } else {
          content.classList.remove('active');
        }
      });
    }
    
    tabBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        switchTab(this.dataset.tab);
      });
    });
    
    // Liens pour changer d'onglet
    document.querySelectorAll('.switch-tab').forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        switchTab(this.dataset.tab);
      });
    });
    
    // Fonction pour afficher/masquer le mot de passe
    const togglePasswordBtns = document.querySelectorAll('.toggle-password');
    
    togglePasswordBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const targetId = this.dataset.for || 'password';
        const passwordInput = document.getElementById(targetId);
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Change l'icône en fonction de l'état
        const icon = this.querySelector('i');
        if (type === 'password') {
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
        } else {
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
        }
      });
    });
    
    // Validation des entrées
    const inputs = document.querySelectorAll('.input-container input[type="text"], .input-container input[type="email"], .input-container input[type="password"]');
    
    inputs.forEach(input => {
      input.addEventListener('input', function() {
        const container = this.closest('.input-container');
        
        if (this.value.length > 0) {
          container.classList.add('valid');
          container.classList.remove('invalid');
        } else {
          container.classList.remove('valid');
        }
      });
      
      input.addEventListener('blur', function() {
        validateInput(this);
      });
    });
    
    function validateInput(input) {
      const container = input.closest('.input-container');
      
      if (input.value.length === 0) {
        container.classList.remove('valid');
        return;
      }
      
      if (input.type === 'email') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(input.value)) {
          container.classList.add('invalid');
          container.classList.remove('valid');
        }
      }
    }
    
    // Mesure de la force du mot de passe
    const registerPassword = document.getElementById('registerPassword');
    const strengthSegments = document.querySelectorAll('.strength-segment');
    const strengthText = document.querySelector('.strength-text');
    
    if (registerPassword) {
      registerPassword.addEventListener('input', function() {
        const password = this.value;
        const strength = measurePasswordStrength(password);
        
        updateStrengthIndicator(strength);
      });
    }
    
    function measurePasswordStrength(password) {
      let score = 0;
      
      // Longueur
      if (password.length > 6) score++;
      if (password.length > 10) score++;
      
      // Complexité
      if (/[A-Z]/.test(password)) score++;
      if (/[0-9]/.test(password)) score++;
      if (/[^A-Za-z0-9]/.test(password)) score++;
      
      return Math.min(score, 4);
    }
    
    function updateStrengthIndicator(strength) {
      const strengthClass = strength === 0 ? '' : 
                            strength < 2 ? 'weak' : 
                            strength < 3 ? 'medium' : 'strong';
      
      const strengthLabels = {
        '': 'Force du mot de passe',
        'weak': 'Faible',
        'medium': 'Moyen',
        'strong': 'Fort'
      };
      
      // Mise à jour des segments
      strengthSegments.forEach((segment, index) => {
        segment.className = 'strength-segment';
        if (index < strength) {
          segment.classList.add(strengthClass);
        }
      });
      
      // Mise à jour du texte
      if (strengthText) {
        strengthText.textContent = strengthLabels[strengthClass];
      }
    }
    
    // Activer/désactiver le bouton d'inscription en fonction des termes acceptés
    const termsCheckbox = document.getElementById('termsAccept');
    const registerButton = document.getElementById('registerButton');
    
    if (termsCheckbox && registerButton) {
      termsCheckbox.addEventListener('change', function() {
        registerButton.disabled = !this.checked;
      });
    }
    
    // Animation de chargement sur le bouton de connexion
    const loginButton = document.getElementById('loginButton');
    
    if (loginButton) {
      loginButton.addEventListener('click', function() {
        this.classList.add('loading');
        
        // Simuler un temps de chargement (À remplacer par votre logique d'authentification)
        setTimeout(() => {
          this.classList.remove('loading');
          
          // Simuler une connexion réussie (à modifier selon votre logique)
          const pseudo = document.getElementById('pseudo').value;
          const password = document.getElementById('password').value;
          
          if (pseudo && password) {
            // Connecter l'utilisateur ou afficher un message d'erreur
            showNotification('Connexion réussie!', 'success');
            
            // Rediriger après une connexion réussie
            // Décommentez la ligne suivante pour effectuer une redirection
            // window.location.href = 'dashboard.html';
          } else {
            showNotification('Veuillez remplir tous les champs', 'error');
          }
        }, 1500);
      });
    }
    
    // Fonction pour afficher les notifications
    function showNotification(message, type) {
      // Vérifier si une notification existe déjà
      let notification = document.querySelector('.popup-notification');
      
      if (notification) {
        notification.remove();
      }
      
      // Créer une nouvelle notification
      notification = document.createElement('div');
      notification.className = `popup-notification ${type}`;
      notification.innerHTML = `
        <div class="notification-content">
          <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
          <span>${message}</span>
        </div>
        <button class="close-notification">×</button>
      `;
      
      // Ajouter au DOM
      document.body.appendChild(notification);
      
      // Animation d'entrée
      setTimeout(() => {
        notification.classList.add('show');
      }, 10);
      
      // Auto-fermeture après un délai
      setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
          notification.remove();
        }, 300);
      }, 4000);
      
      // Bouton de fermeture
      const closeBtn = notification.querySelector('.close-notification');
      closeBtn.addEventListener('click', () => {
        notification.classList.remove('show');
        setTimeout(() => {
          notification.remove();
        }, 300);
      });
    }
    
    // Effet de focus sur les champs de saisie
    const inputFields = document.querySelectorAll('.input-container input');
    
    inputFields.forEach(input => {
      input.addEventListener('focus', function() {
        this.closest('.input-container').classList.add('focused');
      });
      
      input.addEventListener('blur', function() {
        this.closest('.input-container').classList.remove('focused');
      });
    });
    
    // Animation des boutons de médias sociaux
    const socialButtons = document.querySelectorAll('.social-btn');
    
    socialButtons.forEach(btn => {
      btn.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-3px)';
      });
      
      btn.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
      });
      
      btn.addEventListener('click', function() {
        const platform = this.classList.contains('google') ? 'Google' :
                         this.classList.contains('facebook') ? 'Facebook' :
                         this.classList.contains('apple') ? 'Apple' : '';
        
        showNotification(`Connexion avec ${platform} en cours...`, 'success');
        
        // Simuler un chargement
        this.style.pointerEvents = 'none';
        this.style.opacity = '0.7';
        
        setTimeout(() => {
          this.style.pointerEvents = 'auto';
          this.style.opacity = '1';
        }, 2000);
      });
    });
    
    // Ajoutez le style CSS pour les notifications
    const notificationStyle = document.createElement('style');
    notificationStyle.textContent = `
      .popup-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        min-width: 300px;
        padding: 15px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 1000;
        transform: translateX(120%);
        transition: transform 0.3s ease;
      }
      
      .popup-notification.show {
        transform: translateX(0);
      }
      
      .notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
      }
      
      .popup-notification.success .notification-content i {
        color: #28a745;
      }
      
      .popup-notification.error .notification-content i {
        color: #dc3545;
      }
      
      .close-notification {
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        color: #666;
      }
      
      .close-notification:hover {
        color: #333;
      }
      
      .input-container.focused {
        animation: pulse-border 1.5s infinite;
      }
      
      @keyframes pulse-border {
        0% {
          transform: scale(1);
        }
        50% {
          transform: scale(1.01);
        }
        100% {
          transform: scale(1);
        }
      }
    `;
    
    document.head.appendChild(notificationStyle);
  });

  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      events: [
        {
          title: 'Rendez-vous avec Dr. Martin',
          start: '2024-03-15T14:00:00',
          end: '2024-03-15T15:00:00'
        },
        // Add more events as needed
      ],
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      }
    });
    calendar.render();

    // Open modal on button click
    document.querySelector('.new-appointment-btn').addEventListener('click', function() {
      document.getElementById('new-appointment-modal').style.display = 'block';
    });

    // Close modal
    document.querySelector('.close-modal').addEventListener('click', function() {
      document.getElementById('new-appointment-modal').style.display = 'none';
    });

    // Handle form submission
    document.getElementById('appointment-form').addEventListener('submit', function(event) {
      event.preventDefault();
      // Get form data
      var specialty = this.querySelector('select[name="specialty"]').value;
      var doctor = this.querySelector('select[name="doctor"]').value;
      var date = this.querySelector('input[type="date"]').value;
      var time = this.querySelector('select[name="time"]').value;
      var reason = this.querySelector('textarea').value;

      // Create a new appointment object
      var newAppointment = {
        title: 'Rendez-vous avec ' + doctor,
        start: date + 'T' + time + ':00',
        end: date + 'T' + (parseInt(time.split(':')[0]) + 1) + ':00:00',
        status: 'pending'
      };

      // Add to pending list (for now, just log it)
      console.log('New appointment added:', newAppointment);

      // Close the modal
      document.getElementById('new-appointment-modal').style.display = 'none';

      // Optionally, update the UI to show the pending appointment
      // This part would involve updating the DOM to reflect the new appointment
    });
  });

// Gestion du formulaire de connexion
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('error-message');
    const loginButton = document.getElementById('loginButton');
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    // Fonction pour afficher/masquer le mot de passe
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }

    // Gestion de la soumission du formulaire
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Désactiver le bouton pendant la soumission
            loginButton.disabled = true;
            loginButton.querySelector('span').textContent = 'Connexion...';
            
            // Récupérer les données du formulaire
            const formData = new FormData(loginForm);
            
            // Envoyer la requête AJAX
            fetch('auth/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirection vers la page principale
                    window.location.href = 'mainpagecon.php';
                } else {
                    // Afficher le message d'erreur
                    errorMessage.textContent = data.message;
                    errorMessage.style.display = 'block';
                }
            })
            .catch(error => {
                errorMessage.textContent = 'Une erreur est survenue. Veuillez réessayer.';
                errorMessage.style.display = 'block';
            })
            .finally(() => {
                // Réactiver le bouton
                loginButton.disabled = false;
                loginButton.querySelector('span').textContent = 'Connexion';
            });
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
  // Initialiser les éléments de la page principale
  initMainPage();
});

function initMainPage() {
  // Vérifier si l'élément existe avant d'ajouter l'écouteur
  const element = document.querySelector('.element-a-verifier');
  if (element) {
    element.addEventListener('click', function() {
      // Code à exécuter
    });
  }
}



  