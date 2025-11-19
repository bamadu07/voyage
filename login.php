<?php
    require_once('config/database.php');

    $user = new Database();


    if (isset($_POST["connecter"])) {
        extract($_POST);

        $user= "SELECT * FROM user WHERE email =:email AND mdp =:mdp";

        if ($user) {
            $_SESSION["user"] = $user;
             require_once('contact.php');
            //return ("index.php");
        } else {
            $message = "Email ou mot de passe incorrect";
        }
    }

    include 'includes/header.php';
?>


    <div class="p-5">
        <div class="text-center">
            <h1 class="h4 text-gray-900 mb-4">Bienvenue à VoyageExplorer <a href="index.php" class="btn btn-outline-info btn-sm">Retour</a></h1>
        </div>
        
        <form class="user" method="post" action="">
            <div class="form-group">
                <input type="email" required name="email" class="form-control form-control-user" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Entrer l'email'...">
            </div>
            <div class="form-group">
                <input type="password" required name="mdp" class="form-control form-control-user" id="exampleInputPassword" placeholder="Mot de passe">
            </div>

            <button type="submit" name="connecter" class="btn btn-success btn-user btn-block">
                Se connecter
            </button>
        </form>
    </div>

<?php
    include 'includes/footer.php';
 ?>
