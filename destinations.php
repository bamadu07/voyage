<?php
include 'includes/header.php';
include 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Filtres et recherche
$categorie = $_GET['categorie'] ?? '';
$search = $_GET['search'] ?? '';
$prix_min = $_GET['prix_min'] ?? '';
$prix_max = $_GET['prix_max'] ?? '';
$duree = $_GET['duree'] ?? '';
$tri = $_GET['tri'] ?? 'featured';

// Construction de la requête (sans le champ 'statut' qui n'existe pas)
$query = "SELECT * FROM destinations WHERE 1=1";
$params = [];

if (!empty($categorie)) {
    $query .= " AND categorie = ?";
    $params[] = $categorie;
}

if (!empty($search)) {
    $query .= " AND (nom LIKE ? OR pays LIKE ? OR description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($prix_min)) {
    $query .= " AND prix >= ?";
    $params[] = $prix_min;
}

if (!empty($prix_max)) {
    $query .= " AND prix <= ?";
    $params[] = $prix_max;
}

if (!empty($duree)) {
    $query .= " AND duree_jours = ?";
    $params[] = $duree;
}

// Ordre de tri
switch($tri) {
    case 'prix_croissant':
        $query .= " ORDER BY prix ASC";
        break;
    case 'prix_decroissant':
        $query .= " ORDER BY prix DESC";
        break;
    case 'duree':
        $query .= " ORDER BY duree_jours ASC";
        break;
    case 'nom':
        $query .= " ORDER BY nom ASC";
        break;
    default:
        $query .= " ORDER BY featured DESC, nom ASC";
        break;
}

$stmt = $db->prepare($query);
$stmt->execute($params);
$destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques pour les filtres (sans le champ 'statut')
$stats_query = "SELECT 
    COUNT(*) as total,
    MIN(prix) as prix_min,
    MAX(prix) as prix_max,
    GROUP_CONCAT(DISTINCT duree_jours ORDER BY duree_jours ASC) as durees
    FROM destinations";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Catégories disponibles (sans le champ 'statut')
$categories_query = "SELECT DISTINCT categorie FROM destinations ORDER BY categorie";
$categories_stmt = $db->prepare($categories_query);
$categories_stmt->execute();
$categories_db = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);

$categories = !empty($categories_db) ? $categories_db : ['aventure', 'plage', 'culture', 'romantique', 'famille'];
$durees = $stats['durees'] ? explode(',', $stats['durees']) : [3, 5, 7, 10, 14];
?>

