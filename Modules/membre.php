<?php
/** 
* définition de la classe itineraire
*/
class Membre {
        private int $_idmembre;
        private string $_nom;
        private string $_prenom;
		private string $_email;
		private string $_password;
		private int $_anneenaissance;
		private string $_sexe;
		private string $_voiture;
		private string $_telportable;
		private int $_admin;
		
        // contructeur
        public function __construct(array $donnees) {
		// initialisation d'un produit à partir d'un tableau de données
			if (isset($donnees['idmembre'])) { $this->_idmembre = $donnees['idmembre']; }
			if (isset($donnees['nom'])) { $this->_nom = $donnees['nom']; }
			if (isset($donnees['prenom'])) { $this->_prenom = $donnees['prenom']; }
			if (isset($donnees['email'])) { $this->_email = $donnees['email']; }
			if (isset($donnees['password'])) { $this->_password = $donnees['password']; }
			if (isset($donnees['anneenaissance'])) { $this->_anneenaissance = $donnees['anneenaissance']; }
			if (isset($donnees['sexe'])) { $this->_sexe = $donnees['sexe']; }
			if (isset($donnees['voiture'])) { $this->_voiture = $donnees['voiture']; }
			if (isset($donnees['telportable'])) { $this->_telportable = $donnees['telportable']; }
			if (isset($donnees['admin'])) { $this->_admin = $donnees['admin']; }
        }           
        // GETTERS //
		public function idMembre() { return $this->_idmembre;}
		public function nom() { return $this->_nom;}
		public function prenom() { return $this->_prenom;}
		public function email() { return $this->_email;}
		public function password() { return $this->_password;}
		public function anneeNaissance() { return $this->_anneenaissance;}
		public function sexe() { return $this->_sexe;}
		public function voiture() { return $this->_voiture;}
		public function telPortable() { return $this->_telportable;}
		public function admin() { return $this->_admin;}
		public function getAge() { return (date('Y')- $this->_anneenaissance) ; }
		
		// SETTERS //
		public function setIdMembre(int $idmembre) { $this->_idmembre = $idmembre; }
        public function setNom(string $nom) { $this->_nom= $nom; }
		public function setPrenom(string $prenom) { $this->_prenom = $prenom; }
		public function setEmail(string $email) { $this->_email = $email; }
		public function setPassword(string $password) { $this->_password = $password; }
		public function setAnneeNaissance(int $anneenaissance) { $this->_anneenaissance = $anneenaissance; }
		public function setSexe(string $sexe) { $this->_sexe = $sexe; }
		public function setVoiture(string $voiture) { $this->_voiture = $voiture; }
		public function setTelPortable(string $telportable) { $this->_telportable = $telportable; }		
		public function setAdmin(int $admin) { $this->_admin = $admin; }		

    }

?>