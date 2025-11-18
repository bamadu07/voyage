<?php
include 'includes/header.php';
include 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Récupérer les destinations populaires
try {
    $query = "SELECT id, nom, pays, image_url, categorie, description, duree_jours, prix, promotion 
              FROM destinations WHERE featured = 1 AND statut = 'actif' LIMIT 6";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur destinations: " . $e->getMessage());
    $destinations = [];
}

// Récupérer les avis
try {
    $query_avis = "SELECT a.*, d.nom as destination_nom, d.image_url as destination_image 
                   FROM avis a 
                   LEFT JOIN destinations d ON a.destination_id = d.id 
                   WHERE a.approuve = 1 
                   ORDER BY a.created_at DESC LIMIT 6";
    $stmt_avis = $db->prepare($query_avis);
    $stmt_avis->execute();
    $avis = $stmt_avis->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur avis: " . $e->getMessage());
    $avis = [];
}
?>

<!-- Hero Section Minimaliste -->
<section class="hero-minimalist">
    <div class="hero-background">
        <div class="hero-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>
    
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <span>✨ Voyages d'exception depuis 2010</span>
            </div>
            
            <h1 class="hero-title">
                L'art du 
                <span class="text-gradient">voyage</span>
                <br>réinventé
            </h1>
            
            <p class="hero-subtitle">
                Des expériences sur mesure, des souvenirs éternels. 
                L'excellence du voyage à portée de main.
            </p>
            
            <div class="hero-actions">
                <a href="#destinations" class="btn btn-primary">
                    <span>Explorer</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="#about" class="btn btn-secondary">
                    <i class="fas fa-play-circle"></i>
                    <span>Découvrir</span>
                </a>
            </div>
            
            <div class="hero-stats">
                <div class="stat">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Destinations</div>
                </div>
                <div class="stat">
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Satisfaction</div>
                </div>
                <div class="stat">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Support</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="hero-scroll">
        <div class="scroll-indicator"></div>
    </div>
</section>

<!-- Section Catégories -->
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <h2>Par où commencer ?</h2>
            <p>Choisissez votre type d'aventure</p>
        </div>
        
        <div class="categories-grid">
            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-umbrella-beach"></i>
                </div>
                <h3>Plage & Détente</h3>
                <p>Escape to paradise beaches</p>
                <span class="category-count">12 destinations</span>
            </div>
            
            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-mountain"></i>
                </div>
                <h3>Aventure & Nature</h3>
                <p>Thrilling outdoor experiences</p>
                <span class="category-count">8 destinations</span>
            </div>
            
            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-monument"></i>
                </div>
                <h3>Culture & Histoire</h3>
                <p>Discover ancient civilizations</p>
                <span class="category-count">15 destinations</span>
            </div>
            
            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Voyages Romantiques</h3>
                <p>Unforgettable moments for two</p>
                <span class="category-count">6 destinations</span>
            </div>
        </div>
    </div>
</section>

<!-- Section Destinations -->
<section id="destinations" class="destinations-section">
    <div class="container">
        <div class="section-header">
            <h2>Nos destinations coup de cœur</h2>
            <p>Des expériences uniques soigneusement sélectionnées</p>
        </div>
        
        <div class="destinations-grid">
            <?php foreach($destinations as $destination): ?>
            <article class="destination-card">
                <div class="card-image">
                    <img src="<?php echo $destination['image_url']; ?>" 
                         alt="<?php echo htmlspecialchars($destination['nom']); ?>"
                         loading="lazy">
                    
                    <?php if(!empty($destination['promotion'])): ?>
                    <div class="card-badge promo">
                        -<?php echo $destination['promotion']; ?>%
                    </div>
                    <?php endif; ?>
                    
                    <div class="card-overlay">
                        <button class="wishlist-btn">
                            <i class="far fa-heart"></i>
                        </button>
                        <div class="card-actions">
                            <a href="destination-details.php?id=<?php echo $destination['id']; ?>" class="action-btn">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="reservation.php?destination=<?php echo $destination['id']; ?>" class="action-btn primary">
                                <i class="fas fa-shopping-cart"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-content">
                    <div class="card-meta">
                        <span class="category"><?php echo $destination['categorie']; ?></span>
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <span>4.8</span>
                        </div>
                    </div>
                    
                    <h3 class="card-title"><?php echo htmlspecialchars($destination['nom']); ?></h3>
                    
                    <p class="card-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($destination['pays']); ?>
                    </p>
                    
                    <p class="card-description">
                        <?php echo substr(htmlspecialchars($destination['description']), 0, 100); ?>...
                    </p>
                    
                    <div class="card-footer">
                        <div class="price">
                            <?php if(!empty($destination['promotion'])): ?>
                                <span class="old-price"><?php echo number_format($destination['prix'], 0, ',', ' '); ?> €</span>
                                <span class="current-price">
                                    <?php 
                                    $prix_promo = $destination['prix'] * (1 - $destination['promotion']/100);
                                    echo number_format($prix_promo, 0, ',', ' '); 
                                    ?> €
                                </span>
                            <?php else: ?>
                                <span class="current-price"><?php echo number_format($destination['prix'], 0, ',', ' '); ?> €</span>
                            <?php endif; ?>
                            <span class="price-note">par personne</span>
                        </div>
                        
                        <div class="duration">
                            <i class="fas fa-clock"></i>
                            <?php echo $destination['duree_jours']; ?> jours
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <div class="section-cta">
            <a href="destinations.php" class="btn btn-outline">
                <span>Voir toutes les destinations</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Section Process -->
