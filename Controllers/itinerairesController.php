<?php
include "Modules/itineraires.php";
include "Models/itinerairesManager.php";
/**
* Définition d'une classe permettant de gérer les itinéraires 
*   en relation avec la base de données	
*/
class ItineraireController {
    
	private $itiManager; // instance du manager
    private $projetManager;
	private $twig;
        
	/**
	* Constructeur = initialisation de la connexion vers le SGBD
	*/
	public function __construct($db, $twig) {
		$this->itiManager = new ItineraireManager($db);
        $this->projetManager = new ProjetManager($db);
		$this->twig = $twig;
	}
        
	/**
	* liste de tous les itinéraires
	* @param aucun
	* @return rien
	*/
	public function listeItineraires() {
		$itineraires = $this->itiManager->getList();
		echo $this->twig->render('itineraire_liste.html.twig',array('itis'=>$itineraires,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"])); 
	}

	/**
	* liste de mes itinéraires
	* @param aucun
	* @return rien
	*/
	public function listeMesItineraires($idMembre) {
		$itineraires = $this->itiManager->getListMembre($idMembre);
		echo $this->twig->render('itineraire_liste.html.twig',array('itis'=>$itineraires,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"])); 
	  }
	/**
	* formulaire ajout
	* @param aucun
	* @return rien
	*/
	public function formAjoutItineraire() {
		echo $this->twig->render('itineraire_ajout.html.twig',array('acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"],'idmembre'=>$_SESSION['idmembre'])); 
	}

	/**
	* ajout dans la BD d'un iti à partir du form
	* @param aucun
	* @return rien
	*/
	public function ajoutItineraire() {
		$iti = new Itineraire($_POST);
		$ok = $this->proje->add($iti);
		$message = $ok ? "Itinéraire ajouté" : "probleme lors de l'ajout";
		echo $this->twig->render('index.html.twig',array('message'=>$message,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"])); 

	}
	/**
	* form de choix de l'iti à supprimer
	* @param aucun
	* @return rien
	*/
	public function choixSuppItineraire($idMembre) {
		$itineraires = $this->itiManager->getListMembre($idMembre);
		echo $this->twig->render('itineraire_choix_suppression.html.twig',array('itis'=>$itineraires,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"])); 
	}
	/**
	* suppression dans la BD d'un iti à partir de l'id choisi dans le form précédent
	* @param aucun
	* @return rien
	*/
	public function suppItineraire() {
		$iti = new Itineraire($_POST);
		$ok = $this->itiManager->delete($iti);
		$message = $ok ?  "itineraire supprimé" : "probleme lors de la supression";
		echo $this->twig->render('index.html.twig',array('message'=>$message,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"])); 
	}
	/**
	* form de choix de l'iti à modifier
	* @param aucun
	* @return rien
	*/
	public function choixModItineraire($idMembre) {
		$itineraires = $this->itiManager->getListMembre($idMembre);
		echo $this->twig->render('projet_choix_modification.html.twig',array('itis'=>$itineraires,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
	}
	/**
	* form de saisi des nouvelles valeurs de l'iti à modifier
	* @param aucun
	* @return rien
	*/
	public function saisieModItineraire() {
		$iti =  $this->itiManager->get($_POST["iditi"]);
		echo $this->twig->render('projet_modification.html.twig',array('iti'=>$iti,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"]));
	}

	/**
	* modification dans la BD d'un iti à partir des données du form précédent
	* @param aucun
	* @return rien
	*/
	public function modItineraire() {
		$iti =  new Itineraire($_POST);
		$ok = $this->itiManager->update($iti);
		$message = $ok ? "itineraire modifié" : $message = "probleme lors de la modification";
		echo $this->twig->render('index.html.twig',array('message'=>$message,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"])); 
	}

	/**
	* form de saisie des criteres
	* @param aucun
	* @return rien
	*/
	public function formRechercheItineraire() {
		echo $this->twig->render('itineraire_recherche.html.twig',array('acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"])); 
	}

	/**
	* recherche dans la BD d'iti à partir des données du form précédent
	* @param aucun
	* @return rien
	*/
	public function rechercheItineraire() {
		$itineraires = $this->itiManager->search($_POST["lieudepart"], $_POST["lieuarrivee"], $_POST["datedepart"]);
		echo $this->twig->render('itineraire_liste.html.twig',array('itis'=>$itineraires,'acces'=> $_SESSION['acces'],'admin'=>$_SESSION["admin"])); 
	}
}