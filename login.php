<?php
    include 'includes/header.php';
    include 'config/database.php';

    $database = new Database();
    $db = $database->getConnection();

?>
    <div class="p-5">
        <div class="text-center">
            <h1 class="h4 text-gray-900 mb-4">Bienvenue à VoyageExplorer <a href="index.php" class="btn btn-outline-info btn-sm">Retour</a></h1>
        </div>
        <form class="user" method="post" action="">
            <div class="form-group">
                <input type="email" required name="email" class="form-control form-control-user" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Entrer l'adresse email...">
            </div>
            <div class="form-group">
                <input type="password" required name="password" class="form-control form-control-user" id="exampleInputPassword" placeholder="Mot de passe">
            </div>

            <button type="submit" name="connecter" class="btn btn-success btn-user btn-block">
                Se connecter
            </button>
        </form>
    </div>

<?php
    include 'includes/footer.php';
 ?>
