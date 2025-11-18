<?php
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars(trim($_POST['nom']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $sujet = htmlspecialchars(trim($_POST['sujet']));
    $message = htmlspecialchars(trim($_POST['message']));
    
    // Simulation d'envoi d'email
    $success = true;
    
    if ($success) {
        $message_success = "
            <h5 class='alert-heading'><i class='fas fa-check-circle me-2'></i>Message envoyé !</h5>
            <p class='mb-0'>Merci $nom, votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.</p>
        ";
    }
}
?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-5">
                            <h1 class="display-5 fw-bold text-primary mb-3">Contactez-nous</h1>
                            <p class="lead text-muted">Nous sommes là pour répondre à toutes vos questions et vous accompagner dans l'organisation de votre voyage</p>
                        </div>

                        <?php if(isset($message_success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $message_success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <div class="row g-5">
                            <div class="col-lg-8">
                                <form method="POST" class="needs-validation" novalidate>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Nom complet *</label>
                                            <input type="text" class="form-control form-control-lg" name="nom" 
                                                   value="<?php echo $_POST['nom'] ?? ''; ?>" required>
                                            <div class="invalid-feedback">
                                                Veuillez entrer votre nom complet.
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Adresse email *</label>
                                            <input type="email" class="form-control form-control-lg" name="email" 
                                                   value="<?php echo $_POST['email'] ?? ''; ?>" required>
                                            <div class="invalid-feedback">
                                                Veuillez entrer une adresse email valide.
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Sujet *</label>
                                            <input type="text" class="form-control form-control-lg" name="sujet" 
                                                   value="<?php echo $_POST['sujet'] ?? ''; ?>" 
                                                   placeholder="Objet de votre message" required>
                                            <div class="invalid-feedback">
                                                Veuillez entrer un sujet.
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Message *</label>
                                            <textarea class="form-control form-control-lg" name="message" rows="6" 
                                                      placeholder="Décrivez-nous votre projet de voyage, vos questions..." required><?php echo $_POST['message'] ?? ''; ?></textarea>
                                            <div class="invalid-feedback">
                                                Veuillez entrer votre message.
                                            </div>
                                        </div>
                                        <div class="col-12 text-center pt-3">
                                            <button type="submit" class="btn btn-primary btn-lg px-5 py-3 fw-semibold">
                                                <i class="fas fa-paper-plane me-2"></i>Envoyer le message
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-lg-4">
                                <div class="card bg-primary text-white h-100 border-0">
                                    <div class="card-body p-4">
                                        <h5 class="card-title fw-bold mb-4">
                                            <i class="fas fa-info-circle me-2"></i>Informations de contact
                                        </h5>
                                        
                                        <div class="d-flex mb-4">
                                            <i class="fas fa-map-marker-alt fa-lg mt-1 me-3"></i>
                                            <div>
                                                <strong>Adresse</strong><br>
                                                123 Avenue des Voyages<br>
                                                75001 Paris, France
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex mb-4">
                                            <i class="fas fa-phone fa-lg mt-1 me-3"></i>
                                            <div>
                                                <strong>Téléphone</strong><br>
                                                +33 1 23 45 67 89
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex mb-4">
                                            <i class="fas fa-envelope fa-lg mt-1 me-3"></i>
                                            <div>
                                                <strong>Email</strong><br>
                                                contact@voyageexplorer.com
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex">
                                            <i class="fas fa-clock fa-lg mt-1 me-3"></i>
                                            <div>
                                                <strong>Horaires</strong><br>
                                                Lun - Ven: 9h-18h<br>
                                                Sam: 10h-16h
                                            </div>
                                        </div>

                                        <hr class="my-4 bg-white opacity-25">

                                        <div class="text-center">
                                            <h6 class="fw-bold mb-3">Suivez-nous</h6>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="#" class="btn btn-outline-light btn-sm rounded-circle">
                                                    <i class="fab fa-facebook-f"></i>
                                                </a>
                                                <a href="#" class="btn btn-outline-light btn-sm rounded-circle">
                                                    <i class="fab fa-twitter"></i>
                                                </a>
                                                <a href="#" class="btn btn-outline-light btn-sm rounded-circle">
                                                    <i class="fab fa-instagram"></i>
                                                </a>
                                                <a href="#" class="btn btn-outline-light btn-sm rounded-circle">
                                                    <i class="fab fa-linkedin-in"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation Bootstrap
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});
</script>

<?php include 'includes/footer.php'; ?>