<section class="process-section">
    <div class="container">
        <div class="section-header">
            <h2>Voyagez en toute sérénité</h2>
            <p>Un processus simple et transparent</p>
        </div>
        
        <div class="process-steps">
            <div class="process-step">
                <div class="step-number">01</div>
                <div class="step-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Recherchez</h3>
                <p>Explorez nos destinations et trouvez l'inspiration</p>
            </div>
            
            <div class="process-step">
                <div class="step-number">02</div>
                <div class="step-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3>Réservez</h3>
                <p>Simple et sécurisé en quelques clics</p>
            </div>
            
            <div class="process-step">
                <div class="step-number">03</div>
                <div class="step-icon">
                    <i class="fas fa-suitcase"></i>
                </div>
                <h3>Préparez</h3>
                <p>Nous nous occupons de tous les détails</p>
            </div>
            
            <div class="process-step">
                <div class="step-number">04</div>
                <div class="step-icon">
                    <i class="fas fa-plane"></i>
                </div>
                <h3>Voyagez</h3>
                <p>Profitez de moments inoubliables</p>
            </div>
        </div>
    </div>
</section>

<!-- Section Avis -->
<section class="testimonials-section">
    <div class="container">
        <div class="section-header">
            <h2>Ils nous font confiance</h2>
            <p>Retours d'expérience de nos voyageurs</p>
        </div>
        
        <div class="testimonials-grid">
            <?php foreach($avis as $avis_item): ?>
            <div class="testimonial-card">
                <div class="testimonial-header">
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h4><?php echo htmlspecialchars($avis_item['nom_client']); ?></h4>
                            <p><?php echo htmlspecialchars($avis_item['destination_nom']); ?></p>
                        </div>
                    </div>
                    <div class="rating">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $avis_item['note'] ? 'active' : ''; ?>"></i>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <p class="testimonial-text">
                    "<?php echo htmlspecialchars($avis_item['commentaire']); ?>"
                </p>
                
                <div class="testimonial-date">
                    <?php echo date('d M Y', strtotime($avis_item['created_at'])); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Section CTA -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Prêt à vivre l'aventure ?</h2>
            <p>Rejoignez des milliers de voyageurs satisfaits et créez des souvenirs inoubliables</p>
            
            <div class="cta-actions">
                <a href="reservation.php" class="btn btn-primary">
                    <span>Réserver maintenant</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="contact.php" class="btn btn-secondary">
                    <i class="fas fa-phone"></i>
                    <span>Nous contacter</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-card">
            <div class="newsletter-content">
                <h3>Restez inspiré</h3>
                <p>Recevez nos meilleures offres et inspirations de voyage</p>
                
                <form class="newsletter-form">
                    <div class="input-group">
                        <input type="email" placeholder="Votre adresse email" required>
                        <button type="submit">
                            <span>S'abonner</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    <p class="newsletter-note">
                        En vous inscrivant, vous acceptez notre 
                        <a href="#">Politique de confidentialité</a>
                    </p>
                </form>
            </div>
            
            <div class="newsletter-image">
                <div class="floating-element el-1"></div>
                <div class="floating-element el-2"></div>
                <div class="floating-element el-3"></div>
            </div>
        </div>
    </div>
