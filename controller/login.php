<?php
require_once('config/database.php');


if (isset($_POST["connecter"])) {
    extract($_POST);
    $user = connecter($email, $password);
    if ($user) {
        $_SESSION["user"] = $user;
        return header("Location:?page=dashboard");
    } else {
        $message = "Email ou mot de passe incorrect";
    }
}

require_once('login.php');

?>