<section class="destinations-page">
    <!-- Hero Section -->
    <div class="page-hero">
        <div class="hero-background">
            <div class="hero-shape shape-1"></div>
            <div class="hero-shape shape-2"></div>
            <div class="hero-shape shape-3"></div>
        </div>
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="hero-title">Explorez le Monde</h1>
                <p class="hero-subtitle">
                    Découvrez notre collection exclusive de destinations soigneusement sélectionnées
                </p>
                <div class="hero-stats">
                    <div class="stat">
                        <span class="number"><?php echo count($destinations); ?></span>
                        <span class="label">Résultats</span>
                    </div>
                    <div class="stat">
                        <span class="number"><?php echo $stats['total'] ?? '50+'; ?></span>
                        <span class="label">Au total</span>
                    </div>
                    <div class="stat">
                        <span class="number"><?php echo count($categories); ?></span>
                        <span class="label">Catégories</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres Avancés -->
    <div class="filters-section">
        <div class="container">
            <div class="filters-card">
                <div class="filters-header">
                    <h3>Filtrer les résultats</h3>
                    <button class="filters-toggle" id="filtersToggle">
                        <i class="fas fa-sliders-h"></i>
                        Filtres
                    </button>
                </div>
                
                <form method="GET" class="filters-form" id="filtersForm">
                    <div class="filters-grid">
                        <!-- Recherche -->
                        <div class="filter-group">
                            <label>Recherche</label>
                            <div class="search-input">
                                <i class="fas fa-search"></i>
                                <input type="text" name="search" 
                                       placeholder="Destination, pays, mot-clé..."
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>

                        <!-- Catégorie -->
                        <div class="filter-group">
                            <label>Catégorie</label>
                            <select name="categorie" class="filter-select">
                                <option value="">Toutes les catégories</option>
                                <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" 
                                        <?php echo $categorie === $cat ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($cat); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Prix -->
                        <div class="filter-group">
                            <label>Budget (€)</label>
                            <div class="price-range">
                                <div class="range-inputs">
                                    <input type="number" name="prix_min" 
                                           placeholder="Min" 
                                           value="<?php echo htmlspecialchars($prix_min); ?>"
                                           min="0" max="<?php echo $stats['prix_max'] ?? 10000; ?>">
                                    <span>-</span>
                                    <input type="number" name="prix_max" 
                                           placeholder="Max" 
                                           value="<?php echo htmlspecialchars($prix_max); ?>"
                                           min="0" max="<?php echo $stats['prix_max'] ?? 10000; ?>">
                                </div>
                                <div class="range-hint">
                                    De <?php echo number_format($stats['prix_min'] ?? 0, 0, ',', ' '); ?>€ 
                                    à <?php echo number_format($stats['prix_max'] ?? 10000, 0, ',', ' '); ?>€
                                </div>
                            </div>
                        </div>

                        <!-- Durée -->
                        <div class="filter-group">
                            <label>Durée (jours)</label>
                            <select name="duree" class="filter-select">
                                <option value="">Toutes les durées</option>
                                <?php foreach($durees as $d): ?>
                                <option value="<?php echo $d; ?>" 
                                        <?php echo $duree == $d ? 'selected' : ''; ?>>
                                    <?php echo $d; ?> jours
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Tri -->
                        <div class="filter-group">
                            <label>Trier par</label>
                            <select name="tri" class="filter-select">
                                <option value="featured" <?php echo $tri === 'featured' ? 'selected' : ''; ?>>Populaires</option>
                                <option value="prix_croissant" <?php echo $tri === 'prix_croissant' ? 'selected' : ''; ?>>Prix croissant</option>
                                <option value="prix_decroissant" <?php echo $tri === 'prix_decroissant' ? 'selected' : ''; ?>>Prix décroissant</option>
                                <option value="duree" <?php echo $tri === 'duree' ? 'selected' : ''; ?>>Durée</option>
                                <option value="nom" <?php echo $tri === 'nom' ? 'selected' : ''; ?>>Nom A-Z</option>
                            </select>
                        </div>
                    </div>

                    <div class="filters-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i>
                            Appliquer les filtres
                        </button>
                        <a href="destinations.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Résultats -->
    <div class="results-section">
        <div class="container">
            <!-- En-tête résultats -->
            <div class="results-header">
                <div class="results-info">
                    <h2>Nos Destinations</h2>
                    <p class="results-count">
                        <?php echo count($destinations); ?> destination(s) trouvée(s)
                        <?php if($search || $categorie || $prix_min || $prix_max || $duree): ?>
                        <span class="search-criteria">
                            pour votre recherche
                        </span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="results-views">
                    <button class="view-btn active" data-view="grid">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="view-btn" data-view="list">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>

            <!-- Grille des destinations -->
            <div class="destinations-grid" id="destinationsView">
                <?php if(empty($destinations)): ?>
                <div class="no-results">
                    <div class="no-results-content">
                        <i class="fas fa-search fa-3x"></i>
                        <h3>Aucune destination trouvée</h3>
                        <p>Essayez de modifier vos critères de recherche ou explorez toutes nos destinations</p>
                        <div class="no-results-actions">
                            <a href="destinations.php" class="btn btn-primary">
                                <i class="fas fa-globe-americas me-2"></i>
                                Voir toutes les destinations
                            </a>
                            <a href="contact.php" class="btn btn-secondary">
                                <i class="fas fa-envelope me-2"></i>
                                Demander un devis personnalisé
                            </a>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                    <?php foreach($destinations as $destination): ?>
                    <article class="destination-card" data-category="<?php echo $destination['categorie']; ?>">
                        <div class="card-image">
                            <img src="<?php echo $destination['image_url']; ?>" 
                                 alt="<?php echo htmlspecialchars($destination['nom']); ?>"
                                 loading="lazy">
                            
                            <!-- Badges -->
                            <div class="card-badges">
                                <?php if($destination['featured']): ?>
                                <span class="badge badge-popular">
                                    <i class="fas fa-star"></i>
                                    Populaire
                                </span>
                                <?php endif; ?>
                                <span class="badge badge-category">
                                    <?php echo $destination['categorie']; ?>
                                </span>
                            </div>

                            <!-- Actions rapides -->
                            <div class="card-actions">
                                <button class="action-btn wishlist-btn" data-destination="<?php echo $destination['id']; ?>">
                                    <i class="far fa-heart"></i>
                                </button>
                                <a href="destination-details.php?id=<?php echo $destination['id']; ?>" class="action-btn">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>

                        <div class="card-content">
                            <div class="card-meta">
                                <div class="location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($destination['pays']); ?>
                                </div>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <span>4.8</span>
                                </div>
                            </div>

                            <h3 class="card-title">
                                <a href="destination-details.php?id=<?php echo $destination['id']; ?>">
                                    <?php echo htmlspecialchars($destination['nom']); ?>
                                </a>
                            </h3>

                            <p class="card-description">
                                <?php echo htmlspecialchars($destination['description']); ?>
                            </p>

                            <div class="card-features">
                                <div class="feature">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo $destination['duree_jours']; ?> jours</span>
                                </div>
                                <div class="feature">
                                    <i class="fas fa-users"></i>
                                    <span>Tout compris</span>
                                </div>
                                <div class="feature">
                                    <i class="fas fa-plane"></i>
                                    <span>Vol inclus</span>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="price">
                                    <div class="price-amount">
                                        <?php echo number_format($destination['prix'], 0, ',', ' '); ?> €
                                    </div>
                                    <div class="price-note">par personne</div>
                                </div>
                                <div class="card-cta">
                                    <a href="reservation.php?destination=<?php echo $destination['id']; ?>" 
                                       class="btn btn-primary">
                                        Réserver
                                    </a>
                                    <a href="destination-details.php?id=<?php echo $destination['id']; ?>" 
                                       class="btn btn-secondary">
                                        Détails
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if(!empty($destinations)): ?>
            <div class="pagination-section">
                <div class="pagination-info">
                    Affichage de 1 à <?php echo count($destinations); ?> sur <?php echo $stats['total'] ?? count($destinations); ?> résultats
                </div>
                <!-- La pagination serait implémentée ici avec plus de résultats -->
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CTA Section -->
    <?php if(!empty($destinations)): ?>
    <div class="cta-section">
        <div class="container">
            <div class="cta-card">
                <div class="cta-content">
                    <h2>Vous ne trouvez pas votre bonheur ?</h2>
                    <p>Notre équipe d'experts peut créer un voyage sur mesure parfaitement adapté à vos envies</p>
                    <div class="cta-actions">
                        <a href="contact.php" class="btn btn-primary">
                            <i class="fas fa-envelope me-2"></i>
                            Demander un devis personnalisé
                        </a>
                        <a href="tel:+33123456789" class="btn btn-secondary">
                            <i class="fas fa-phone me-2"></i>
                            Nous appeler
                        </a>
                    </div>
                </div>
                <div class="cta-image">
                    <i class="fas fa-suitcase-rolling"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</section>