</section>

<style>
/* Variables CSS */
:root {
    --primary: #2563eb;
    --primary-dark: #1e40af;
    --secondary: #f59e0b;
    --accent: #10b981;
    --dark: #1f2937;
    --light: #f8fafc;
    --gray: #6b7280;
    --gray-light: #e5e7eb;
    --border: 1px solid #e5e7eb;
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    --radius: 12px;
    --transition: all 0.3s ease;
}

/* Reset et base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    line-height: 1.6;
    color: var(--dark);
    background: var(--light);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Hero Section */
.hero-minimalist {
    min-height: 100vh;
    display: flex;
    align-items: center;
    position: relative;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.hero-shapes .shape {
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
    color: white;
    max-width: 800px;
    margin: 0 auto;
}

.hero-badge {
    margin-bottom: 2rem;
}

.hero-badge span {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.hero-title {
    font-size: 4rem;
    font-weight: 700;
    line-height: 1.1;
    margin-bottom: 1.5rem;
}

.text-gradient {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-subtitle {
    font-size: 1.25rem;
    opacity: 0.9;
    margin-bottom: 3rem;
    line-height: 1.6;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 4rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 12px 24px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    border: none;
    cursor: pointer;
    font-size: 1rem;
}

.btn-primary {
    background: var(--secondary);
    color: var(--dark);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.2);
}

.btn-outline {
    border: 2px solid var(--primary);
    color: var(--primary);
    background: transparent;
}

.btn-outline:hover {
    background: var(--primary);
    color: white;
}

.hero-stats {
    display: flex;
    justify-content: center;
    gap: 3rem;
}

.stat {
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
}

.hero-scroll {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
}

.scroll-indicator {
    width: 2px;
    height: 40px;
    background: rgba(255, 255, 255, 0.5);
    position: relative;
}

.scroll-indicator::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 8px;
    background: white;
    animation: scroll 2s infinite;
}

@keyframes scroll {
    0% { top: 0; opacity: 1; }
    100% { top: 100%; opacity: 0; }
}

/* Sections communes */
.section-header {
    text-align: center;
    margin-bottom: 4rem;
}

.section-header h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: var(--dark);
}

.section-header p {
    font-size: 1.125rem;
    color: var(--gray);
}

.section-cta {
    text-align: center;
    margin-top: 3rem;
}

/* Categories */
.categories-section {
    padding: 100px 0;
    background: white;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.category-card {
    text-align: center;
    padding: 2rem;
    border-radius: var(--radius);
    background: var(--light);
    transition: var(--transition);
    border: var(--border);
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.category-icon {
    width: 80px;
    height: 80px;
    background: var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    color: white;
    font-size: 2rem;
}

.category-card h3 {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    color: var(--dark);
}

.category-card p {
    color: var(--gray);
    margin-bottom: 1rem;
}

.category-count {
    font-size: 0.9rem;
    color: var(--primary);
    font-weight: 600;
}

/* Destinations */
.destinations-section {
    padding: 100px 0;
    background: var(--light);
}

.destinations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
}

.destination-card {
    background: white;
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
    position: relative;
}

.destination-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
}

.card-image {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.destination-card:hover .card-image img {
    transform: scale(1.05);
}

.card-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    color: white;
}

.card-badge.promo {
    background: #ef4444;
}

.card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
    opacity: 0;
    transition: var(--transition);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 1rem;
}

.destination-card:hover .card-overlay {
    opacity: 1;
}

.wishlist-btn {
    align-self: flex-end;
    background: rgba(255, 255, 255, 0.9);
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    color: var(--gray);
}

.wishlist-btn:hover {
    background: white;
    color: #ef4444;
}

.card-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.action-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.9);
    color: var(--dark);
    text-decoration: none;
    transition: var(--transition);
}

.action-btn:hover {
    background: white;
    transform: scale(1.1);
}

.action-btn.primary {
    background: var(--primary);
    color: white;
}

.card-content {
    padding: 1.5rem;
}

.card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.category {
    background: var(--gray-light);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--gray);
}

.rating {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: var(--secondary);
    font-size: 0.9rem;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--dark);
}

.card-location {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--gray);
    font-size: 0.9rem;
    margin-bottom: 0.75rem;
}

.card-description {
    color: var(--gray);
    font-size: 0.9rem;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: var(--border);
}

