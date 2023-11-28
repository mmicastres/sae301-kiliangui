<?php
/**
* Définition d'une classe permettant de gérer les itinéraires 
*   en relation avec la base de données	
*/
class ItineraireManager {
    
	private $_db; // Instance de PDO - objet de connexion au SGBD
        
	/**
	* Constructeur = initialisation de la connexion vers le SGBD
	*/
	public function __construct($db) {
		$this->_db=$db;
	}
        
	/**
	* ajout d'un itineraire dans la BD
	* @param itineraire à ajouter
	* @return int true si l'ajout a bien eu lieu, false sinon
	*/
	public function add(Itineraire $iti) {
		// calcul d'un nouveau code d'itineraire non déja utilisé = Maximum + 1
		$stmt = $this->_db->prepare("SELECT max(iditi) AS maximum FROM itineraire");
		$stmt->execute();
		$iti->setIdIti($stmt->fetchColumn()+1);
		
		// requete d'ajout dans la BD
		$req = "INSERT INTO itineraire (iditi,idmembre,lieudepart,lieuarrivee,heuredepart,datedepart,tarif,nbplaces,bagagesautorises,details) VALUES (?,?,?,?,?,?,?,?,?,?)";
		$stmt = $this->_db->prepare($req);
		$res  = $stmt->execute(array($iti->idIti(), $iti->idMembre(), $iti->lieuDepart(), $iti->lieuArrivee(), $iti->heureDepart(),dateChgmtFormat($iti->dateDepart()), $iti->tarif(), $iti->nbPlaces(), $iti->bagagesAutorises(), $iti->details()));		
		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
		return $res;
	}
        
	/**
	* nombre d'itinéraires dans la base de données
	* @return int le nb d'itinéraires
	*/
	public function count():int {
		$stmt = $this->_db->prepare('SELECT COUNT(*) FROM itineraire');
		$stmt->execute();
		return $stmt->fetchColumn();
	}
        
	/**
	* suppression d'un itineraire dans la base de données
	* @param Itineraire 
	* @return boolean true si suppression, false sinon
	*/
	public function delete(Itineraire $iti) : bool {
		$req = "DELETE FROM itineraire WHERE iditi = ?";
		$stmt = $this->_db->prepare($req);
		return $stmt->execute(array($iti->iditi()));
	}
		
	/**
	* echerche dans la BD d'un itineraire à partir de son id
	* @param int $iditi 
	* @return Itineraire 
	*/
	public function get(int $iditi) : Itineraire {	
		$req = 'SELECT iditi,idmembre,lieudepart,lieuarrivee,heuredepart,date_format(datedepart,"%d/%c/%Y")as datedepart,tarif,nbplaces,bagagesautorises,details FROM itineraire WHERE iditi=?';
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($iditi));
		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
		$iti = new Itineraire($stmt->fetch());
		return $iti;
	}		
		
	/**
	* retourne l'ensemble des itinéraires présents dans la BD 
	* @return Itineraire[]
	*/
	public function getList() {
		$itis = array();
		$req = "SELECT iditi, lieudepart, lieuarrivee, heuredepart, date_format(datedepart,'%d/%c/%Y') as datedepart,"
		. "tarif, nbplaces, bagagesautorises, details FROM itineraire";
		$stmt = $this->_db->prepare($req);
		$stmt->execute();
		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
		// récup des données
		while ($donnees = $stmt->fetch())
		{
			$itis[] = new Itineraire($donnees);
		}
		return $itis;
	}

	/**
	* retourne l'ensemble des itinéraires présents dans la BD pour un membre
	* @param int idmembre
	* @return Itineraire[]
	*/
	public function getListMembre(int $idmembre) {
		$itis = array();
		$req = "SELECT iditi, lieudepart, lieuarrivee, heuredepart, date_format(datedepart,'%d/%c/%Y') as datedepart,"
		. "tarif, nbplaces, bagagesautorises, details FROM itineraire WHERE idmembre=?";
		$stmt = $this->_db->prepare($req);
		$stmt->execute(array($idmembre));
		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
		// recup des données
		while ($donnees = $stmt->fetch())
		{
			$itis[] = new Itineraire($donnees);
		}
		return $itis;
	}
	/**
	* méthode de recherche d'itinéraires dans la BD à partir des critères passés en paramètre
	* @param string $lieudepart
	* @param string $lieudepart
	* @param string $datedepart
	* @return Itineraire[]
	*/
	public function search(string $lieudepart, string $lieuarrivee, string $datedepart) {
		$req = "SELECT iditi,lieudepart,lieuarrivee,heuredepart,date_format(datedepart,'%d/%c/%Y')as datedepart,tarif,nbplaces,bagagesautorises,details FROM itineraire";
		$cond = '';

		if ($lieudepart<>"") 
		{ 	$cond = $cond . " lieudepart like '%". $lieudepart ."%'";
		}
		if ($lieuarrivee<>"") 
		{ 	if ($cond<>"") $cond .= " AND ";
			$cond = $cond . " lieuarrivee like '%" . $lieuarrivee ."%'";
		}
		if ($datedepart<>"") 
		{ 	if ($cond<>"") $cond .= " AND ";
			$cond = $cond . " datedepart = '" . dateChgmtFormat($datedepart) . "'";
		}
		if ($cond <>"")
		{ 	$req .= " WHERE " . $cond;
		}
		// execution de la requete				
		$stmt = $this->_db->prepare($req);
		$stmt->execute();
		// pour debuguer les requêtes SQL
		$errorInfo = $stmt->errorInfo();
		if ($errorInfo[0] != 0) {
			print_r($errorInfo);
		}
		$itineraires = array();
		while ($donnees = $stmt->fetch())
		{
			$itineraires[] = new Itineraire($donnees);
		}
		return $itineraires;
	}
	
	/**
	* modification d'un itineraire dans la BD
	* @param Itineraire
	* @return boolean 
	*/
	public function update(Itineraire $iti) : bool {
		$req = "UPDATE itineraire SET lieudepart = :lieudepart, "
					. "lieuarrivee = :lieuarrivee, "
					. "heuredepart = :heuredepart, "
					. "datedepart  = :datedepart, "
					. "tarif = :tarif, "
					. "nbplaces = :nbplaces, "
					. "bagagesautorises= :bagages, "
					. "details = :details" 
					. " WHERE iditi = :iditi";
		//var_dump($iti);

		$stmt = $this->_db->prepare($req);
		$stmt->execute(array(":lieudepart" => $iti->lieuDepart(),
								":lieuarrivee" => $iti->lieuArrivee(),
								":heuredepart" => $iti->heureDepart(),
								":datedepart" => dateChgmtFormat($iti->dateDepart()),
								":tarif" => $iti->tarif(), 
								":nbplaces" => $iti->nbPlaces(),
								":bagages" => $iti->bagagesAutorises(),
								":details" => $iti->details(),
								":iditi" => $iti->idIti() ));
		return $stmt->rowCount();
		
	}
}

// fontion de changement de format d'une date
// tranformation de la date au format j/m/a au format a/m/j
function dateChgmtFormat($date) {
//echo "date:".$date;
		list($j,$m,$a) = explode("/",$date);
		return "$a/$m/$j";
}
?>