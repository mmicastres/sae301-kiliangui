<?php

/**
* Définition d'une classe permettant de gérer les membres 
* en relation avec la base de données
*
*/

class MembreManager
    {
        private $_db; // Instance de PDO - objet de connexion au SGBD
        
		/** 
		* Constructeur = initialisation de la connexion vers le SGBD
		*/
        public function __construct($db) {
            $this->_db=$db;
        }

        public function get($idmembre) {
            $req = "SELECT * FROM pr_utilisateur WHERE idmembre=:idmembre";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array(":idmembre" => $idmembre));
            if ($data = $stmt->fetch()) {
                $membre = new Membre($data);
                return $membre;
            } else return false;
        }

        public function get_participants($idProjet){
            $req = "SELECT * FROM pr_utilisateur WHERE idmembre IN (SELECT idmembre FROM pr_participer WHERE idProjet=:idProjet)";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array(":idProjet" => $idProjet));
            $participants = array();
            while ($data = $stmt->fetch()) {
                $participants[] = new Membre($data);
            }
            return $participants;
        }
		
		/**
		* verification de l'identité d'un membre (Login/password)
		* @param string $login
		* @param string $password
		* @return membre si authentification ok, false sinon
		*/
		public function verif_identification($email, $password) {
		//echo $login." : ".$password;
            // get the password hash for password_verify
            $req = "SELECT password_hash FROM pr_utilisateur WHERE `email`=:email";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array(":email" => $email));
            $data = $stmt->fetch();
            $hpassword = $data['password_hash'];
            if (password_verify($password, $hpassword)) {
                $req = "SELECT idmembre, nom, prenom FROM pr_utilisateur WHERE `email`=:email";
                $stmt = $this->_db->prepare($req);
                $stmt->execute(array(":email" => $email));
                if ($data = $stmt->fetch()) {
                    $membre = new Membre($data);
                    return $membre;
                } else return false;
            } else return false;
		}

        /**
         * Inscrit un membre a partir de son email et mot de passe
         * @param string $nom
         * @param string $prenom
         * @param int $id_iut
         * @param string $email
         * @param string $password
         * @param int $admin
         * @return membre
         */
        public function InscriptionMembre($nom, $prenom, $id_iut,$email,$password,$admin = 0) {
            $req = "INSERT INTO pr_utilisateur (nom,prenom,id_iut,email,password_hash,admin) VALUES (:nom,:prenom,:id_iut,:email,:password_hash, :admin)";
            $stmt = $this->_db->prepare($req);
            // password hashé
            $hpassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute(array(":nom" => $nom, ":prenom" => $prenom, ":id_iut" => $id_iut, ":email" => $email, ":password_hash" => $hpassword, ":admin" => $admin));
            $idmembre = $this->_db->lastInsertId();
            $req = "SELECT * FROM pr_utilisateur WHERE idmembre=:idmembre";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array(":idmembre" => $idmembre));
            if ($data = $stmt->fetch()) {
                $membre = new Membre($data);
                return $membre;
            } else return false;


        }
    }
?>