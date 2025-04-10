<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Traitement de la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
    try {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        
        // Vérifier si l'email existe
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['success_message'] = "Connexion réussie !";
            
            // Redirection vers la page principale après connexion
            header("Location: html/php/mainpagecon.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Email ou mot de passe incorrect";
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Erreur de connexion à la base de données";
        error_log("Erreur PDO: " . $e->getMessage());
    } catch(Exception $e) {
        $_SESSION['error_message'] = "Une erreur est survenue";
        error_log("Erreur: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="MediRdv - Plateforme de rendez-vous médicaux en ligne" />
    <link rel="stylesheet" href="/css/mainpage.css">
    <link rel="icon" href="/assets/images/image.png"> 
    <title>MediConnect</title>
    <script src="/js/mainpage.js">
      // Script pour le popup de connexion
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  </head>
  <body>
    <div class="container"><div class="video-background">
      <video autoplay muted loop id="background-video">
        <source src="/assets/video/videobg2.mp4" type="video/mp4">
        <!-- Vous pouvez ajouter d'autres formats pour une meilleure compatibilité -->
        <source src="/assets/video/videobg2.mp4" type="video/webm">
        <!-- Fallback pour les navigateurs qui ne supportent pas la vidéo -->
        Votre navigateur ne supporte pas les vidéos HTML5.
      </video>
    </div>
      <div class="headercontainer">
        <div class="logo" onclick="window.location.href='/html/php/docteur_login.php'"></div>
        <div class="boxhdcontainer">
          <a onclick="window.location.href='/html/php/creationcomptedocteur.php'">Accueil</a>
          <a href="#about">À Propos de nous</a>
          <a  href="#contact">Contacts</a>
          <a href="#services">Services</a>
        </div>
        <button class="login" popovertarget="popover">Connexion</button>
      </div>
      <div class="contenuemainpage">
        <h1>
          Bienvenue sur votre plateforme de rendez-vous santé pour votre bien-être personnel
        </h1>
        <div class="contentmainpage">
          <h2>Medi.Rdv</h2>
          <div class="exploremenu">
            <div class="menucontentexplore">
              <div class="barcontent1">Consultation plus rapide</div>
              <div class="barcontent2">
                <div class="txtcontent">
                  Vous permet de consulter rapidement les rendez-vous de vos
                  proches
                </div>
                <div class="imgcontent">▸</div>
              </div>
            </div>
            <div class="menucontentexplore">
              <div class="barcontent1">Gestion simplifiée</div>
              <div class="barcontent2">
                <div class="txtcontent">
                  Gérez facilement vos rendez-vous médicaux en quelques clics
                </div>
                <div class="imgcontent">▸</div>
              </div>
            </div>
            <div class="menucontentexplore">
              <div class="barcontent1">Suivi médical personnalisé</div>
              <div class="barcontent2">
                <div class="txtcontent">
                  Accédez à votre historique médical et suivez vos consultations
                </div>
                <div class="imgcontent">▸</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="popover" class="popover" popover>
      <div class="containerpop">
        
        <!-- Partie supérieure avec fond dégradé -->
        <div class="containerpopbox1">
          <div class="popup-header">
            <div class="popup-logo-container">
              <img src="/assets/images/image.png" alt="MediConnect Logo" class="popup-logo">
              <div class="logo-pulse"></div>
            </div>
            <h2>Connectez-vous à <span class="accent-text">MediConnect</span></h2>
            <p class="popup-tagline">Votre santé, notre priorité</p>
          </div>
        </div>
        
        <!-- Onglets de connexion -->
        
        <!-- Formulaire de connexion -->
        <div class="containerpopbox2">
          <div class="tab-content active" id="login-tab">
            <?php if(isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger" style="color: red; margin-bottom: 15px; padding: 10px; border-radius: 5px; background-color: #ffe6e6;">
                    <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="input-container">
                    <label for="email">
                        <i class="fas fa-user-circle"></i> Email
                    </label>
                    <div class="input-field">
                        <input
                            type="email"
                            class="entree1"
                            id="email"
                            name="email"
                            placeholder="Votre email"
                            required
                        />
                    </div>
                </div>
                
                <div class="input-container">
                    <label for="password">
                        <i class="fas fa-lock"></i> Mot de passe
                    </label>
                    <div class="input-field password-field">
                        <input
                            type="password"
                            class="entree2"
                            id="password"
                            name="password"
                            placeholder="Votre mot de passe"
                            required
                        />
                        <button type="button" id="togglePassword" class="toggle-password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="login-button">
                    <span>Connexion</span>
                    <div class="button-loader"></div>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
            
            <div class="login-divider">
              <span>ou</span>
            </div>
            
            <div class="social-login">
              <p>Connectez-vous avec</p>
              <div class="social-icons">
                <button class="social-btn google" title="Se connecter avec Google">
                  <i class="fab fa-google"></i>
                  <span>Google</span>
                </button>
                <button class="social-btn facebook" title="Se connecter avec Facebook">
                  <i class="fab fa-facebook-f"></i>
                  <span>Facebook</span>
                </button>
                <button class="social-btn apple" title="Se connecter avec Apple">
                  <i class="fab fa-apple"></i>
                  <span>Apple</span>
                </button>
              </div>
            </div>
            
            <div class="login-footer">
              <p>Nouveau sur MediConnect? <a href="./html/php/creationcompteutilisateur.php" class="switch-tab" data-tab="register">Créer un compte</a></p>
            </div>
          </div>
          
          <!-- Onglet d'inscription (masqué par défaut) -->
        </div>
        
        <!-- Informations de sécurité -->
        <div class="security-info">
          <i class="fas fa-shield-alt"></i>
          <span>Connexion sécurisée - Vos données sont protégées</span>
        </div>
      </div>
    </div>
    <div class="somme">
      <span class="section-title" id="about">Qui sommes-nous ?</span> 
      <div class="somme-content">
        
        <!-- Section Vision avec espace pour image -->
        <div class="info-block" >
          <div class="text-content">
            <h3  >Notre Vision</h3>
            <p>MediConnect est née d'une vision simple mais ambitieuse : révolutionner l'accès aux soins de santé en Afrique. 
            Face aux défis d'un système médical souvent engorgé, nous avons créé une solution digitale permettant 
            de connecter patients et professionnels de santé de manière fluide et efficace.</p>
          </div>
          <div class="image-container">
            <!-- Emplacement pour insérer une image -->
            <div class="image-placeholder">
              <img src="/assets/images/Nurse in Scrubs.jpg" alt="Notre vision" class="section-image">
            </div>
          </div>
        </div>
        
        <!-- Section Mission avec espace pour image -->
        <div class="info-block reverse">
          <div class="text-content">
            <h3>Notre Mission</h3>
            <p>Chaque jour, nous œuvrons pour:</p>
            <ul>
              <li>Faciliter l'accès aux soins médicaux pour tous</li>
              <li>Réduire les temps d'attente et optimiser la gestion des rendez-vous</li>
              <li>Offrir une expérience utilisateur simple et intuitive</li>
              <li>Garantir la confidentialité et la sécurité des données de santé</li>
            </ul>
          </div>
          <div class="image-container">
            <!-- Emplacement pour insérer une image -->
            <div class="image-placeholdertest">
              <!-- <img src="../../assets/images/Material Symbols Icon 24dp.png" alt="Notre mission" class="section-image"> -->
            </div>
          </div>

        </div>
        
        <!-- Section Services avec espace pour image -->
        <div class="info-block">
          <div class="text-content">
            <h3 id="services">Nos Services</h3>
            <p>MediConnect propose une gamme complète de fonctionnalités:</p>
            <div class="services-grid">
              <div class="service-item">
                <div class="service-icon">
                  <img src="/assets/images/Material Icons 24dp.png" alt="Icône rendez-vous">
                </div>
                <div class="service-text">
                  <strong>Prise de rendez-vous en ligne</strong>
                  <p>24h/24 et 7j/7, sans appel téléphonique</p>
                </div>
              </div>
              <div class="service-item">
                <div class="service-icon">
                  <img src="/assets/images/Family Restroom Icon.png" alt="Icône famille">
                </div>
                <div class="service-text">
                  <strong>Gestion familiale</strong>
                  <p>Organisez les rendez-vous pour vos proches</p>
                </div>
              </div>
              <div class="service-item">
                <div class="service-icon">
                  <img src="/assets/images/Active Notifications Icon.png" alt="Icône notifications">

                </div>
                <div class="service-text">
                  <strong>Rappels automatiques</strong>
                  <p>Notifications par SMS et email</p>
                </div>
              </div>
              <div class="service-item">
                <div class="service-icon">
                  <img src="/assets/images/Medical Services Icon.png" alt="Icône dossier médical">

                </div>
                <div class="service-text">
                  <strong>Dossier médical numérique</strong>
                  <p>Accédez à votre historique de consultations</p>
                </div>
              </div>
              <div class="service-item">
                <div class="service-icon">
                  <img src="/assets/images/Videocam Icon.png" alt="Icône téléconsultation">

                </div>
                <div class="service-text">
                  <strong>Téléconsultation</strong>
                  <p>Consultez certains spécialistes à distance</p>
                </div>
              </div>
            </div>
          </div>
          <div class="image-container">
            <!-- Emplacement pour insérer une image -->
            <div class="image-placeholder">
              <img src="/assets/images/Medical Patient Analysis.avif" alt="Nos services" class="section-image">
            </div>
          </div>
        </div>
        
        <!-- Section Équipe avec espace pour image -->
        <div class="info-block reverse">
          <div class="text-content">
            <h3>Notre Équipe</h3>
            <p>Derrière MediConnect se trouve une équipe passionnée de professionnels de la santé, d'ingénieurs et de 
            spécialistes du numérique, tous unis par la conviction que la technologie peut transformer positivement 
            l'expérience des soins de santé. Nous collaborons étroitement avec des établissements médicaux et des 
            praticiens pour garantir une solution adaptée aux besoins réels du terrain.</p>
          </div>
          <div class="image-container">
            <!-- Emplacement pour insérer une image -->
            <div class="image-placeholdertestimg">
              <img src="/assets/images/Medical Team Photo.jpg" alt="Notre équipe" class="section-image">
            </div>
          </div>

        </div>
        
        <!-- Section Engagement avec espace pour image -->
        <div class="info-block">
          <div class="text-content">
            <h3>Notre Engagement</h3>
            <p>Nous nous engageons à améliorer continuellement notre plateforme en fonction de vos retours. 
            La satisfaction de nos utilisateurs et la qualité des soins sont au cœur de nos préoccupations.</p>
          </div>
          <div class="image-container">
            <!-- Emplacement pour insérer une image -->
            <div class="image-placeholder2">
              
            </div>
          </div>
        </div>
        
      </div>
    </div>
    </div>

    <div class="connexion">
      <span> Qu'attends-tu alors ? <br> Rejoins-nous ! </span>
      <div class="connexioncontainer">
      <div class="connexion-button">
        <div class="connexionbtn1cont">
          Connecte-toi à ton compte pour profiter des offres de notre plateforme
          <div class="slidebtn"> Connexion ✒︎</div>
        </div>
        
      </div>
      <div class="connexion-button2">
        <div class="connexionbtn1cont">
          Crée un compte pour découvrir tous nos services
          <div class="slidebtn"> Inscription ✒︎</div>
        </div> 
</div>
</div>
        <div class="conclusion-block">
          <p class="conclusion">MediConnect - Votre santé, notre priorité.</p>
        </div>

    <script src="/js/mainpage.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('loginForm');
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
                // Désactiver le bouton pendant la soumission
                loginButton.disabled = true;
                loginButton.querySelector('span').textContent = 'Connexion...';
            });
        }
    });
    </script>

    <!-- Footer moderne et complet -->
    <footer class="footer-container">
      <!-- Section principale du footer -->
      <div class="footer-main">
        <!-- Colonne 1 : À propos -->
        <div class="footer-section">
          <div class="footer-brand">
            <img src="/assets/images/image.png" alt="MediRdv Logo" class="footer-logo">
            <h3>MediConnect</h3>
          </div>
          <p class="footer-description">
            Votre plateforme de confiance pour la prise de rendez-vous médicaux en Afrique. 
            Nous connectons patients et professionnels de santé pour des soins plus accessibles.
          </p>
          <div class="social-links">
            <a href="#" class="social-link" aria-label="Facebook">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="social-link" aria-label="Twitter">
              <i class="fab fa-twitter"></i>
            </a>
            <a href="#" class="social-link" aria-label="LinkedIn">
              <i class="fab fa-linkedin-in"></i>
            </a>
            <a href="#" class="social-link" aria-label="Instagram">
              <i class="fab fa-instagram"></i>
            </a>
          </div>
        </div>

        <!-- Colonne 2 : Liens rapides -->
        <div class="footer-section">
          <h4>Liens rapides</h4>
          <ul class="footer-links">
            <li><a href="inscription_docteur.php">Accueil</a></li>
            <li><a href="about.php">À Propos de nous</a></li>
            <li><a href="services.php">Nos services</a></li>
            <li><a href="doctors.php">Nos médecins</a></li>
            <li><a href="blog.php">Blog santé</a></li>
            <li><a href="contact.php">Contact</a></li>
          </ul>
        </div>

        <!-- Colonne 3 : Services -->
        <div class="footer-section">
          <h4>Nos services</h4>
          <ul class="footer-links">
            <li><a href="#">Consultation en ligne</a></li>
            <li><a href="#">Rendez-vous d'urgence</a></li>
            <li><a href="#">Suivi médical</a></li>
            <li><a href="#">Téléconsultation</a></li>
            <li><a href="#">Dossier médical numérique</a></li>
            <li><a href="#">Conseils santé</a></li>
          </ul>
        </div>

        <!-- Colonne 4 : Contact -->
        <div class="footer-section" id="contact">
          <h4>Contactez-nous</h4>
          <ul class="contact-info">
            <li>
              <i class="fas fa-map-marker-alt"></i>
              <span>123 Avenue de la Santé<br>Abidjan, Cote d'Ivoire</span>
            </li>
            <li >
              <i class="fas fa-phone"></i>
              <span>+225 05 66 72 07 63</span>
            </li>
            <li>
              <i class="fas fa-envelope"></i>
              <span>Ureon206@gmail.com</span>
            </li>
          </ul>
          <div class="newsletter">
            <h5>Newsletter</h5>
            <form class="newsletter-form">
              <input type="email" placeholder="Votre email" required>
              <button type="submit">S'abonner</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Bande d'informations supplémentaires -->
      <div class="footer-info">
        <div class="info-container">
          <div class="info-item">
            <i class="fas fa-headset"></i>
            <div class="info-content">
              <h5>Support 24/7</h5>
              <p>Assistance disponible à tout moment</p>
            </div>
          </div>
          <div class="info-item">
            <i class="fas fa-user-md"></i>
            <div class="info-content">
              <h5>Médecins certifiés</h5>
              <p>Experts qualifiés à votre service</p>
            </div>
          </div>
          <div class="info-item">
            <i class="fas fa-lock"></i>
            <div class="info-content">
              <h5>Données sécurisées</h5>
              <p>Protection garantie de vos informations</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Bas de page -->
      <div class="footer-bottom">
        <div class="footer-bottom-content">
          <p>&copy; 2024 MediConnect. Tous droits réservés.</p>
          <div class="footer-bottom-links">
            <a href="#">Mentions légales</a>
            <a href="#">Politique de confidentialité</a>
            <a href="#">Conditions d'utilisation</a>
            <a href="#">Plan du site</a>
          </div>
        </div>
      </div>
    </footer>


  </body>
</html>