.price {
    display: flex;
    flex-direction: column;
}

.old-price {
    text-decoration: line-through;
    color: var(--gray);
    font-size: 0.8rem;
}

.current-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary);
}

.price-note {
    font-size: 0.7rem;
    color: var(--gray);
}

.duration {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--gray);
    font-size: 0.9rem;
}

/* Process Section */
.process-section {
    padding: 100px 0;
    background: white;
}

.process-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.process-step {
    text-align: center;
    padding: 2rem 1rem;
    position: relative;
}

.step-number {
    font-size: 4rem;
    font-weight: 900;
    color: var(--gray-light);
    position: absolute;
    top: -1rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1;
}

.step-icon {
    width: 80px;
    height: 80px;
    background: var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    color: white;
    font-size: 2rem;
    position: relative;
    z-index: 2;
}

.process-step h3 {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    color: var(--dark);
}

.process-step p {
    color: var(--gray);
}

/* Testimonials */
.testimonials-section {
    padding: 100px 0;
    background: var(--light);
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.testimonial-card {
    background: white;
    padding: 2rem;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.testimonial-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-avatar {
    width: 50px;
    height: 50px;
    background: var(--gray-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gray);
}

.user-info h4 {
    font-size: 1rem;
    margin-bottom: 0.25rem;
    color: var(--dark);
}

.user-info p {
    font-size: 0.9rem;
    color: var(--gray);
}

.rating .fa-star {
    color: var(--gray-light);
}

.rating .fa-star.active {
    color: var(--secondary);
}

.testimonial-text {
    color: var(--gray);
    font-style: italic;
    margin-bottom: 1rem;
    line-height: 1.6;
}

.testimonial-date {
    font-size: 0.8rem;
    color: var(--gray);
}

/* CTA Section */
.cta-section {
    padding: 100px 0;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    text-align: center;
}

.cta-content h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.cta-content p {
    font-size: 1.125rem;
    opacity: 0.9;
    margin-bottom: 2rem;
}

.cta-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

/* Newsletter */
.newsletter-section {
    padding: 80px 0;
    background: white;
}

.newsletter-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: var(--radius);
    padding: 4rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: center;
    color: white;
    position: relative;
    overflow: hidden;
}

.newsletter-content h3 {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.newsletter-content p {
    opacity: 0.9;
    margin-bottom: 2rem;
}

.newsletter-form .input-group {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.newsletter-form input {
    flex: 1;
    padding: 12px 16px;
    border: none;
    border-radius: 50px;
    font-size: 1rem;
}

.newsletter-form button {
    padding: 12px 24px;
    background: var(--secondary);
    color: var(--dark);
    border: none;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--transition);
}

.newsletter-form button:hover {
    transform: translateY(-2px);
}

.newsletter-note {
    font-size: 0.8rem;
    opacity: 0.8;
}

.newsletter-note a {
    color: white;
    text-decoration: underline;
}

.newsletter-image {
    position: relative;
    height: 200px;
}

.floating-element {
    position: absolute;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

.el-1 {
    width: 100px;
    height: 100px;
    top: 0;
    right: 0;
}

.el-2 {
    width: 60px;
    height: 60px;
    bottom: 40px;
    right: 40px;
}

.el-3 {
    width: 80px;
    height: 80px;
    bottom: 0;
    left: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .hero-stats {
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .destinations-grid {
        grid-template-columns: 1fr;
    }
    
    .newsletter-card {
        grid-template-columns: 1fr;
        text-align: center;
        padding: 2rem;
    }
    
    .cta-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .process-steps {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation au scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observer les éléments
    document.querySelectorAll('.category-card, .destination-card, .process-step, .testimonial-card').forEach(item => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(30px)';
        item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(item);
    });

    // Wishlist functionality
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                this.style.color = '#ef4444';
                
                // Show notification
                showNotification('Destination ajoutée aux favoris');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                this.style.color = '';
                
                showNotification('Destination retirée des favoris');
            }
        });
    });

    function showNotification(message) {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--dark);
            color: white;
            padding: 12px 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // Newsletter form
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            const btn = this.querySelector('button');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-check"></i> Inscrit !';
                btn.style.background = '#10b981';
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    btn.style.background = '';
                    this.reset();
                    showNotification('Merci pour votre inscription !');
                }, 2000);
            }, 1500);
        });
    }

    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>