<style>
/* Variables */
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
    margin-bottom: 2rem;
}

.hero-stats {
    display: flex;
    justify-content: center;
    gap: 3rem;
}

.stat {
    text-align: center;
}

.stat .number {
    display: block;
    font-size: 2rem;
    font-weight: 700;
}

.stat .label {
    font-size: 0.9rem;
    opacity: 0.8;
}

/* Filtres */
.filters-section {
    padding: 2rem 0;
    background: white;
    border-bottom: var(--border);
}

.filters-card {
    background: var(--light);
    border-radius: var(--radius);
    padding: 2rem;
    box-shadow: var(--shadow);
}

.filters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.filters-header h3 {
    margin: 0;
    color: var(--dark);
}

.filters-toggle {
    display: none;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    font-weight: 600;
    color: var(--dark);
    font-size: 0.9rem;
}

.search-input {
    position: relative;
}

.search-input i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
}

.search-input input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: var(--border);
    border-radius: var(--radius);
    font-size: 1rem;
}

.filter-select {
    padding: 0.75rem 1rem;
    border: var(--border);
    border-radius: var(--radius);
    font-size: 1rem;
    background: white;
}

.price-range .range-inputs {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.price-range input {
    flex: 1;
    padding: 0.75rem;
    border: var(--border);
    border-radius: var(--radius);
    text-align: center;
}

.range-hint {
    font-size: 0.8rem;
    color: var(--gray);
    margin-top: 0.25rem;
}

.filters-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: var(--radius);
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
}

