<?php
/** 
* définition de la classe membre
*/
class Membre {
        private int $_idMembre;
        private string $_nom;
        private string $_prenom;
        private string $_idIut;
		private string $_email;
		private string $_passwordHash;
		private int $_admin;
		
        // contructeur
        public function __construct(array $donnees) {
		// initialisation d'un produit à partir d'un tableau de données
			if (isset($donnees['idMembre'])) { $this->_idMembre = intval($donnees['idMembre']); }
			if (isset($donnees['nom'])) { $this->_nom = $donnees['nom']; }
			if (isset($donnees['prenom'])) { $this->_prenom = $donnees['prenom']; }
            if (isset($donnees['idIut'])) { $this->_idIut = $donnees['idIut']; }
            if (isset($donnees['email'])) { $this->_email = $donnees['email']; }
			if (isset($donnees['passwordHash'])) { $this->_passwordHash = $donnees['passwordHash']; }
			if (isset($donnees['admin'])) { $this->_admin = $donnees['admin']; }
        }           
        // GETTERS //
		public function idMembre() { return $this->_idMembre;}
		public function nom() { return $this->_nom;}
		public function prenom() { return $this->_prenom;}
        public function id_iut() { return $this->_idIut;}
		public function email() { return $this->_email;}
		public function passwordHash() { return $this->_passwordHash;}
		public function admin() { return $this->_admin;}

		// SETTERS //
		public function setIdMembre(int $idmembre) { $this->_idmembre = $idmembre; }
        public function setNom(string $nom) { $this->_nom= $nom; }
		public function setPrenom(string $prenom) { $this->_prenom = $prenom; }
	    public function setIdIut(int $id_iut) { $this->_id_iut = $id_iut; }
        public function setEmail(string $email) { $this->_email = $email; }
		public function setPasswordHash(string $password_hash) { $this->_password_hash = $password_hash; }
        public function setAdmin(int $admin) { $this->_admin = $admin; }

        public function jsonSerialize()
        {
            $vars = [];
            // push all the public vars into the array

            $vars["idMembre"] = $this->idMembre();
            $vars["nom"] = $this->nom();
            $vars["prenom"] = $this->prenom();
            $vars["idIut"] = $this->id_iut();
            $vars["email"] = $this->email();


            return $vars;
        }
    }

?>