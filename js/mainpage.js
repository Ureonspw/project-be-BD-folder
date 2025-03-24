document.getElementById('showPassword').addEventListener('change', function() {
    const passwordField = document.getElementById('password');
    passwordField.type = this.checked ? 'text' : 'password';
});

document.getElementById('loginButton').addEventListener('click', function() {
    const pseudoField = document.getElementById('pseudo');
    const passwordField = document.getElementById('password');

    if (pseudoField.value.trim() === '' || passwordField.value.trim() === '') {
        alert('Veuillez remplir tous les champs avant de continuer.');
    } else {
        window.location.href = 'mainpagecon.html';
    }
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
