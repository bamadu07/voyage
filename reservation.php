<?php
session_start();
include 'includes/header.php';
include 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Récupérer les destinations
$query = "SELECT * FROM destinations ORDER BY nom";
$stmt = $db->prepare($query);
$stmt->execute();
$destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destination_id = $_POST['destination_id'];
    $nom_client = htmlspecialchars(trim($_POST['nom_client']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $telephone = htmlspecialchars(trim($_POST['telephone']));
    $date_depart = $_POST['date_depart'];
    $nombre_personnes = $_POST['nombre_personnes'];
    $message_special = htmlspecialchars(trim($_POST['message_special'] ?? ''));

    // Validation
    $errors = [];
    if (empty($nom_client)) $errors[] = "Le nom complet est requis";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Adresse email invalide";
    if (empty($telephone)) $errors[] = "Le numéro de téléphone est requis";
    if (empty($date_depart)) $errors[] = "La date de départ est requise";
    if (empty($destination_id)) $errors[] = "Veuillez sélectionner une destination";

    if (empty($errors)) {
        // Récupérer le prix de la destination
        $query_prix = "SELECT prix, nom FROM destinations WHERE id = ?";
        $stmt_prix = $db->prepare($query_prix);
        $stmt_prix->execute([$destination_id]);
        $destination = $stmt_prix->fetch(PDO::FETCH_ASSOC);
        
        $prix_total = $destination['prix'] * $nombre_personnes;

        // Insérer la réservation
        $query_insert = "INSERT INTO reservations (destination_id, nom_client, email, telephone, date_depart, nombre_personnes, prix_total, message_special) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $db->prepare($query_insert);
        
        if ($stmt_insert->execute([$destination_id, $nom_client, $email, $telephone, $date_depart, $nombre_personnes, $prix_total, $message_special])) {
            $reservation_id = $db->lastInsertId();
            $_SESSION['success_message'] = "
                <h4 class='success-title'><i class='fas fa-check-circle me-2'></i>Réservation confirmée !</h4>
                <div class='success-details'>
                    <div class='success-item'>
                        <span class='label'>Référence :</span>
                        <span class='value'>RES" . str_pad($reservation_id, 6, '0', STR_PAD_LEFT) . "</span>
                    </div>
                    <div class='success-item'>
                        <span class='label'>Destination :</span>
                        <span class='value'>" . htmlspecialchars($destination['nom']) . "</span>
                    </div>
                    <div class='success-item'>
                        <span class='label'>Montant total :</span>
                        <span class='value'>" . number_format($prix_total, 0, ',', ' ') . " €</span>
                    </div>
                </div>
                <p class='success-note'>Nous vous contacterons dans les plus brefs délais pour confirmer votre réservation.</p>
            ";
            header('Location: reservation.php');
            exit;
        } else {
            $errors[] = "Une erreur s'est produite lors de l'enregistrement. Veuillez réessayer.";
        }
    }
}

// Afficher le message de succès
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);
?>