.btn-secondary {
    background: var(--gray-light);
    color: var(--dark);
}

.btn-secondary:hover {
    background: var(--gray);
    color: white;
}

/* Résultats */
.results-section {
    padding: 3rem 0;
    background: var(--light);
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.results-info h2 {
    margin: 0 0 0.5rem 0;
    color: var(--dark);
}

.results-count {
    color: var(--gray);
    margin: 0;
}

.search-criteria {
    color: var(--primary);
    font-weight: 600;
}

.results-views {
    display: flex;
    gap: 0.5rem;
}

.view-btn {
    padding: 0.5rem;
    border: var(--border);
    background: white;
    border-radius: var(--radius);
    cursor: pointer;
    color: var(--gray);
}

.view-btn.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

/* Grille des destinations */
.destinations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
}

.destinations-grid.list-view {
    grid-template-columns: 1fr;
}

.destinations-grid.list-view .destination-card {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 1.5rem;
}

.destinations-grid.list-view .card-image {
    height: 200px;
}

.destination-card {
    background: white;
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}

.destination-card:hover {
    transform: translateY(-5px);
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
    transition: transform 0.3s ease;
}

.destination-card:hover .card-image img {
    transform: scale(1.05);
}

.card-badges {
    position: absolute;
    top: 1rem;
    left: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.badge {
    padding: 0.5rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    color: white;
}

.badge-popular {
    background: var(--secondary);
    color: var(--dark);
}

.badge-category {
    background: var(--primary);
}

.card-actions {
    position: absolute;
    top: 1rem;
    right: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.destination-card:hover .card-actions {
    opacity: 1;
}

.action-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9);
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--dark);
    transition: all 0.3s ease;
}

.action-btn:hover {
    background: white;
    transform: scale(1.1);
}

.wishlist-btn.active {
    background: #ef4444;
    color: white;
}

.card-content {
    padding: 1.5rem;
}

.card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.location {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--gray);
    font-size: 0.9rem;
}

.rating {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: var(--secondary);
    font-size: 0.9rem;
}

.card-title {
    margin: 0 0 0.75rem 0;
    font-size: 1.25rem;
    font-weight: 700;
}

.card-title a {
    color: var(--dark);
    text-decoration: none;
}

.card-title a:hover {
    color: var(--primary);
}

.card-description {
    color: var(--gray);
    margin-bottom: 1rem;
    line-height: 1.5;
}

.card-features {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.feature {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: var(--gray);
}

.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: var(--border);
}

.price-amount {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
}

.price-note {
    font-size: 0.8rem;
    color: var(--gray);
}

.card-cta {
    display: flex;
    gap: 0.5rem;
}

/* Aucun résultat */
.no-results {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
}

.no-results-content i {
    color: var(--gray-light);
    margin-bottom: 1.5rem;
}

.no-results-content h3 {
    color: var(--dark);
    margin-bottom: 1rem;
}

