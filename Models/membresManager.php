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
            $req = "SELECT * FROM pr_membre WHERE idmembre=:idmembre";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array(":idmembre" => $idmembre));
            if ($data = $stmt->fetch()) {
                $membre = new Membre($data);
                return $membre;
            } else return false;
        }

        public function get_participants($idProjet){
            $req = "SELECT * FROM pr_membre WHERE idmembre IN (SELECT idmembre FROM pr_participer WHERE idProjet=:idProjet)";
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
            $req = "SELECT passwordHash FROM pr_membre WHERE `email`=:email";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array(":email" => $email));
            $data = $stmt->fetch();
            if ($data == false) return false;
            $hpassword = $data['passwordHash'];
            if (password_verify($password, $hpassword)) {
                $req = "SELECT idMembre, nom, prenom FROM pr_membre WHERE `email`=:email";
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
         * @param string $id_iut
         * @param string $email
         * @param string $password
         * @param int $admin
         * @return membre
         */
        public function InscriptionMembre($nom, $prenom, $id_iut,$email,$password,$admin = 0) {
            // check if the email is already used
            $req = "SELECT * FROM pr_membre WHERE email=:email";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array(":email" => $email));
            if ($data = $stmt->fetch()) {
                return "email already used";
            }
            $req = "INSERT INTO pr_membre (nom,prenom,idIut,email,passwordHash,admin) VALUES (:nom,:prenom,:id_iut,:email,:password_hash, :admin)";
            $stmt = $this->_db->prepare($req);
            // password hashé
            $hpassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute(array(":nom" => $nom, ":prenom" => $prenom, ":id_iut" => $id_iut, ":email" => $email, ":password_hash" => $hpassword, ":admin" => $admin));
            $idmembre = $this->_db->lastInsertId();
            $req = "SELECT * FROM pr_membre WHERE idmembre=:idmembre";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array(":idmembre" => $idmembre));
            if ($data = $stmt->fetch()) {
                $membre = new Membre($data);
                return $membre;
            } else return false;
        }
        public function searchMembre($search){
            $req = "SELECT * FROM pr_membre WHERE nom LIKE :search OR prenom LIKE :search OR email LIKE :search LIMIT 10";
            $stmt = $this->_db->prepare($req);
            $stmt->execute(array(":search" => "%".$search."%"));
            $membres = array();
            while ($data = $stmt->fetch()) {
                $membres[] = new Membre($data);
            }
            return $membres;
        }
    }
?>