<section class="reservation-page">
    <!-- Hero Section -->
    <div class="page-hero">
        <div class="hero-background">
            <div class="hero-shape shape-1"></div>
            <div class="hero-shape shape-2"></div>
            <div class="hero-shape shape-3"></div>
        </div>
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="hero-title">Réserver Votre Voyage</h1>
                <p class="hero-subtitle">
                    Remplissez le formulaire ci-dessous pour confirmer votre réservation
                </p>
                <div class="hero-steps">
                    <div class="step active">
                        <div class="step-number">1</div>
                        <span>Informations</span>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <span>Confirmation</span>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <span>Validation</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de Réservation -->
    <div class="reservation-section">
        <div class="container">
            <div class="reservation-card">
                <?php if($success_message): ?>
                <div class="success-message">
                    <?php echo $success_message; ?>
                    <div class="success-actions">
                        <a href="destinations.php" class="btn btn-primary">
                            <i class="fas fa-globe me-2"></i>
                            Voir d'autres destinations
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-home me-2"></i>
                            Retour à l'accueil
                        </a>
                    </div>
                </div>
                <?php else: ?>

                <?php if(!empty($errors)): ?>
                <div class="error-message">
                    <div class="error-header">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h4>Erreurs de validation</h4>
                    </div>
                    <ul class="error-list">
                        <?php foreach($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="POST" class="reservation-form" novalidate>
                    <div class="form-grid">
                        <!-- Destination -->
                        <div class="form-group">
                            <label for="destination_id" class="form-label">
                                <i class="fas fa-map-marker-alt"></i>
                                Destination *
                            </label>
                            <select class="form-select" id="destination_id" name="destination_id" required>
                                <option value="">Choisir une destination...</option>
                                <?php foreach($destinations as $destination): ?>
                                <option value="<?php echo $destination['id']; ?>" 
                                        data-prix="<?php echo $destination['prix']; ?>"
                                        data-image="<?php echo $destination['image_url']; ?>"
                                        <?php echo ($_POST['destination_id'] ?? '') == $destination['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($destination['nom']); ?> - 
                                    <?php echo number_format($destination['prix'], 0, ',', ' '); ?> €
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Veuillez sélectionner une destination.
                            </div>
                        </div>

                        <!-- Nombre de personnes -->
                        <div class="form-group">
                            <label for="nombre_personnes" class="form-label">
                                <i class="fas fa-users"></i>
                                Nombre de personnes *
                            </label>
                            <select class="form-select" id="nombre_personnes" name="nombre_personnes" required>
                                <option value="1" <?php echo ($_POST['nombre_personnes'] ?? '2') == '1' ? 'selected' : ''; ?>>1 personne</option>
                                <option value="2" <?php echo ($_POST['nombre_personnes'] ?? '2') == '2' ? 'selected' : ''; ?>>2 personnes</option>
                                <option value="3" <?php echo ($_POST['nombre_personnes'] ?? '2') == '3' ? 'selected' : ''; ?>>3 personnes</option>
                                <option value="4" <?php echo ($_POST['nombre_personnes'] ?? '2') == '4' ? 'selected' : ''; ?>>4 personnes</option>
                                <option value="5" <?php echo ($_POST['nombre_personnes'] ?? '2') == '5' ? 'selected' : ''; ?>>5 personnes</option>
                                <option value="6" <?php echo ($_POST['nombre_personnes'] ?? '2') == '6' ? 'selected' : ''; ?>>6 personnes</option>
                            </select>
                        </div>

                        <!-- Informations personnelles -->
                        <div class="form-group full-width">
                            <h3 class="form-section-title">
                                <i class="fas fa-user-circle"></i>
                                Informations personnelles
                            </h3>
                        </div>

                        <div class="form-group">
                            <label for="nom_client" class="form-label">
                                <i class="fas fa-user"></i>
                                Nom complet *
                            </label>
                            <input type="text" class="form-control" id="nom_client" name="nom_client" 
                                   value="<?php echo $_POST['nom_client'] ?? ''; ?>" 
                                   placeholder="Votre nom complet" required>
                            <div class="invalid-feedback">
                                Veuillez entrer votre nom complet.
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i>
                                Adresse email *
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo $_POST['email'] ?? ''; ?>" 
                                   placeholder="votre@email.com" required>
                            <div class="invalid-feedback">
                                Veuillez entrer une adresse email valide.
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="telephone" class="form-label">
                                <i class="fas fa-phone"></i>
                                Téléphone *
                            </label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" 
                                   value="<?php echo $_POST['telephone'] ?? ''; ?>" 
                                   placeholder="+33 1 23 45 67 89" required>
                            <div class="invalid-feedback">
                                Veuillez entrer votre numéro de téléphone.
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="date_depart" class="form-label">
                                <i class="fas fa-calendar-alt"></i>
                                Date de départ souhaitée *
                            </label>
                            <input type="date" class="form-control" id="date_depart" name="date_depart" 
                                   min="<?php echo date('Y-m-d', strtotime('+3 days')); ?>" 
                                   value="<?php echo $_POST['date_depart'] ?? ''; ?>" required>
                            <div class="invalid-feedback">
                                Veuillez sélectionner une date de départ.
                            </div>
                        </div>

                        <!-- Demandes particulières -->
                        <div class="form-group full-width">
                            <label for="message_special" class="form-label">
                                <i class="fas fa-comment-dots"></i>
                                Demandes particulières
                            </label>
                            <textarea class="form-control" id="message_special" name="message_special" 
                                      rows="4" placeholder="Précisez vos besoins particuliers, allergies, préférences alimentaires, etc."><?php echo $_POST['message_special'] ?? ''; ?></textarea>
                        </div>

                        <!-- Récapitulatif -->
                        <div class="form-group full-width">
                            <div class="summary-card">
                                <div class="summary-header">
                                    <i class="fas fa-receipt"></i>
                                    <h3>Récapitulatif de votre réservation</h3>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-details">
                                        <div class="summary-item">
                                            <span class="label">Destination:</span>
                                            <span id="summary-destination" class="value">-</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="label">Nombre de personnes:</span>
                                            <span id="summary-personnes" class="value">-</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="label">Date de départ:</span>
                                            <span id="summary-date" class="value">-</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="label">Prix par personne:</span>
                                            <span id="summary-prix" class="value">- €</span>
                                        </div>
                                    </div>
                                    <div class="summary-total">
                                        <span class="total-label">Total:</span>
                                        <span id="summary-total" class="total-amount">- €</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="form-group full-width">
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-submit">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Confirmer la Réservation
                                </button>
                                <p class="security-note">
                                    <i class="fas fa-lock me-1"></i>
                                    Vos données sont sécurisées et confidentielles
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* Variables */
:root {
    --primary: #2563eb;
    --primary-dark: #1e40af;
    --secondary: #f59e0b;
    --accent: #10b981;
    --danger: #ef4444;
    --dark: #1f2937;
    --light: #f8fafc;
    --gray: #6b7280;
    --gray-light: #e5e7eb;
    --border: 1px solid #e5e7eb;
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    --radius: 12px;
}

/* Page Hero */
.page-hero {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    padding: 100px 0 60px;
    position: relative;
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.hero-shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
}

.shape-1 {
    width: 300px;
    height: 300px;
    top: -150px;
    right: -100px;
}

.shape-2 {
    width: 200px;
    height: 200px;
    bottom: 100px;
    left: -50px;
}

.shape-3 {
    width: 150px;
    height: 150px;
    bottom: -50px;
    right: 20%;
}

.hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.hero-subtitle {
    font-size: 1.25rem;
    opacity: 0.9;
    margin-bottom: 3rem;
}

.hero-steps {
    display: flex;
    justify-content: center;
    gap: 3rem;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    opacity: 0.5;
    transition: all 0.3s ease;
}

.step.active {
    opacity: 1;
}

.step-number {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.25rem;
}

.step.active .step-number {
    background: var(--secondary);
    border-color: var(--secondary);
    color: var(--dark);
}

/* Section Réservation */
.reservation-section {
    padding: 4rem 0;
    background: var(--light);
}

.reservation-card {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    max-width: 1000px;
    margin: 0 auto;
}

/* Messages */
.success-message {
    padding: 3rem;
    text-align: center;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.success-title {
    font-size: 2rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.success-details {
    background: rgba(255, 255, 255, 0.1);
    border-radius: var(--radius);
    padding: 2rem;
    margin-bottom: 2rem;
}

.success-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.success-item:last-child {
    border-bottom: none;
}

.success-item .label {
    font-weight: 600;
}

.success-item .value {
    font-weight: 700;
    font-size: 1.1rem;
}

.success-note {
    opacity: 0.9;
    margin-bottom: 2rem;
}

.success-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.error-message {
    background: var(--danger);
    color: white;
    padding: 2rem;
    border-radius: var(--radius);
    margin: 2rem;
}

.error-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.error-header h4 {
    margin: 0;
}

.error-list {
    margin: 0;
    padding-left: 1.5rem;
}

.error-list li {
    margin-bottom: 0.5rem;
}

/* Formulaire */
.reservation-form {
    padding: 3rem;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.form-label i {
    color: var(--primary);
    width: 16px;
}

.form-control, .form-select {
    padding: 0.75rem 1rem;
    border: var(--border);
    border-radius: var(--radius);
    font-size: 1rem;
    transition: all 0.3s ease;
    width: 100%;
}

.form-control:focus, .form-select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-control:invalid, .form-select:invalid {
    border-color: var(--danger);
}

.invalid-feedback {
    color: var(--danger);
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: none;
}

.form-control:invalid ~ .invalid-feedback,
.form-select:invalid ~ .invalid-feedback {
    display: block;
}

.form-section-title {
    color: var(--dark);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-section-title i {
    color: var(--primary);
}

/* Récapitulatif */
.summary-card {
    background: var(--light);
    border-radius: var(--radius);
    border: var(--border);
    overflow: hidden;
}

.summary-header {
    background: var(--primary);
    color: white;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.summary-header h3 {
    margin: 0;
    font-size: 1.25rem;
}

.summary-content {
    padding: 2rem;
}

.summary-details {
    margin-bottom: 1.5rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--gray-light);
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-item .label {
    color: var(--gray);
    font-weight: 500;
}

.summary-item .value {
    color: var(--dark);
    font-weight: 600;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 2px solid var(--gray-light);
}

.total-label {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--dark);
}

.total-amount {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
}

/* Actions */
.form-actions {
    text-align: center;
    padding-top: 2rem;
    border-top: var(--border);
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    border: none;
    border-radius: var(--radius);
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.btn-secondary {
    background: var(--gray-light);
    color: var(--dark);
}

.btn-secondary:hover {
    background: var(--gray);
    color: white;
}

.btn-submit {
    font-size: 1.125rem;
    padding: 1.25rem 3rem;
}

.security-note {
    color: var(--gray);
    margin-top: 1rem;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-steps {
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .reservation-form {
        padding: 2rem;
    }
    
    .success-actions {
        flex-direction: column;
    }
    
    .summary-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .btn-submit {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const destinationSelect = document.getElementById('destination_id');
    const personnesSelect = document.getElementById('nombre_personnes');
    const dateInput = document.getElementById('date_depart');
    
    function updateSummary() {
        const selectedOption = destinationSelect.selectedOptions[0];
        const destinationName = selectedOption ? selectedOption.text.split(' - ')[0] : '-';
        const prixParPersonne = selectedOption ? selectedOption.dataset.prix : '0';
        const nombrePersonnes = personnesSelect.value;
        const dateDepart = dateInput.value;
        const total = prixParPersonne * nombrePersonnes;
        
        document.getElementById('summary-destination').textContent = destinationName;
        document.getElementById('summary-personnes').textContent = nombrePersonnes;
        document.getElementById('summary-date').textContent = dateDepart || '-';
        document.getElementById('summary-prix').textContent = Number(prixParPersonne).toLocaleString() + ' €';
        document.getElementById('summary-total').textContent = total.toLocaleString() + ' €';
    }
    
    destinationSelect.addEventListener('change', updateSummary);
    personnesSelect.addEventListener('change', updateSummary);
    dateInput.addEventListener('change', updateSummary);
    
    // Validation
    const form = document.querySelector('.reservation-form');
    form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        // Ajouter la classe was-validated à tous les éléments
        const formControls = form.querySelectorAll('.form-control, .form-select');
        formControls.forEach(control => {
            control.classList.add('was-validated');
        });
    }, false);
    
    // Validation en temps réel
    const inputs = form.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('blur', () => {
            if (input.checkValidity()) {
                input.classList.remove('invalid');
                input.classList.add('valid');
            } else {
                input.classList.remove('valid');
                input.classList.add('invalid');
            }
        });
    });
    
    // Initial update
    updateSummary();
    
    // Set minimum date to today + 3 days
    const today = new Date();
    today.setDate(today.getDate() + 3);
    const minDate = today.toISOString().split('T')[0];
    dateInput.min = minDate;
});
</script>

<?php include 'includes/footer.php'; ?>