.no-results-content p {
    color: var(--gray);
    margin-bottom: 2rem;
}

.no-results-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

/* Pagination */
.pagination-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: var(--border);
}

.pagination-info {
    color: var(--gray);
}

/* CTA Section */
.cta-section {
    padding: 4rem 0;
    background: white;
}

.cta-card {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: var(--radius);
    padding: 3rem;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 2rem;
    align-items: center;
    color: white;
}

.cta-content h2 {
    margin: 0 0 1rem 0;
    font-size: 2rem;
}

.cta-content p {
    opacity: 0.9;
    margin-bottom: 2rem;
}

.cta-actions {
    display: flex;
    gap: 1rem;
}

.cta-image {
    font-size: 4rem;
    opacity: 0.8;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-stats {
        flex-direction: column;
        gap: 1rem;
    }
    
    .filters-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .filters-toggle {
        display: flex;
    }
    
    .filters-form {
        display: none;
    }
    
    .filters-form.active {
        display: block;
    }
    
    .filters-grid {
        grid-template-columns: 1fr;
    }
    
    .results-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .results-views {
        align-self: flex-end;
    }
    
    .destinations-grid {
        grid-template-columns: 1fr;
    }
    
    .destinations-grid.list-view .destination-card {
        grid-template-columns: 1fr;
    }
    
    .card-footer {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .card-cta {
        justify-content: stretch;
    }
    
    .card-cta .btn {
        flex: 1;
    }
    
    .cta-card {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .cta-actions {
        flex-direction: column;
    }
    
    .filters-actions {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle des filtres sur mobile
    const filtersToggle = document.getElementById('filtersToggle');
    const filtersForm = document.getElementById('filtersForm');
    
    if (filtersToggle && filtersForm) {
        filtersToggle.addEventListener('click', function() {
            filtersForm.classList.toggle('active');
        });
    }

    // Changement de vue (grille/liste)
    const viewBtns = document.querySelectorAll('.view-btn');
    const destinationsView = document.getElementById('destinationsView');
    
    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.dataset.view;
            
            // Mettre à jour les boutons actifs
            viewBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Changer la vue
            if (view === 'list') {
                destinationsView.classList.add('list-view');
            } else {
                destinationsView.classList.remove('list-view');
            }
        });
    });

    // Wishlist functionality
    const wishlistBtns = document.querySelectorAll('.wishlist-btn');
    
    wishlistBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const destinationId = this.dataset.destination;
            const icon = this.querySelector('i');
            
            if (this.classList.contains('active')) {
                // Retirer des favoris
                this.classList.remove('active');
                icon.classList.remove('fas');
                icon.classList.add('far');
                showNotification('Destination retirée des favoris');
            } else {
                // Ajouter aux favoris
                this.classList.add('active');
                icon.classList.remove('far');
                icon.classList.add('fas');
                showNotification('Destination ajoutée aux favoris');
            }
            
            // Sauvegarder en localStorage
            saveWishlistState(destinationId, this.classList.contains('active'));
        });
    });

    // Restaurer l'état de la wishlist
    function restoreWishlistState() {
        wishlistBtns.forEach(btn => {
            const destinationId = btn.dataset.destination;
            const isInWishlist = localStorage.getItem(`wishlist_${destinationId}`) === 'true';
            
            if (isInWishlist) {
                btn.classList.add('active');
                const icon = btn.querySelector('i');
                icon.classList.remove('far');
                icon.classList.add('fas');
            }
        });
    }

    function saveWishlistState(destinationId, isInWishlist) {
        localStorage.setItem(`wishlist_${destinationId}`, isInWishlist);
    }

    function showNotification(message) {
        // Créer une notification toast
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
        
        // Animation d'entrée
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Animation de sortie après 3 secondes
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

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

    // Observer les cartes de destination
    document.querySelectorAll('.destination-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });

    // Restaurer l'état de la wishlist au chargement
    restoreWishlistState();

    // Auto-submit du formulaire lors du changement des sélecteurs
    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filtersForm').submit();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>