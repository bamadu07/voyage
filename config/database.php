<?php
class Database {
    private $host = "localhost";
    private $db_name = "agence_voyage";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Erreur de connexion: " . $exception->getMessage();
        }
        return $this->conn;
    }

    // Connexion utilisateur 
    function connecter($email, $mdp)
    {
        try {
            $req = $this->conn->prepare("SELECT * FROM user WHERE email =:email AND mdp =:mdp");

            $req->execute([
                'email' => $email,
                'mdp' => $mdp,
            ]);

            return $req->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }

    
    // Récupérer tous les utilisateurs
     function user()
    {
        try {
            $req = $this->conn->prepare("SELECT * FROM user
            ORDER BY nom ASC");

            $req->execute();

            return $req->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }

}
?>